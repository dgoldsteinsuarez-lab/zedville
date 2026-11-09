<?php

namespace Tests\Feature;

use App\Http\Controllers\BankController;
use App\Models\Activity;
use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Models\WantsPlan;
use App\Models\WantsPlanCharge;
use App\Models\WantsPlanChoice;
use App\Services\LedgerService;
use App\Services\WantsPlanService;
use App\Support\TransactionText;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Spending 4, the plan of the month (Daniel, 2026-10-31): one plan a month
 * after the question of the day; Low Spending 4 next to the class's Low
 * Spending activity - together at least 20% of the salary - or High Spending
 * 4 on its own, every plan at least 20% (2026-11-08: 24% at first, which with
 * the bills, the fund and the month's shopping takes a student to 91%; then
 * 4 points went to the monthly donation of 4-6%, DonationService).
 */
class WantsPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-03 10:00'); // a Tuesday: Saturdays 7, 14, 21, 28
    }

    private function classWith(?string $lowActivity = null, string $kind = 'low'): SchoolClass
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'spending4' => $kind]);
        if ($lowActivity) {
            DB::table('mba_position')->insert(['user_id' => 1, 'activity_key' => 'lba_position', 'sid' => '1', 'cid' => $class->id,
                'activity_id' => Activity::where('title', $lowActivity)->value('id'), 'required' => 1, 'optional' => 0,
                'created_at' => now(), 'updated_at' => now()]);
        }

        return $class;
    }

    private function student(SchoolClass $class, float $balance = 2000): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0]);
        Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->subDay(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance, 'is_penalty' => false]);

        return $user;
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    public function test_forty_low_and_twelve_high_plans_and_twelve_low_spending_activities(): void
    {
        $salary = WantsPlanService::salary();
        $low = WantsPlan::where('is_active', true)->where('kind', 'low')->get();
        $high = WantsPlan::where('is_active', true)->where('kind', 'high')->get();

        $this->assertCount(40, $low);
        $this->assertCount(12, $high);
        $this->assertTrue($low->every(fn ($p) => $p->monthlyCost() >= 710 && $p->monthlyCost() <= 900));
        $this->assertTrue($high->every(fn ($p) => $p->monthlyCost() >= WantsPlanService::HIGH_MIN * $salary));
        $this->assertTrue($low->contains('name', 'Mexican cooking course') && $low->contains('name', 'Cinema month') && $low->contains('name', 'Long weekend in Smith City'));
        $this->assertTrue($high->contains('name', 'Luxury staycation in Zedville') && $high->contains('name', 'Flight and 4 days in Galbraith Bay'));
        $this->assertSame(12, Activity::where('type', 'low')->count());
        $this->assertSame(50.0, (float) Activity::where('title', 'Community Food Drive')->value('budget'));
    }

    public function test_low_spending_4_with_the_low_activity_is_at_least_20_percent(): void
    {
        $target = WantsPlanService::LOW_TOTAL * WantsPlanService::salary();
        foreach (['Community Food Drive', 'Birthday Party Budget', 'Cosy Winter'] as $title) {
            $student = $this->student($this->classWith($title));
            $budget = (float) Activity::where('title', $title)->value('budget');
            $offers = WantsPlanService::offers($student);

            $this->assertCount(4, $offers, $title);
            foreach ($offers as $plan) {
                $this->assertGreaterThanOrEqual(round($target, 2), round($budget + $plan->monthlyCost(), 2), "$title + {$plan->name}");
            }
        }

        // no Low Spending activity: the dearest Low plans, the admin is warned
        $this->assertCount(4, WantsPlanService::offers($this->student($this->classWith(null))));
    }

    public function test_high_spending_4_plans_are_each_at_least_20_percent(): void
    {
        $student = $this->student($this->classWith(null, 'high'));
        $offers = WantsPlanService::offers($student);

        $this->assertCount(4, $offers);
        $this->assertTrue($offers->every(fn ($p) => $p->kind === 'high' && $p->monthlyCost() >= WantsPlanService::HIGH_MIN * WantsPlanService::salary()));
        $this->actingAs($student)->get(route('dashboard'))->assertOk()->assertSee('High Spending 4')->assertSee($offers->first()->name);
    }

    public function test_a_new_four_every_month_the_same_for_the_class(): void
    {
        $class = $this->classWith('Cosy Winter'); // every Low plan reaches 20% with it
        $student = $this->student($class);
        $classmate = $this->student($class);
        $this->assertSame(WantsPlanService::offers($student)->pluck('id')->all(), WantsPlanService::offers($classmate)->pluck('id')->all());

        $seen = [];
        for ($m = 0; $m < 10; $m++) {
            Carbon::setTestNow(Carbon::create(2026, 9, 15)->addMonthsNoOverflow($m));
            foreach (WantsPlanService::offers($student) as $level => $plan) {
                $seen[$level][] = $plan->id;
            }
        }
        foreach ($seen as $ids) {
            $this->assertCount(10, array_unique($ids));
        }
    }

    public function test_the_box_comes_once_a_month_and_only_one_plan_is_chosen(): void
    {
        $student = $this->student($this->classWith('Birthday Party Budget'));
        $offers = WantsPlanService::offers($student);
        $plan = $offers->last();

        $this->actingAs($student)->get(route('dashboard'))->assertOk()
            ->assertSee('Low Spending 4')->assertSee('Your plan for November')->assertSee('Choose this plan')->assertSee($plan->name)
            ->assertDontSee('Save the money')->assertDontSee('Three low-budget plans');

        $other = WantsPlan::whereNotIn('id', $offers->pluck('id'))->first();
        $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $other->id])->assertStatus(422);

        $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $plan->id])->assertOk()
            ->assertJson(['id' => $plan->id, 'paid_today' => 'Paid today: ' . zeds($plan->cost)]);
        $this->assertSame(round(2000 - $plan->cost, 2), $this->balance($student));
        $this->assertSame('plan', WantsPlanChoice::where('user_id', $student->id)->value('kind'));

        $this->actingAs($student)->get(route('dashboard'))->assertOk()->assertDontSee('Choose this plan');
        $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $plan->id])->assertStatus(409);

        Carbon::setTestNow('2026-12-01 09:00');
        $this->actingAs($student)->get(route('dashboard'))->assertOk()->assertSee('Your plan for December');

        // the bank line in the student's language
        $line = Transaction1::where('user_id', $student->id)->where('category', 'Wants')->first();
        app()->setLocale('es');
        $this->assertSame('Plan del mes: ' . $plan->translations['es']['name'], TransactionText::forStudent($line->description));
    }

    public function test_a_weekly_plan_is_for_the_whole_month_and_never_below_zero(): void
    {
        // an admin's weekly plan, the only one offered
        WantsPlan::query()->update(['is_active' => false]);
        $weekly = WantsPlan::create(['kind' => 'low', 'name' => 'Climbing gym every Saturday', 'description' => 'Every Saturday at the climbing gym.',
            'cost' => 200, 'times_per_month' => 4, 'is_active' => true, 'sort_order' => 1,
            'translations' => ['es' => ['name' => 'Rocódromo cada sábado', 'description' => 'Cada sábado en el rocódromo.']]]);
        Carbon::setTestNow('2026-11-20 10:00'); // two Saturdays already gone
        $student = $this->student($this->classWith('Cosy Winter'), 500);

        $card = WantsPlanService::card($weekly);
        $this->assertSame('Ƶ 200.00 every Saturday', $card['cost_line']);
        $this->assertSame('This month: 4 × Ƶ 200.00 = Ƶ 800.00', $card['total_line']);
        $this->assertSame('on 7, 14, 21 and 28 November', $card['dates']);

        $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $weekly->id])->assertOk()
            ->assertJson(['paid_today' => 'Paid today: Ƶ 400.00']);
        $this->assertSame(100.0, $this->balance($student));

        Carbon::setTestNow('2026-11-21 07:00');
        $this->artisan('plans:charge')->assertSuccessful(); // Ƶ 100 left: not enough for Ƶ 200
        $this->assertSame(100.0, $this->balance($student));
        $this->assertSame(['charged', 'charged', 'waiting', 'pending'], WantsPlanCharge::orderBy('due_date')->pluck('status')->all());
    }

    /** An admin's plan, the only one offered. */
    private function onlyPlan(float $cost, int $timesPerMonth = 1): WantsPlan
    {
        WantsPlan::query()->update(['is_active' => false]);

        return WantsPlan::create(['kind' => 'low', 'name' => 'Long weekend in Hayek Peaks', 'description' => 'Two days in the mountains.',
            'cost' => $cost, 'times_per_month' => $timesPerMonth, 'is_active' => true, 'sort_order' => 1]);
    }

    public function test_without_money_the_payment_waits_and_is_paid_right_after_the_salary(): void
    {
        // Daniel, 2026-11-02: "si el salario no ha llegado aún, se descuenta justo cuando
        // llegue el salario, y se debe notificar en este mensaje"
        $trip = $this->onlyPlan(800);
        Carbon::setTestNow('2026-11-02 10:00'); // the salary comes on the 6th
        $student = $this->student($this->classWith('Cosy Winter'), 300);
        Transaction1::where('user_id', $student->id)->update(['transaction_date' => '2026-10-31 18:00']); // October's money
        BankAccount::where('student_id', $student->id)->update(['is_open_emergency_account' => 1]);

        $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $trip->id])->assertOk()
            ->assertJson(['paid_on' => null, 'paid_today' => null, 'waiting' => 'Your salary hasn\'t arrived yet, so there isn\'t enough money in your current account today. '
                . 'The Ƶ 800.00 will be paid automatically when your salary arrives, on 6 November.']);
        $this->assertSame(300.0, $this->balance($student));
        $this->assertSame('waiting', WantsPlanCharge::value('status'));

        Carbon::setTestNow('2026-11-04 07:00');
        $this->artisan('plans:charge')->assertSuccessful(); // still no money: it keeps waiting
        $this->assertSame('waiting', WantsPlanCharge::value('status'));

        // the nightly run on the 6th: the salary, the Emergency Fund's 20 %, then the plan
        Carbon::setTestNow(Carbon::create(2026, 11, 6, 0, 15, 0, 'Europe/Berlin'));
        app(BankController::class)->creditMonthlySalary($student->id);

        $lines = Transaction1::where('user_id', $student->id)->orderBy('id')->get();
        $this->assertSame(['Opening balance', 'Monthly Salary Credit - November 2026', 'Auto Transfer to Emergency Fund', 'Monthly plan: Long weekend in Hayek Peaks'],
            $lines->pluck('description')->all());
        $this->assertSame('2026-11-06 08:10:00', Carbon::parse($lines->last()->transaction_date)->format('Y-m-d H:i:s'));
        $this->assertSame(round(300 + WantsPlanService::salary() - 790.48 - 800, 2), $this->balance($student));
        $this->assertSame('charged', WantsPlanCharge::value('status'));
    }

    public function test_after_the_salary_the_payment_waits_for_money_at_the_latest_the_next_salary(): void
    {
        $trip = $this->onlyPlan(800);
        Carbon::setTestNow('2026-11-20 10:00');
        $student = $this->student($this->classWith('Cosy Winter'), 300); // November's money is in, most of it spent

        $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $trip->id])->assertOk()
            ->assertJson(['paid_on' => null, 'waiting' => 'There isn\'t enough money in your current account today. The Ƶ 800.00 will be paid automatically '
                . 'as soon as there is, at the latest when your next salary arrives, on 6 December.']);

        // money comes in: the next morning's run pays it
        LedgerService::post($student->id, 'credit', 'Fund Transfer', 600, 'Transfer from a classmate', ['sid' => '1']);
        Carbon::setTestNow('2026-11-21 07:00');
        $this->artisan('plans:charge')->assertSuccessful();
        $this->assertSame(100.0, $this->balance($student));
        $this->assertSame('charged', WantsPlanCharge::value('status'));
    }

    public function test_a_new_citizen_pays_with_the_first_salary(): void
    {
        // chosen while the bank was being set up, before any money (Uno Suarez, 2026-11-02)
        $trip = $this->onlyPlan(800);
        Carbon::setTestNow('2026-11-02 10:00');
        $student = $this->student($this->classWith('Cosy Winter'));
        Transaction1::where('user_id', $student->id)->delete(); // nothing on the account yet

        $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $trip->id])->assertOk()
            ->assertJson(['waiting' => 'Your salary hasn\'t arrived yet, so there isn\'t enough money in your current account today. '
                . 'The Ƶ 800.00 will be paid automatically when your salary arrives.']);

        app(\App\Services\StatementGenerator::class)->generateForUser($student->id);

        // after the salary (and the month's needs it pays), the plan
        $this->assertSame('charged', WantsPlanCharge::value('status'));
        [$plan, $before] = Transaction1::where('user_id', $student->id)->orderByDesc('id')->take(2)->get()->all();
        $this->assertSame('Monthly plan: Long weekend in Hayek Peaks', $plan->description);
        $this->assertSame(round((float) $before->balance - 800, 2), (float) $plan->balance);
        $this->assertSame(1, Transaction1::where('user_id', $student->id)->where('description', 'like', 'Monthly Salary%')->count());
    }

    public function test_the_waiting_message_in_spanish_and_in_a_hybrid_class(): void
    {
        $trip = $this->onlyPlan(800);
        Carbon::setTestNow('2026-11-02 10:00');
        foreach (['standard' => 'Tu nómina aún no ha llegado, así que hoy no hay suficiente dinero en tu cuenta corriente. '
                . "Los 800,00\u{00A0}Ƶ se cargarán en tu cuenta automáticamente cuando llegue tu nómina, el 6 de noviembre.",
            // a long text: in English, with its own date
            'hybrid' => 'Your salary hasn\'t arrived yet, so there isn\'t enough money in your current account today. '
                . "The 800,00\u{00A0}Ƶ will be paid automatically when your salary arrives, on 6 November."] as $level => $text) {
            $class = $this->classWith('Cosy Winter');
            $class->update(['default_language' => 'es', 'language_level' => $level]);
            $student = $this->student($class, 100);
            Transaction1::where('user_id', $student->id)->update(['transaction_date' => '2026-10-31 18:00']);

            $this->actingAs($student)->postJson(route('wants-plan.choose'), ['plan_id' => $trip->id])->assertOk()->assertJson(['waiting' => $text]);
        }
    }

    public function test_only_students_with_a_bank_account_get_the_box(): void
    {
        $noBank = User::factory()->create(['role' => 4, 'sid' => '1']);
        $tutor = User::factory()->create(['role' => 3, 'sid' => '1']);

        $this->assertFalse(WantsPlanService::needsChoice($noBank));
        $this->assertFalse(WantsPlanService::needsChoice($tutor));
        $this->assertTrue(WantsPlanService::needsChoice($this->student($this->classWith(null))));
    }
}
