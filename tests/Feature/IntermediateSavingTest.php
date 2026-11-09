<?php

namespace Tests\Feature;

use App\Http\Controllers\BankController;
use App\Models\Activity;
use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\BankTransferService;
use App\Services\SaverService;
use App\Services\WantsPlanService;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The Intermediate saves as much as it can (Daniel, 2026-11-03/04/08): the
 * share of the salary for the Emergency Fund is the student's to choose, the
 * Money Market asks only for the month's tasks, Spending 4 is the
 * Intermediate's own plans (about 15% with the Low activity), "How much have I
 * saved?" replaces the 50/30/20 tasks and the Match, and the bank shows what
 * was saved this month.
 */
class IntermediateSavingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 12, 6, 10, 0, 0, 'Europe/Berlin'));
        Level::flush();
    }

    private function classAt(string $level, ?string $lowActivity = null): SchoolClass
    {
        $grade = Grade::create(['sid' => '1', 'name' => $level . ' grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        if ($lowActivity) {
            DB::table('mba_position')->insert(['user_id' => 1, 'activity_key' => 'lba_position', 'sid' => '1', 'cid' => $class->id,
                'activity_id' => Activity::where('title', $lowActivity)->value('id'), 'required' => 1, 'optional' => 0,
                'created_at' => now(), 'updated_at' => now()]);
        }

        return $class;
    }

    private function student(string $level = 'Intermediate', ?int $fundPercent = null): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $this->classAt($level)->id, 'created_at' => '2026-10-01']);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        $account->forceFill(['is_open_emergency_account' => 1, 'emergency_fund_percent' => $fundPercent, 'primary_savings_account_amount' => 100])->save();
        \App\Models\AutoDebitRequest::create(['user_id' => $user->id, 'sid' => 1, 'type' => 'Rent']);   // the Overview, not the sign-up steps

        return $user;
    }

    private function fundTransfer(User $user): ?float
    {
        $amount = Transaction1::where('user_id', $user->id)->where('description', 'Auto Transfer to Emergency Fund')->value('amount');

        return $amount === null ? null : (float) $amount;
    }

    public function test_the_level_of_the_class(): void
    {
        $this->assertTrue(Level::saver($this->student('Intermediate')));
        $this->assertTrue(Level::saver($this->student('Advance Economist')));
        $this->assertFalse(Level::saver($this->student('Rookie')));
        $this->assertFalse(Level::saver($this->student('Junior')));
    }

    public function test_the_intermediate_chooses_its_share_for_the_fund(): void
    {
        $user = $this->student();

        $this->actingAs($user)->post(route('bank.fund_percent'), ['percent' => 35])->assertSessionHas('success');
        $this->assertSame(35, (int) BankAccount::where('student_id', $user->id)->value('emergency_fund_percent'));
        $this->actingAs($user)->post(route('bank.fund_percent'), ['percent' => 33])->assertSessionHasErrors('percent');

        app(BankController::class)->creditMonthlySalary($user->id);
        $this->assertSame(1383.34, $this->fundTransfer($user));   // 35% of Ƶ 3,952.40
    }

    public function test_none_at_all_is_a_choice_too(): void
    {
        $user = $this->student('Intermediate', 0);

        app(BankController::class)->creditMonthlySalary($user->id);

        $this->assertNull($this->fundTransfer($user));
    }

    public function test_a_rookie_keeps_the_twenty_percent(): void
    {
        $user = $this->student('Rookie', 35);   // a share left from before doesn't count

        $this->actingAs($user)->post(route('bank.fund_percent'), ['percent' => 35])->assertForbidden();
        app(BankController::class)->creditMonthlySalary($user->id);

        $this->assertSame(790.48, $this->fundTransfer($user));
    }

    public function test_the_money_market_asks_only_for_the_tasks_from_the_intermediate(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 12, 27, 10, 0, 0, 'Europe/Berlin'));
        $intermediate = $this->student();
        $rookie = $this->student('Rookie');
        foreach ([$intermediate, $rookie] as $user) {   // the plan of the month chosen: the tasks are done
            DB::table('wants_plan_choices')->insert(['user_id' => $user->id, 'kind' => 'plan', 'month' => WantsPlanService::month(), 'total' => 0, 'created_at' => now(), 'updated_at' => now()]);
        }
        $bank = app(BankTransferService::class);

        $this->assertNull($bank->moneyMarketLock($intermediate));
        $this->assertSame('mm_locked_spend', $bank->moneyMarketLock($rookie));   // nothing spent yet
    }

    public function test_spending_4_is_the_intermediates_own_plans_about_fifteen_percent(): void
    {
        $class = $this->classAt('Intermediate', 'Beach Day');
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $budget = (float) Activity::where('title', 'Beach Day')->value('budget');

        $this->assertSame('mid', WantsPlanService::kindFor($user));
        $this->assertSame(round(0.15 * WantsPlanService::salary() - $budget, 2), WantsPlanService::minimum('mid', $class->id));
        $offers = WantsPlanService::offers($user);
        $this->assertCount(4, $offers);
        $this->assertTrue($offers->every(fn ($p) => $p->kind === 'mid' && $p->monthlyCost() >= 410 && $p->monthlyCost() <= 560));
        $this->assertSame('low', WantsPlanService::kindFor($this->student('Rookie')));
    }

    public function test_every_plan_description_adds_up_to_its_cost(): void
    {
        foreach (DB::table('wants_plans')->get() as $plan) {
            $texts = array_filter(array_merge([$plan->description], array_column(json_decode($plan->translations, true) ?: [], 'description')));
            foreach ($texts as $text) {
                preg_match_all('/\(Ƶ\s?([\d.,]+)\)/u', $text, $m);
                if ($m[1]) {
                    $this->assertSame((int) round($plan->cost), array_sum(array_map(fn ($a) => (int) preg_replace('/\D/', '', $a), $m[1])), $plan->name);
                }
            }
        }
    }

    public function test_how_much_have_i_saved_and_the_bank_card(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 12, 4, 10, 0, 0, 'Europe/Berlin'));   // before payday: opening the bank pays no salary
        $user = $this->student('Intermediate', 25);
        $account = BankAccount::where('student_id', $user->id)->first();
        foreach ([['emergency', 'credit', 'Monthly transfer', 500], ['money_market', 'credit', 'Own Transfer', 200], ['money_market', 'debit', 'Own Transfer', 100], ['emergency', 'credit', 'Interest', 10]] as [$acc, $type, $category, $amount]) {
            SavingsTransaction::create(['user_id' => $user->id, 'bank_account_id' => $account->id, 'account' => $acc, 'type' => $type, 'category' => $category,
                'amount' => $amount, 'balance' => 0, 'description' => $category, 'transaction_date' => now()]);
        }

        $this->assertSame(['amount' => 600.0, 'percent' => 15.2], SaverService::savedThisMonth($user->id));
        $this->actingAs($user)->get(route('task.page', ['task' => 'task-5']))->assertOk()
            ->assertSee(__('savings_check.q1'))->assertSee(zeds(600));
        $this->actingAs($user)->get(route('bank.index'))->assertOk()->assertSee(__('bank_ui.saver_title'))->assertSee(zeds(600));
        $this->actingAs($this->student('Rookie'))->get(route('bank.index'))->assertOk()->assertDontSee(__('bank_ui.saver_title'));
    }

    public function test_admins_offer_task_5_from_the_intermediate_and_tasks_3_and_4_before(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $intermediate = GradeLevel::where('name', 'Intermediate')->value('id');
        $rookie = GradeLevel::where('name', 'Rookie')->value('id');

        $this->actingAs($admin)->get('/admin/finhero/activities?level=' . $intermediate)->assertOk()
            ->assertSee('How much have I saved?')->assertDontSee('Budget Rule')->assertDontSee('Needs, Wants &amp; Savings Match', false);
        $this->actingAs($admin)->get('/admin/finhero/activities?level=' . $rookie)->assertOk()
            ->assertDontSee('How much have I saved?')->assertSee('Budget Rule');
    }
}
