<?php

namespace Tests\Feature;

use App\Models\AutopilotPeriod;
use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-31: "el autopiloto debería poder ponerse retrospectivamente,
 * en el caso de que alguien se olvide ponerlo, podemos hacer una
 * retrospección máxima de 2 meses?" - a period can start up to 2 months
 * back, and the months already gone get what Autopilot would have done:
 * the penalty given back, the month in the history.
 */
class AutopilotRetroactiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-31 10:00'); // before the month-end jobs
    }

    private function classInGrade(?Grade $grade = null): SchoolClass
    {
        $grade ??= Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid()]);

        return SchoolClass::create(['sid' => '1', 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
    }

    /** A student with Ƶ 1000, and a penalty at the end of each month given. */
    private function student(SchoolClass $class, array $penalties = []): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => '2026-05-01']);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        $balance = 1000;
        Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => '2026-07-01',
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => 1000, 'balance' => 1000]);
        foreach ($penalties as $date => $amount) {
            $balance -= $amount;
            Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => $date,
                'description' => 'Entertainment: Cinema ticket', 'type' => 'debit', 'category' => 'Penalty', 'amount' => $amount,
                'balance' => $balance, 'is_penalty' => 1]);
        }

        return $user;
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 2, 'sid' => 1]);
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    public function test_it_can_start_up_to_two_months_back_and_no_further(): void
    {
        $student = $this->student($this->classInGrade());
        $admin = $this->admin();
        $form = ['target' => 'student', 'student_id' => $student->id, 'end_date' => '2026-11-10', 'reason' => 'Forgot to set it, ill since August'];

        $this->actingAs($admin)->get(route('admin.autopilot.index'))->assertOk()
            ->assertSee('min="2026-08-31"', false)
            ->assertSee('It can start up to 2 months back');

        $this->actingAs($admin)->from(route('admin.autopilot.index'))->post(route('admin.autopilot.store'), $form + ['start_date' => '2026-08-30'])
            ->assertSessionHasErrors(['start_date' => 'Autopilot can start at most 2 months back: the earliest start date is 2026-08-31.']);
        $this->assertSame(0, AutopilotPeriod::count());

        $this->actingAs($admin)->post(route('admin.autopilot.store'), $form + ['start_date' => '2026-08-31'])
            ->assertRedirect(route('admin.autopilot.index'));
        $this->assertSame(1, AutopilotPeriod::count());

        $this->actingAs($admin)->get(route('admin.autopilot.index'))->assertSee('set later, on 2026-10-31');
    }

    public function test_the_months_already_gone_get_their_penalty_back_and_their_history_line(): void
    {
        // penalties at the end of July (not covered), September, and today at login (October)
        $student = $this->student($this->classInGrade(), ['2026-07-31 23:50' => 100, '2026-09-30 23:50' => 200, '2026-10-31 08:00' => 50]);
        $this->assertSame(650.0, $this->balance($student));

        $this->actingAs($this->admin())->post(route('admin.autopilot.store'), [
            'target' => 'student', 'student_id' => $student->id,
            'start_date' => '2026-09-01', 'end_date' => '2026-10-15', 'reason' => 'Hospital stay, approved by parents',
        ])->assertRedirect(route('admin.autopilot.index'))
            ->assertSessionHas('success', 'Autopilot recorded for ' . $student->nickname . '. Earlier months caught up: 2 penalties given back (' . zeds(250) . '), 1 past month added to the history.');

        // September's and October's penalties back, as reversals; July's stays
        $reversals = Transaction1::where('user_id', $student->id)->where('category', 'Reversal')->orderBy('id')->get();
        $this->assertSame(['Reversal - Entertainment: Cinema ticket', 'Reversal - Entertainment: Cinema ticket'], $reversals->pluck('description')->all());
        $this->assertEquals([200, 50], $reversals->pluck('amount')->map(fn ($a) => (float) $a)->all());
        $this->assertSame(900.0, $this->balance($student));
        $this->assertNull(Transaction1::where('user_id', $student->id)->where('amount', 100)->first()->meta['reversed_by'] ?? null);

        // September is in the history; October gets its line at month-end, as usual
        $lines = Transaction1::where('user_id', $student->id)->where('category', 'Autopilot')->get();
        $this->assertSame(['Autopilot - September'], $lines->pluck('description')->all());
        $this->assertSame(850.0, (float) $lines->first()->balance); // September's penalty back, October's not yet

        // Each only once: a second period over the same months adds nothing, and
        // the month-end job finds September already there.
        $this->actingAs($this->admin())->post(route('admin.autopilot.store'), [
            'target' => 'student', 'student_id' => $student->id,
            'start_date' => '2026-09-10', 'end_date' => '2026-09-20', 'reason' => 'Second entry for the same weeks',
        ])->assertSessionHas('success', 'Autopilot recorded for ' . $student->nickname . '.');
        $this->assertSame(2, Transaction1::where('user_id', $student->id)->where('category', 'Reversal')->count());

        $this->artisan('autopilot:apply', ['--month' => 9, '--year' => 2026, '--write' => true])->assertSuccessful();
        $this->assertSame(1, Transaction1::where('user_id', $student->id)->where('category', 'Autopilot')->count());

        // the student reads it in their language
        app()->setLocale('es');
        $this->assertSame('Anulación - ' . \App\Support\TransactionText::forStudent('Entertainment: Cinema ticket'),
            \App\Support\TransactionText::forStudent($reversals->first()->description));
        $this->assertSame('Piloto automático - septiembre', \App\Support\TransactionText::forStudent('Autopilot - September'));
    }

    public function test_a_period_from_today_on_changes_nothing_in_the_past(): void
    {
        $student = $this->student($this->classInGrade(), ['2026-09-30 23:50' => 200]);

        $this->actingAs($this->admin())->post(route('admin.autopilot.store'), [
            'target' => 'student', 'student_id' => $student->id,
            'start_date' => '2026-11-02', 'end_date' => '2026-11-20', 'reason' => 'School trip abroad',
        ])->assertSessionHas('success', 'Autopilot recorded for ' . $student->nickname . '.');

        $this->assertSame(0, Transaction1::where('user_id', $student->id)->whereIn('category', ['Reversal', 'Autopilot'])->count());
        $this->assertSame(800.0, $this->balance($student));
    }

    public function test_a_whole_grade_set_later_catches_up_every_student(): void
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid()]);
        $a = $this->student($this->classInGrade($grade), ['2026-09-30 23:50' => 120]);
        $b = $this->student($this->classInGrade($grade), ['2026-09-30 23:50' => 80]);
        $c = $this->student($this->classInGrade($grade));

        $this->actingAs($this->admin())->post(route('admin.autopilot.store'), [
            'target' => 'grade', 'grade_id' => $grade->id,
            'start_date' => '2026-09-14', 'end_date' => '2026-09-25', 'reason' => 'Exchange trip, forgot to set it',
        ])->assertSessionHas('success', 'Autopilot scheduled for ' . $grade->name . ' (3 students). Earlier months caught up: 2 penalties given back ('
            . zeds(200) . '), 3 past months added to the history.');

        foreach ([$a, $b, $c] as $student) {
            $this->assertSame(1000.0, $this->balance($student));
            $this->assertSame(['Autopilot - September'], Transaction1::where('user_id', $student->id)->where('category', 'Autopilot')->pluck('description')->all());
        }
    }
}
