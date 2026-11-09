<?php

namespace Tests\Feature\Bank;

use App\Http\Controllers\BankController;
use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\LedgerService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-11-08: the month's shopping from the consumer survey is charged
 * every month with the bills - it only was the month the survey was first
 * filled, so nobody reached the 91% the Spending activities are made for.
 */
class MonthlyShoppingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 12, 7, 10, 0, 0, 'Europe/Berlin'));
    }

    private function student(float $balance = 4000, array $survey = []): User
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => '2026-10-01']);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        Transaction1::create(['user_id' => $user->id, 'sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => '2026-12-06 09:00',
            'description' => 'Monthly Salary Credit - December 2026', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance, 'is_penalty' => false]);
        // the November survey: applies from then on
        ConsumerSurvey::create($survey + ['user_id' => $user->id, 'sid' => '1', 'diet' => 'omnivore', 'groceries' => [], 'basket_cost' => 118.28,
            'lunch' => 20, 'transport' => 80, 'activities' => 50, 'restaurants' => 50, 'total_cost' => 318.28, 'survey_month' => 'Nov-2026']);

        return $user;
    }

    private function shopping(User $user): array
    {
        return Transaction1::where('user_id', $user->id)->where('type', 'debit')
            ->whereNotIn('description', ['Rent', 'School Fees', 'Internet', 'Utility - Electricity', 'Utility - Water'])
            ->orderBy('id')->get()->map(fn ($t) => [$t->description, $t->category, (float) $t->amount])->all();
    }

    public function test_the_surveys_month_comes_with_the_bills_on_the_7th(): void
    {
        $user = $this->student();

        app(BankController::class)->ensureMonthlyBills($user);
        app(BankController::class)->ensureMonthlyBills($user);   // and only once

        $this->assertSame([
            ['Grocery – Omnivore', 'Needs', 118.28],
            ['School Lunch', 'Needs', 20.0],
            ['Transportation', 'Needs', 80.0],
            ['Activities & Hobbies', 'Wants', 50.0],
            ['Eating Out', 'Wants', 50.0],
        ], $this->shopping($user));
        $this->assertSame(5, Transaction1::where('user_id', $user->id)->whereDate('transaction_date', '2026-12-07')
            ->whereNotIn('description', ['Rent', 'School Fees', 'Internet', 'Utility - Electricity', 'Utility - Water'])->count());
    }

    public function test_not_again_in_the_month_the_first_survey_charged_it(): void
    {
        $user = $this->student();
        LedgerService::post($user->id, 'debit', 'Needs', 118.28, 'Grocery – Omnivore', ['sid' => '1', 'transaction_date' => '2026-12-03 10:00']);
        LedgerService::post($user->id, 'debit', 'Needs', 20, 'School Lunch', ['sid' => '1', 'transaction_date' => '2026-12-03 10:00']);

        app(BankController::class)->ensureMonthlyBills($user);

        $this->assertSame(1, Transaction1::where('user_id', $user->id)->where('description', 'like', 'Grocery – %')->count());
        $this->assertSame(1, Transaction1::where('user_id', $user->id)->where('description', 'School Lunch')->count());
        $this->assertSame(1, Transaction1::where('user_id', $user->id)->where('description', 'Eating Out')->count());
    }

    public function test_nothing_for_a_skipped_survey(): void
    {
        $user = $this->student(4000, ['diet' => null, 'basket_cost' => 0, 'lunch' => 0, 'transport' => 0, 'activities' => 0, 'restaurants' => 0, 'total_cost' => 0]);

        app(BankController::class)->ensureMonthlyBills($user);

        $this->assertSame([], $this->shopping($user));
    }

    public function test_it_waits_for_the_money_like_a_bill(): void
    {
        $user = $this->student(1500);   // the bills take almost all of it

        app(BankController::class)->ensureMonthlyBills($user);
        $this->assertGreaterThanOrEqual(0.0, (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance'));
        $before = count($this->shopping($user));

        LedgerService::post($user->id, 'credit', 'Income', 500, 'Bonus', ['sid' => '1']);
        app(BankController::class)->ensureMonthlyBills($user);

        $this->assertGreaterThan($before, count($this->shopping($user)));
    }
}
