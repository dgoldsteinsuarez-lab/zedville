<?php

namespace Tests\Feature;

use App\Models\ActivityFine;
use App\Models\ActivityFineSetting;
use App\Models\AutopilotPeriod;
use App\Models\BankAccount;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Models\WantsPlanChoice;
use App\Services\AutopilotService;
use App\Services\BankTransferService;
use App\Services\LedgerService;
use App\Services\MonthEndPenaltyService;
use App\Services\WantsPlanService;
use App\Support\Hybrid;
use App\Support\TransactionText;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The month-end fine (Daniel, 2026-11-07): only a student who never chose the
 * plan of the month (Spending 4) pays it - the City Hall's fine, at least the
 * usual 50 Ƶ and raised until the month's spending is 99% of the salary, with
 * a letter that says why. The first month too. Whoever chose a plan pays
 * nothing, however much is left ("no quiero que haya castigo si no mueve el
 * dinero").
 */
class MonthEndPenaltyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-30 23:50'); // the last day of the month
    }

    /** A student whose month so far is the salary and nothing spent. */
    private function student(array $class = [], string $createdAt = '2026-10-01'): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'spending4' => 'low'] + $class);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => $createdAt]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0]);
        $salary = MonthEndPenaltyService::salary();
        Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth()->addDays(5),
            'description' => 'Monthly Salary Credit - November 2026', 'type' => 'credit', 'category' => 'Income', 'amount' => $salary, 'balance' => $salary, 'is_penalty' => false]);

        return $user;
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    private function chooseAPlan(User $user): void
    {
        WantsPlanService::choose($user, WantsPlanService::offers($user)->first()->id);
    }

    public function test_a_student_who_chose_a_plan_pays_nothing_however_much_is_left(): void
    {
        $user = $this->student();
        $this->chooseAPlan($user);
        $before = $this->balance($user);

        $this->assertNull(app(MonthEndPenaltyService::class)->apply($user));

        $this->assertEquals($before, $this->balance($user));
        $this->assertSame(0, Transaction1::where('user_id', $user->id)->whereIn('category', ['Penalty', 'Fine'])->count());
    }

    public function test_without_a_plan_the_city_hall_fine_takes_the_months_spending_to_99_percent(): void
    {
        $user = $this->student();
        $salary = MonthEndPenaltyService::salary();

        $this->assertEquals(['fine' => round($salary * 0.99, 2)], app(MonthEndPenaltyService::class)->apply($user));

        // one line: the fine for the activity not done, no plan chosen for them ("en vez de wants")
        $fine = Transaction1::where('user_id', $user->id)->where('type', 'debit')->sole();
        $this->assertSame('Fine', $fine->category);
        $this->assertTrue((bool) $fine->is_penalty);
        $this->assertSame('Fine: activity not done - Monthly plan', $fine->description);
        $this->assertSame('2026-11-30', Carbon::parse($fine->transaction_date)->toDateString());
        $this->assertSame(0, WantsPlanChoice::where('user_id', $user->id)->count());
        $this->assertEqualsWithDelta($salary * 0.01, $this->balance($user), 0.01);

        $record = ActivityFine::where('student_id', $user->id)->sole();
        $this->assertNull($record->calendar_event_id);
        $this->assertSame('2026-11-30', $record->lastDay()->toDateString());
        $this->assertEquals((float) $fine->amount, $record->amount);

        // and the City Hall's letter, read in the student's language
        $letter = Mailbox::where('student_id', $user->id)->sole();
        $this->assertSame('city-hall-fine', $letter->template);
        $this->assertEquals(['amount' => (float) $fine->amount, 'month' => '2026-11'], $letter->template_data);
    }

    public function test_the_fine_is_never_less_than_the_usual_fine(): void
    {
        $user = $this->student();
        LedgerService::post($user->id, 'debit', 'Needs', round(MonthEndPenaltyService::salary() * 0.985, 2), 'Rent', ['sid' => '1']);

        $this->assertEquals(['fine' => 50.0], app(MonthEndPenaltyService::class)->apply($user));
    }

    public function test_it_is_charged_once_a_month_and_the_plan_is_no_longer_offered(): void
    {
        $user = $this->student();

        app(MonthEndPenaltyService::class)->apply($user);
        $this->assertNull(app(MonthEndPenaltyService::class)->apply($user));

        $this->assertSame(1, Transaction1::where('user_id', $user->id)->where('category', 'Fine')->count());
        $this->assertSame(1, Mailbox::where('student_id', $user->id)->count());
        $this->assertFalse(WantsPlanService::needsChoice($user));
    }

    public function test_the_first_month_too(): void
    {
        $newcomer = $this->student([], '2026-11-20');

        $this->artisan('penalty:apply --write')->assertSuccessful();

        $this->assertSame(1, Transaction1::where('user_id', $newcomer->id)->where('category', 'Fine')->count());
    }

    public function test_never_on_autopilot(): void
    {
        $user = $this->student();
        AutopilotPeriod::create(['student_id' => $user->id, 'start_date' => '2026-11-01', 'end_date' => '2026-11-30', 'reason' => 'Ill', 'created_by' => $user->id]);

        $this->assertNull(app(MonthEndPenaltyService::class)->apply($user));
        $this->assertSame(0, ActivityFine::where('student_id', $user->id)->count());
    }

    public function test_an_autopilot_set_later_gives_it_back(): void
    {
        $user = $this->student();
        $before = $this->balance($user);
        app(MonthEndPenaltyService::class)->apply($user);

        $period = AutopilotPeriod::create(['student_id' => $user->id, 'start_date' => '2026-11-01', 'end_date' => '2026-11-30', 'reason' => 'Ill', 'created_by' => $user->id]);
        $done = AutopilotService::catchUp($period, $user->id);

        $this->assertSame(1, $done['refunds']);
        $this->assertEqualsWithDelta($before, $this->balance($user), 0.01);
    }

    public function test_the_line_and_the_letter_in_spanish_and_in_a_hybrid_class(): void
    {
        $user = $this->student(['default_language' => 'es']);
        app(MonthEndPenaltyService::class)->apply($user);
        $letter = Mailbox::where('student_id', $user->id)->sole();
        app()->setLocale('es');

        $this->assertSame('Multa: actividad no hecha – Plan del mes', TransactionText::forStudent('Fine: activity not done - Monthly plan'));
        $this->assertStringStartsWith('Multa del ayuntamiento: tu plan de noviembre', $letter->subject);
        $this->assertStringContainsString('En noviembre no elegiste tu plan del mes, así que el ayuntamiento te ha cobrado una multa de', $letter->content);
        $this->assertStringContainsString('como cualquier ciudadano adulto, tienes obligaciones', $letter->content);
        $this->assertStringContainsString('Ayuntamiento de Zedville', $letter->content);

        // hybrid: inform, don't explain - the fine and what to do, without the why
        Hybrid::enable();
        try {
            $this->assertStringNotContainsString('ciudadano adulto', $letter->content);
            $this->assertStringContainsString('Elige tu plan cada mes.', $letter->content);
        } finally {
            Hybrid::enable(false);
        }
    }

    /** Daniel, 2026-11-04: from the 26th, with the month's tasks done and 91% of the salary spent. */
    public function test_the_money_market_waits_for_the_tasks_and_91_percent_spent(): void
    {
        $user = $this->student();
        $bank = app(BankTransferService::class);
        $event = DB::table('calendar_events')->insertGetId(['sid' => '1', 'classId' => $user->grade, 'title' => 'Needs, Wants & Savings Match',
            'position' => 'required', 'start' => '2026-11-02', 'end' => '2026-12-01', 'created_at' => now(), 'updated_at' => now()]);

        Carbon::setTestNow('2026-11-25 10:00');
        $this->assertSame('mm_locked_day', $bank->moneyMarketLock($user));

        Carbon::setTestNow('2026-11-26 10:00');
        $this->assertSame('mm_locked_tasks', $bank->moneyMarketLock($user));   // no plan of the month
        $this->chooseAPlan($user);
        $this->assertSame('mm_locked_tasks', $bank->moneyMarketLock($user));   // a Required activity still to do

        DB::table('activity_completions')->insert(['student_id' => $user->id, 'activity_id' => $event, 'activity_type' => 'mba',
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $this->assertSame('mm_locked_spend', $bank->moneyMarketLock($user));   // the plan is about a fifth of the salary

        LedgerService::post($user->id, 'debit', 'Needs', round(MonthEndPenaltyService::salary() * 0.75, 2), 'Rent', ['sid' => '1']);
        $this->assertNull($bank->moneyMarketLock($user));
    }

    public function test_the_month_end_job_fines_only_students_without_a_plan(): void
    {
        $without = $this->student();
        $with = $this->student();
        $this->chooseAPlan($with);

        $this->artisan('penalty:apply')->expectsOutputToContain($without->display_name)->doesntExpectOutputToContain($with->display_name . ':');
        $this->assertSame(0, Transaction1::where('category', 'Fine')->count());

        $this->artisan('penalty:apply --write')->assertSuccessful();
        $this->assertSame(1, Transaction1::where('user_id', $without->id)->where('category', 'Fine')->count());
        $this->assertSame(0, Transaction1::where('user_id', $with->id)->whereIn('category', ['Penalty', 'Fine'])->count());
    }
}
