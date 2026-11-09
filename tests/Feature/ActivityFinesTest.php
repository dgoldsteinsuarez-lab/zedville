<?php

namespace Tests\Feature;

use App\Models\ActivityFine;
use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\ActivityFineService;
use App\Support\TransactionText;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-11-03: "Put the penalty for the students that did not do the
 * activities in the educational finance department, and reset all the
 * activities that is showing when the deadline already ended" - a 50 Ƶ fine
 * for each Required activity not done, the day after its last day; the board
 * shows an activity only while it runs, with its days left.
 */
class ActivityFinesTest extends TestCase
{
    use RefreshDatabase;

    private SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-12-01 00:20:00');
        DB::table('activity_fine_settings')->update(['amount' => 50, 'active_since' => '2026-11-03']);
        $this->class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
    }

    /** A student of the class since October, with Ƶ $balance on the account. */
    private function student(float $balance = 1000, array $attributes = []): User
    {
        $user = User::factory()->create($attributes + ['role' => 4, 'sid' => '1', 'grade' => $this->class->id, 'created_at' => '2026-10-01']);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        if ($balance > 0) {
            Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => '2026-11-01',
                'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance]);
        }

        return $user;
    }

    /** An activity on the class calendar, as the admin screens save it (the end is the last day + 1). */
    private function activity(string $title, string $firstDay = '2026-11-02', string $lastDay = '2026-11-30', string $position = 'required', ?int $classId = null): int
    {
        return DB::table('calendar_events')->insertGetId([
            'sid' => '1', 'classId' => $classId ?? $this->class->id, 'title' => $title, 'position' => $position,
            'start' => $firstDay . ' 00:00:00', 'end' => Carbon::parse($lastDay)->addDay()->toDateString() . ' 00:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    private function fines(): ActivityFineService
    {
        return app(ActivityFineService::class);
    }

    public function test_a_required_activity_not_done_is_fined_the_day_after_its_last_day(): void
    {
        $student = $this->student();
        $eventId = $this->activity('Needs, Wants & Savings Match');

        // its last day: still time
        $this->assertCount(0, $this->fines()->run(Carbon::parse('2026-11-30 23:00')));

        // the day after: 50 Ƶ, once
        $this->artisan('fines:apply --write')->assertSuccessful();
        $line = Transaction1::where('user_id', $student->id)->where('category', 'Fine')->sole();
        $this->assertSame('Fine: activity not done - Needs, Wants & Savings Match', $line->description);
        $this->assertSame(50.0, (float) $line->amount);
        $this->assertTrue($line->is_penalty);
        $this->assertSame(950.0, $this->balance($student));
        $this->assertDatabaseHas('activity_fines', ['student_id' => $student->id, 'calendar_event_id' => $eventId, 'transaction_id' => $line->id]);

        $this->artisan('fines:apply --write')->assertSuccessful();
        $this->assertSame(1, Transaction1::where('user_id', $student->id)->where('category', 'Fine')->count());

        // a dry run writes nothing
        $other = $this->student();
        $this->artisan('fines:apply')->expectsOutputToContain('Dry run - 1 fine(s) found')->assertSuccessful();
        $this->assertSame(0, Transaction1::where('user_id', $other->id)->where('category', 'Fine')->count());
    }

    public function test_nobody_is_fined_who_did_it_or_is_excused(): void
    {
        $eventId = $this->activity('Needs, Wants & Savings Match');

        $did = $this->student();
        DB::table('activity_completions')->insert(['student_id' => $did->id, 'activity_id' => $eventId, 'activity_type' => 'mba',
            'completed_at' => '2026-11-20 10:00', 'created_at' => now(), 'updated_at' => now()]);
        $extended = $this->student();
        DB::table('assignment_extensions')->insert(['sid' => '1', 'calendar_event_id' => $eventId, 'student_id' => $extended->id,
            'original_due' => '2026-12-01', 'extended_to' => '2026-12-05', 'reason' => 'Ill for a week', 'granted_by' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        $away = $this->student();
        DB::table('autopilot_periods')->insert(['sid' => '1', 'student_id' => $away->id, 'start_date' => '2026-11-20', 'end_date' => '2026-12-10',
            'reason' => 'Hospital stay', 'created_by' => 1, 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);
        $joinedLate = $this->student(1000, ['created_at' => '2026-11-15']);
        $missed = $this->student();

        $this->assertSame([$missed->id], $this->fines()->run()->pluck('student.id')->all());

        // the extension's last day passed: fined then
        $this->assertSame([], $this->fines()->run(Carbon::parse('2026-12-05 23:00'), false)->pluck('student.id')->all());
        $this->assertSame([$extended->id], $this->fines()->run(Carbon::parse('2026-12-06 00:20'), false)->pluck('student.id')->all());
    }

    public function test_only_required_activities_of_the_department_with_a_real_deadline(): void
    {
        $student = $this->student();
        $this->activity('Spending budget activity', '2026-11-02', '2026-11-30', 'optional');
        $this->activity('exam-Final Exam');
        // saved without a last day: it "ends" when it was saved - no deadline
        DB::table('calendar_events')->insert(['sid' => '1', 'classId' => $this->class->id, 'title' => 'task-Budget Basics', 'position' => 'required',
            'start' => '2026-11-05 09:00:00', 'end' => '2026-11-05 09:00:00', 'created_at' => now(), 'updated_at' => now()]);
        // a deadline before fines started
        $this->activity('library-Introduction to Budget', '2026-10-20', '2026-10-31');
        // the Emergency Fund one is done once the fund is open
        $this->activity('Emergency Fund');
        BankAccount::where('student_id', $student->id)->update(['is_open_emergency_account' => 1]);

        $this->assertCount(0, $this->fines()->run());
    }

    public function test_copies_of_one_activity_are_fined_once(): void
    {
        $student = $this->student();
        // two copies on the class calendar (saves before 2026-10-25 made some)
        $this->activity('Emergency Fund', '2026-11-05', '2026-11-29');
        $this->activity('Emergency Fund', '2026-11-02', '2026-11-30');

        $this->fines()->run();
        $this->assertSame(1, ActivityFine::where('student_id', $student->id)->count());
        $this->assertSame(950.0, $this->balance($student));
    }

    public function test_the_amount_is_set_by_the_admin_and_a_student_never_goes_below_zero(): void
    {
        $this->activity('Needs, Wants & Savings Match');
        $rich = $this->student();
        $poor = $this->student(30);
        $broke = $this->student(0);

        DB::table('activity_fine_settings')->update(['amount' => 0]);
        $this->assertCount(0, $this->fines()->run());

        DB::table('activity_fine_settings')->update(['amount' => 75]);
        $this->fines()->run();
        $this->assertSame(925.0, $this->balance($rich));
        $this->assertSame(0.0, $this->balance($poor));
        $this->assertSame(30.0, (float) ActivityFine::where('student_id', $poor->id)->value('amount'));
        // nothing to take: no bank line, but not tried again every day
        $this->assertNull(ActivityFine::where('student_id', $broke->id)->value('transaction_id'));
        $this->assertCount(0, $this->fines()->run());
    }

    public function test_the_statement_and_the_bell_name_the_activity_in_the_students_language(): void
    {
        $this->app->setLocale('es');
        $this->assertSame('Multa: actividad no hecha – Necesidades, deseos y ahorro',
            TransactionText::forStudent('Fine: activity not done - Needs, Wants & Savings Match'));
        $this->assertSame('Anulación - Multa: actividad no hecha – Budget Basics',
            TransactionText::forStudent('Reversal - Fine: activity not done - task-Budget Basics'));
        $this->app->setLocale('de');
        $this->assertSame('Strafgebühr: Aktivität nicht erledigt – Bedürfnisse, Wünsche & Sparen',
            TransactionText::forStudent('Fine: activity not done - Needs, Wants & Savings Match'));
        $this->app->setLocale('en');
        $this->assertSame('Fine: activity not done – Budget Basics', TransactionText::forStudent('Fine: activity not done - task-Budget Basics'));

        // the bank's own notification, from the line
        $student = $this->student();
        $this->activity('Needs, Wants & Savings Match');
        $this->fines()->run();
        $this->assertDatabaseHas('app_notifications', ['user_id' => $student->id, 'title' => 'Transaction Alert']);
    }

    public function test_a_fine_is_not_sorted_in_the_needs_wants_and_savings_match(): void
    {
        $student = $this->student();
        $this->activity('Needs, Wants & Savings Match', '2026-11-02', '2026-11-30');
        $this->fines()->run();

        // in December, November's lines are the ones to sort: the opening one, not the fine
        Carbon::setTestNow('2026-12-02 10:00:00');
        Transaction1::where('user_id', $student->id)->update(['created_at' => '2026-11-30 23:59:00']);
        $page = $this->actingAs($student)->get(route('education.index'))->assertOk();
        $this->assertFalse($page->viewData('simulated'));
        $this->assertSame(['Opening balance'], $page->viewData('template')->pluck('name')->all());
    }

    public function test_autopilot_set_later_gives_the_fine_back(): void
    {
        $student = $this->student();
        $this->activity('Needs, Wants & Savings Match');
        $this->fines()->run();
        $this->assertSame(950.0, $this->balance($student));

        $this->actingAs(User::factory()->create(['role' => 2, 'sid' => 1]))->post(route('admin.autopilot.store'), [
            'target' => 'student', 'student_id' => $student->id,
            'start_date' => '2026-11-20', 'end_date' => '2026-11-28', 'reason' => 'Hospital stay, approved by parents',
        ])->assertRedirect(route('admin.autopilot.index'));

        $this->assertSame(1000.0, $this->balance($student));
        $this->assertSame('Reversal - Fine: activity not done - Needs, Wants & Savings Match',
            Transaction1::where('user_id', $student->id)->where('category', 'Reversal')->value('description'));
    }

    public function test_the_admin_sees_the_fines_and_only_a_super_admin_changes_the_amount(): void
    {
        $student = $this->student();
        $this->activity('Needs, Wants & Savings Match');
        $this->fines()->run();

        $super = User::factory()->create(['role' => 1, 'sid' => 1]);
        $this->actingAs($super)->get(route('admin.activity-fines.index'))->assertOk()
            ->assertSee('Activity Fines')->assertSee('Amount (zeds) - 0 turns fines off')
            ->assertSee($student->name)->assertSee('Needs, Wants &amp; Savings Match', false)->assertSee('30 Nov 2026')->assertSee('Charged');
        $this->actingAs($super)->post(route('admin.activity-fines.settings'), ['amount' => 20])
            ->assertSessionHas('success', 'Each Required activity not done in time now costs 20.00 zeds.');
        $this->assertSame(20.0, (float) DB::table('activity_fine_settings')->value('amount'));

        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => 1]);
        $this->actingAs($schoolAdmin)->get(route('admin.activity-fines.index'))->assertOk()
            ->assertSee($student->name)->assertDontSee('Amount (zeds) - 0 turns fines off')->assertSee('Only a Super Admin can change it.');
        $this->actingAs($schoolAdmin)->post(route('admin.activity-fines.settings'), ['amount' => 0])->assertForbidden();
    }

    public function test_a_deleted_finhero_activity_takes_its_calendar_events_with_it(): void
    {
        Carbon::setTestNow('2026-11-10 10:00:00');
        $student = $this->student();
        $id = DB::table('finhero_activity_registry')->insertGetId(['activity_key' => 'task_9', 'activity_name' => 'Budget Basics', 'type' => 'task',
            'sid' => 1, 'cid' => $this->class->id, 'position' => 'required', 'is_active' => 1,
            'start_date' => '2026-11-02', 'end_date' => '2026-11-30', 'created_at' => now(), 'updated_at' => now()]);
        $october = $this->activity('task-Budget Basics', '2026-10-01', '2026-10-31');
        $this->activity('task-Budget Basics');

        $this->actingAs(User::factory()->create(['role' => 1, 'sid' => 1]))
            ->delete('/admin/finhero/activities/' . $id, ['confirm_code' => '1234', 'delete_code' => '1234'])
            ->assertRedirect('/admin/finhero/activities');

        // this month's goes (October's stays, as that month's history), and nobody is fined for it
        $this->assertSame([$october], DB::table('calendar_events')->where('title', 'task-Budget Basics')->pluck('id')->all());
        $this->assertCount(0, $this->fines()->run(Carbon::parse('2026-12-01 00:20')));
        $this->assertSame(1000.0, $this->balance($student));
    }

    public function test_the_board_shows_an_activity_only_while_it_runs_with_its_days_left(): void
    {
        Carbon::setTestNow('2026-11-03 10:00:00');
        $student = $this->student();
        $assign = fn (string $key, string $from, string $until) => DB::table('mba_position')->insert([
            'user_id' => 1, 'activity_key' => $key, 'sid' => '1', 'cid' => $this->class->id, 'required' => 1, 'optional' => 0,
            'start_date' => $from, 'end_date' => $until, 'created_at' => now(), 'updated_at' => now(),
        ]);
        // last month's, over: not on the board any more
        $assign('mba', '2026-10-20', '2026-10-31');
        $assign('emeaccount', '2026-11-02', '2026-11-10');
        DB::table('finhero_activity_registry')->insert([
            ['activity_key' => 'task_8', 'activity_name' => 'Old Task', 'type' => 'task', 'sid' => 1, 'cid' => $this->class->id,
                'position' => 'required', 'is_active' => 1, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31', 'created_at' => now(), 'updated_at' => now()],
            ['activity_key' => 'task_9', 'activity_name' => 'Budget Basics', 'type' => 'task', 'sid' => 1, 'cid' => $this->class->id,
                'position' => 'required', 'is_active' => 1, 'start_date' => '2026-11-02', 'end_date' => '2026-11-30', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()
            ->assertDontSee('Needs, Wants &amp; Savings Match', false)
            ->assertDontSee('Old Task')
            ->assertSee('Budget Basics')->assertSee('28 days left')
            // the Emergency Fund's real days left (it said 30, always)
            ->assertSee('Open Emergency Fund Account')->assertSee('>8</span>', false)
            ->assertSee(__('efd.fine_note', ['amount' => zeds(50)]))
            // why (Daniel: "explícalo más fácil y mejor")
            ->assertSee('Why?')->assertSee('In Zedville, learning is your job, so you earn a salary for it');
    }

    public function test_a_hybrid_class_is_told_the_fine_but_not_why(): void
    {
        Carbon::setTestNow('2026-11-03 10:00:00');
        $this->class->update(['default_language' => 'es', 'language_level' => 'hybrid']);
        $student = $this->student();

        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()
            ->assertSee('Si no las haces a tiempo')
            ->assertDontSee('aprender es tu trabajo')->assertDontSee('learning is your job');
    }
}
