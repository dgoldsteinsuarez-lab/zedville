<?php

namespace Tests\Feature\Bank;

use App\Models\AutopilotPeriod;
use App\Models\BankAccount;
use App\Models\Beneficiary;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * The bank review (Daniel, 2026-10-29: "check if everything is working,
 * transfers, statement and the other functions", then "ok, do it"): every way
 * money was created or lost, and the functions that didn't work.
 */
class BankMoneyRulesTest extends TestCase
{
    use RefreshDatabase;

    private function student(float $balance = 1000, int $sid = 1, array $open = []): array
    {
        // one class per school: money only goes to classmates
        $class = SchoolClass::firstOrCreate(['sid' => $sid, 'name' => 'Class of school ' . $sid]);
        $user = User::factory()->create(['role' => 4, 'sid' => $sid, 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => $sid, 'student_name' => $user->name, 'student_email' => $user->email,
            'primary_savings_account_number' => '123000-' . random_int(100000, 999999),
            'emergency_fund_account_number' => (string) random_int(1000000000, 9999999999),
            'money_market_account_number' => (string) random_int(1000000000, 9999999999),
            'card_iban' => 'ZV' . random_int(1000000000, 9999999999), 'card_pin' => '1234',
        ]);
        foreach ($open as $key => $amount) {
            $account->{$key === 'emergency' ? 'is_open_emergency_account' : 'is_open_money_market_account'} = 1;
            $account->{$key === 'emergency' ? 'emergency_fund_account_amount' : 'money_market_account_amount'} = $amount;
        }
        $account->save();
        Transaction1::create(['user_id' => $user->id, 'sid' => $sid, 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance, 'is_penalty' => false]);

        return [$user, $account->fresh()];
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    private function send(User $from, array $fields)
    {
        return $this->actingAs($from)->post('/bank-account/transfer', $fields + ['from_account' => 'Primary Savings Account', 'type' => 'now']);
    }

    public function test_a_friend_gets_the_money_with_their_account_number_and_name(): void
    {
        [$a] = $this->student(500);
        [$b, $bAccount] = $this->student(100);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => strtoupper($b->name), 'amount' => 40])
            ->assertRedirect(route('bank.transfer', ['to' => 'friend']))->assertSessionHas('success');

        $this->assertEquals(460, $this->balance($a));
        $this->assertEquals(140, $this->balance($b));
        $this->assertSame('Fund Transfer', Transaction1::where('user_id', $b->id)->latest('id')->value('category'));
    }

    public function test_the_iban_works_like_the_account_number(): void
    {
        [$a] = $this->student(500);
        [$b, $bAccount] = $this->student(0);
        $spaced = trim(chunk_split($bAccount->card_iban, 4, ' '));

        $this->send($a, ['to' => 'friend', 'account_number' => strtolower($spaced), 'beneficiary_name' => $b->name, 'amount' => 25])->assertSessionHas('success');

        $this->assertEquals(25, $this->balance($b));
    }

    public function test_a_wrong_name_or_too_little_money_moves_nothing(): void
    {
        [$a] = $this->student(50);
        [$b, $bAccount] = $this->student(0);
        $to = ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name, 'amount' => 10];

        $this->send($a, ['beneficiary_name' => 'Somebody Else'] + $to)->assertSessionHas('error', __('flash.no_beneficiary'));
        $this->send($a, ['amount' => 51] + $to)->assertSessionHas('error', __('flash.insufficient_balance'));
        $this->send($a, ['amount' => 0] + $to)->assertSessionHasErrors('amount');

        $this->assertEquals(50, $this->balance($a));
        $this->assertSame(0, Transaction1::where('user_id', $b->id)->where('category', 'Fund Transfer')->count());
    }

    /** Daniel, 2026-10-29: "better no pin for transfer" - like online banking, the check step confirms. */
    public function test_no_card_pin_is_asked_to_send_money_or_pay_a_bill(): void
    {
        [$a] = $this->student(100);
        [$b, $bAccount] = $this->student(0);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name, 'amount' => 10])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->actingAs($a)->post(route('bank.store_pay_bill'), ['beneficiary_name' => 'Electricity Co', 'account_number' => '555', 'amount' => 5, 'type' => 'now'])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertEquals(85, $this->balance($a));
        $this->actingAs($a)->get(route('bank.transfer'))->assertDontSee('name="pin"', false);
    }

    /** Daniel, 2026-10-29: "transfers only same class". */
    public function test_money_goes_only_to_classmates(): void
    {
        [$a] = $this->student(100);
        [$b, $bAccount] = $this->student(0);
        $b->update(['grade' => SchoolClass::create(['sid' => 1, 'name' => 'Another class'])->id]);
        $to = ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name, 'amount' => 10];

        $this->send($a, $to)->assertSessionHas('error', __('flash.other_class'));
        $this->actingAs($a)->post(route('bank.beneficiary_store'), ['name' => $b->name, 'account_number' => $bAccount->primary_savings_account_number])
            ->assertSessionHas('error', __('flash.other_class'));
        $this->assertEquals(100, $this->balance($a));
        $this->assertSame(0, Beneficiary::where('user_id', $a->id)->count());
        $this->actingAs($a)->get(route('bank.transfer', ['to' => 'friend']))->assertSee('You can send money to the students in your class.');
    }

    /** It doubled the money: the same row was read twice and saved twice. */
    public function test_an_account_can_t_pay_into_itself(): void
    {
        [$a, $account] = $this->student(100, 1, ['emergency' => 790.48]);

        $this->send($a, ['to' => 'friend', 'from_account' => 'Emergency Fund Account', 'account_number' => $account->emergency_fund_account_number,
            'beneficiary_name' => $a->name, 'amount' => 790.48])->assertSessionHas('error', __('flash.same_account'));

        $this->assertEquals(790.48, (float) $account->fresh()->emergency_fund_account_amount);
    }

    public function test_money_moves_between_own_accounts_and_each_side_has_its_line(): void
    {
        [$a, $account] = $this->student(1000, 1, ['emergency' => 0]);

        $this->send($a, ['to' => 'own', 'own_to' => 'emergency', 'amount' => 300])->assertSessionHas('success');
        $this->send($a, ['to' => 'own', 'from_account' => 'Emergency Fund Account', 'own_to' => 'current', 'amount' => 100])->assertSessionHas('success');

        $this->assertEquals(800, $this->balance($a));
        $this->assertEquals(200, (float) $account->fresh()->emergency_fund_account_amount);
        $lines = SavingsTransaction::where('user_id', $a->id)->where('account', 'emergency')->orderBy('id')->get();
        $this->assertSame(['credit', 'debit'], $lines->pluck('type')->all());
        $this->assertEquals(200, $lines->last()->balance);
        $this->assertSame('Own Transfer', Transaction1::where('user_id', $a->id)->latest('id')->value('category'));
    }

    public function test_a_closed_savings_account_takes_nothing(): void
    {
        [$a] = $this->student(500);
        [$b, $bAccount] = $this->student(0);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->emergency_fund_account_number, 'beneficiary_name' => $b->name, 'amount' => 10])
            ->assertSessionHas('error', __('flash.recipient_account_closed'));
        $this->assertEquals(500, $this->balance($a));
    }

    /** A double click (or a stale tab) took the first deposit again and set the fund back to it. */
    public function test_opening_the_emergency_fund_twice_charges_once(): void
    {
        [$a, $account] = $this->student(3000);

        $this->actingAs($a)->post('/bank-account/emengercy-fund-account');
        $this->actingAs($a)->post('/bank-account/emengercy-fund-account')->assertSessionHas('success', __('flash.ef_already_open'));

        $deposit = round(config('zedville.monthly_salary') * 0.2, 2);
        $this->assertEquals(round(3000 - $deposit, 2), $this->balance($a));
        $this->assertEquals($deposit, (float) $account->fresh()->emergency_fund_account_amount);
        $this->assertSame(1, SavingsTransaction::where('user_id', $a->id)->where('category', 'Opening deposit')->count());
    }

    public function test_opening_the_money_market_twice_keeps_its_money(): void
    {
        [$a, $account] = $this->student(100, 1, ['money_market' => 250]);

        $this->actingAs($a)->post('/bank-account/money-market-amount')->assertSessionHas('success', __('flash.mm_already_open'));

        $this->assertEquals(250, (float) $account->fresh()->money_market_account_amount);
    }

    /** "Later" ran at once, then the every-minute scheduler ran it again every minute. */
    public function test_a_later_transfer_runs_on_its_date_once(): void
    {
        [$a] = $this->student(1000);
        [$b, $bAccount] = $this->student(0);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name,
            'amount' => 10, 'type' => 'later', 'paydate' => now()->addDays(10)->toDateString()])->assertSessionHas('success');
        $this->assertEquals(1000, $this->balance($a), 'Nothing moves before the date.');

        Artisan::call('transfers:run-due');
        $this->assertEquals(1000, $this->balance($a));

        $this->travel(11)->days();
        Artisan::call('transfers:run-due');
        Artisan::call('transfers:run-due');
        $this->assertEquals(990, $this->balance($a));
        $this->assertEquals(10, $this->balance($b));
        $this->assertSame('done', Transfer::first()->status);
    }

    /** A recurring transfer re-dispatched itself in an endless loop (a column that didn't exist). */
    public function test_a_standing_order_runs_each_date_once_and_stops_after_its_end(): void
    {
        [$a] = $this->student(100);
        [$b, $bAccount] = $this->student(0);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name,
            'amount' => 20, 'type' => 'recurring', 'frequency' => 'weekly',
            'start_date' => now()->toDateString(), 'end_date' => now()->addDays(15)->toDateString()])->assertSessionHas('success');
        $this->assertEquals(80, $this->balance($a), 'The first date is today: it runs now.');

        foreach ([7, 7, 7, 7] as $days) {
            $this->travel($days)->days();
            Artisan::call('transfers:run-due');
            Artisan::call('transfers:run-due');
        }

        $this->assertEquals(40, $this->balance($a), 'Three dates within 15 days: today, +7, +14.');
        $this->assertEquals(60, $this->balance($b));
        $this->assertSame('done', Transfer::first()->status);
    }

    public function test_a_standing_order_skips_a_date_without_enough_money(): void
    {
        [$a] = $this->student(30);
        [$b, $bAccount] = $this->student(0);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name,
            'amount' => 20, 'type' => 'recurring', 'frequency' => 'monthly',
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addMonths(3)->toDateString()]);
        $this->travel(2)->days();
        Artisan::call('transfers:run-due');
        $this->travel(1)->months();
        Artisan::call('transfers:run-due');

        $this->assertEquals(10, $this->balance($a), 'The second date had only 10: skipped, never below 0.');
        $transfer = Transfer::first();
        $this->assertSame('scheduled', $transfer->status);
        $this->assertSame('insufficient_balance', $transfer->failure);
    }

    public function test_yearly_is_a_frequency_the_form_can_use(): void
    {
        [$a] = $this->student(100);
        [$b, $bAccount] = $this->student(0);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name,
            'amount' => 5, 'type' => 'recurring', 'frequency' => 'yearly',
            'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addYears(2)->toDateString()])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
    }

    public function test_a_past_date_is_refused(): void
    {
        [$a] = $this->student(100);
        [$b, $bAccount] = $this->student(0);

        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name,
            'amount' => 5, 'type' => 'later', 'paydate' => now()->subDay()->toDateString()])->assertSessionHasErrors('paydate');
    }

    public function test_a_scheduled_transfer_can_be_cancelled(): void
    {
        [$a] = $this->student(100);
        [$b, $bAccount] = $this->student(0);
        $this->send($a, ['to' => 'friend', 'account_number' => $bAccount->primary_savings_account_number, 'beneficiary_name' => $b->name,
            'amount' => 5, 'type' => 'later', 'paydate' => now()->addDays(3)->toDateString()]);
        $transfer = Transfer::first();

        $this->actingAs($b)->post(route('bank.transfer_cancel', $transfer))->assertNotFound();
        $this->actingAs($a)->post(route('bank.transfer_cancel', $transfer))->assertSessionHas('success');

        $this->travel(4)->days();
        Artisan::call('transfers:run-due');
        $this->assertEquals(100, $this->balance($a));
        $this->assertSame('cancelled', $transfer->fresh()->status);
    }

    /** A "later" bill was never paid although the student was told it was scheduled. */
    public function test_bills_are_paid_now_or_on_their_date(): void
    {
        [$a] = $this->student(500);
        $bill = ['beneficiary_name' => 'Electricity Co', 'account_number' => '555', 'amount' => 25];

        $this->actingAs($a)->post('/bank-account/store-pay-bill', $bill + ['type' => 'now'])->assertSessionHas('success', e(__('flash.bill_paid')));
        $this->assertEquals(475, $this->balance($a));

        $this->actingAs($a)->post('/bank-account/store-pay-bill', $bill + ['type' => 'later', 'paydate' => now()->addDays(5)->toDateString()]);
        $this->assertEquals(475, $this->balance($a));
        $this->travel(6)->days();
        Artisan::call('transfers:run-due');
        $this->assertEquals(450, $this->balance($a));
        $this->assertSame(2, Transaction1::where('user_id', $a->id)->where('category', 'Bill Payment')->count());
    }

    /** It looked for a field the form doesn't send and always said "not in database". */
    public function test_a_friend_can_be_saved_with_their_number_and_name(): void
    {
        [$a, $aAccount] = $this->student(0);
        [$b, $bAccount] = $this->student(0);

        $this->actingAs($a)->post('/bank-account/beneficiary', ['name' => $b->name, 'account_number' => $bAccount->primary_savings_account_number])
            ->assertSessionHas('success');
        $this->actingAs($a)->post('/bank-account/beneficiary', ['name' => 'Wrong Name', 'account_number' => $bAccount->primary_savings_account_number])
            ->assertSessionHas('error', __('flash.no_beneficiary'));
        $this->actingAs($a)->post('/bank-account/beneficiary', ['name' => $a->name, 'account_number' => $aAccount->primary_savings_account_number])
            ->assertSessionHas('error', __('flash.beneficiary_is_you'));

        $this->assertSame([$b->name], Beneficiary::where('user_id', $a->id)->pluck('name')->all());
    }

    /** Sending the direct-deposit form again paid a second salary and restarted the balance from 0. */
    public function test_the_direct_deposit_form_starts_the_bank_once(): void
    {
        [$a] = $this->student(50);

        $this->actingAs($a)->post('/bank-account/authorize-direct-deposit')->assertRedirect(route('bank.index'));

        $this->assertEquals(50, $this->balance($a));
        $this->assertSame(1, Transaction1::where('user_id', $a->id)->count());
    }

    /** Moving money to yourself counted as spending and avoided the month-end penalty. */
    public function test_moving_money_is_not_spending(): void
    {
        [$a, $account] = $this->student(4000, 1, ['emergency' => 0]);
        $this->send($a, ['to' => 'own', 'own_to' => 'emergency', 'amount' => 3800]);

        $this->assertEquals(0.0, \App\Services\BankTransferService::spentThisMonth($a->id));
    }

    public function test_the_penalty_is_charged_once_and_never_on_autopilot(): void
    {
        $this->travelTo(now()->endOfMonth()->setTime(12, 0));
        [$a] = $this->student(4000);
        [$b] = $this->student(4000);
        AutopilotPeriod::create(['student_id' => $b->id, 'start_date' => now()->startOfMonth(), 'end_date' => now()->endOfMonth(), 'reason' => 'Ill', 'created_by' => $b->id]);
        $bank = app(\App\Http\Controllers\BankController::class);
        $statements = app(\App\Services\StatementGenerator::class);

        $bank->banks_penalty($statements, $a);
        $bank->banks_penalty($statements, $a);
        $bank->banks_penalty($statements, $b);

        $this->assertSame(1, Transaction1::where('user_id', $a->id)->where('category', 'Fine')->count());
        $this->assertSame(0, Transaction1::where('user_id', $b->id)->where('category', 'Fine')->count());
    }

    /** "Credited on the 1st of each month" - nothing paid it. Now once per account and month. */
    public function test_interest_is_paid_once_a_month(): void
    {
        [$a, $account] = $this->student(0, 1, ['emergency' => 1200, 'money_market' => 600]);

        Artisan::call('savings:credit-interest', ['--write' => true]);
        Artisan::call('savings:credit-interest', ['--write' => true]);

        $account->refresh();
        $this->assertEquals(1202.0, (float) $account->emergency_fund_account_amount, '2% a year: 1200 x 0.02 / 12 = 2.00');
        $this->assertEquals(602.0, (float) $account->money_market_account_amount, '4% a year: 600 x 0.04 / 12 = 2.00');
        $this->assertSame(2, SavingsTransaction::where('user_id', $a->id)->where('category', 'Interest')->count());
    }

    public function test_the_salary_is_paid_once_a_month_and_adds_to_the_latest_balance(): void
    {
        $this->travelTo(now()->startOfMonth()->addDays(9)->setTime(12, 0));
        [$a] = $this->student(70);
        Transaction1::where('user_id', $a->id)->update(['transaction_date' => now()->startOfMonth()->subMonth()]); // last month's money
        // a purchase dated after the salary's date that is already on the account
        Transaction1::create(['user_id' => $a->id, 'sid' => 1, 'transaction_date' => now(), 'description' => 'Groceries', 'type' => 'debit',
            'category' => 'Needs', 'amount' => 20, 'balance' => 50, 'is_penalty' => false]);
        $bank = app(\App\Http\Controllers\BankController::class);

        $bank->creditMonthlySalary($a->id);
        $bank->creditMonthlySalary($a->id);

        $this->assertSame(1, Transaction1::where('user_id', $a->id)->where('category', 'Income')->where('description', 'like', 'Monthly Salary%')->count());
        $this->assertEquals(round(50 + config('zedville.monthly_salary'), 2), $this->balance($a), 'The purchase stays counted.');
    }
}
