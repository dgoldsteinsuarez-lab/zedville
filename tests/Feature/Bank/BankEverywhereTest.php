<?php

namespace Tests\Feature\Bank;

use App\Models\AppNotification;
use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\SavingsLedger;
use App\Support\NotificationText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-29: "check if all the other bank information is actualized
 * in the whole web, the architecture" - the bank's numbers, names and
 * notices outside the bank pages, and every other way money moves.
 */
class BankEverywhereTest extends TestCase
{
    use RefreshDatabase;

    private function student(float $balance = 1000, ?SchoolClass $class = null, ?float $emergency = null): array
    {
        $class ??= SchoolClass::create(['sid' => 1, 'name' => 'Class ' . uniqid()]);
        $user = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => 1, 'student_name' => $user->name, 'student_email' => $user->email,
            'primary_savings_account_number' => '123000-' . random_int(100000, 999999), 'primary_savings_account_amount' => 3952.40,
            'emergency_fund_account_number' => (string) random_int(1000000000, 9999999999), 'card_pin' => '1234',
        ]);
        if ($emergency !== null) {
            $account->is_open_emergency_account = 1;
            $account->save();
            SavingsLedger::post($account, 'emergency', 'credit', 'Savings', $emergency, 'Opening deposit');
        }
        if ($balance > 0) {
            Transaction1::create(['user_id' => $user->id, 'sid' => 1, 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth(),
                'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => $balance, 'balance' => $balance, 'is_penalty' => false]);
        }

        return [$user, $account->fresh()];
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    public function test_a_transfer_notice_comes_only_when_the_money_moved_and_reads_in_the_students_language(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        [$ana] = $this->student(100, $class);
        [$ben, $benAccount] = $this->student(0, $class);
        $send = fn (float $amount) => $this->actingAs($ana)->post(route('bank.transfer_store'), [
            'from_account' => 'Primary Savings Account', 'to' => 'friend', 'type' => 'now',
            'account_number' => $benAccount->primary_savings_account_number, 'beneficiary_name' => $ben->name, 'amount' => $amount,
        ]);

        $before = (int) AppNotification::max('id');
        $notices = fn (User $user) => AppNotification::where('user_id', $user->id)->where('id', '>', $before)->pluck('type')->all();

        $send(500)->assertSessionHas('error'); // not enough money
        $this->assertSame([], $notices($ana));

        $send(40)->assertSessionHas('success');
        $this->assertSame(['transfer_completed'], $notices($ana)); // not also a "payment" alert

        $received = AppNotification::where('user_id', $ben->id)->where('id', '>', $before)->sole();
        app()->setLocale('es');
        [$title, $body] = NotificationText::for($received);
        $this->assertSame('Dinero recibido', $title);
        $this->assertStringContainsString("40,00\u{00A0}Ƶ", $body);
        app()->setLocale('en');
    }

    public function test_moving_money_between_own_accounts_gives_one_notice_not_a_deposit(): void
    {
        [$ana] = $this->student(500, null, 100);
        $before = (int) AppNotification::max('id');

        $this->actingAs($ana)->post(route('bank.transfer_store'), ['from_account' => 'Primary Savings Account', 'to' => 'own', 'own_to' => 'emergency', 'type' => 'now', 'amount' => 50])
            ->assertSessionHas('success');

        $this->assertSame(['transfer_completed'], AppNotification::where('user_id', $ana->id)->where('id', '>', $before)->pluck('type')->all());
    }

    public function test_the_home_page_shows_the_banks_numbers(): void
    {
        [$ana] = $this->student(0); // no line yet: 0, not the salary column
        $bank = $this->actingAs($ana)->get(route('dashboard'))->viewData('bank');
        $this->assertSame(0.0, $bank['balance']);
        $this->assertFalse($bank['ef_open']);

        [$ben] = $this->student(800, null, 400);
        $bank = $this->actingAs($ben)->get(route('dashboard'))->assertOk()->viewData('bank');
        $this->assertSame(800.0, $bank['balance']);
        $this->assertSame(400.0, $bank['savings']);
        // the 3-month reserve, not one month's 20% (that read 100% after a month)
        $this->assertLessThan(100, $bank['reserve_pct']);
    }

    public function test_the_emergency_fund_page_lists_the_funds_own_movements_and_interest(): void
    {
        [$ana, $account] = $this->student(1000, null, 1200);
        SavingsLedger::post($account, 'emergency', 'credit', 'Savings', 790.48, 'Monthly transfer from your current account');

        $response = $this->actingAs($ana)->get(route('education.educational_finance_department'))->assertOk();
        $this->assertCount(2, $response->viewData('emmengercyfundtransactions'));
        // 2% a year of 1,990.48 for a month
        $this->assertEquals(round(1990.48 * 0.02 / 12, 2), $response->viewData('emergencyFundInterest')['estimated_interest']);
        $response->assertDontSee('500 / 3,000')->assertSee('+Ƶ 790.48');
    }

    public function test_the_budget_match_sorts_moves_to_savings_as_savings_and_leaves_out_transfers(): void
    {
        [$ana] = $this->student(1000);
        $lastMonth = now()->startOfMonth()->subMonth()->addDays(3);
        foreach ([['debit', 'Own Transfer', 'Transfer to your Emergency Fund'], ['credit', 'Own Transfer', 'Transfer from your Emergency Fund'],
                     ['debit', 'Fund Transfer', 'Transfer to Ben (123000-1)'], ['debit', 'Bill Payment', 'Bill payment to City School (SCHOOL-AUTO)']] as [$type, $category, $text]) {
            Transaction1::create(['user_id' => $ana->id, 'sid' => 1, 'transaction_date' => $lastMonth,
                'description' => $text, 'type' => $type, 'category' => $category, 'amount' => 50, 'balance' => 900, 'is_penalty' => false])
                ->forceFill(['created_at' => $lastMonth])->save();
        }

        $template = collect($this->actingAs($ana)->get(route('education.index'))->viewData('template'))->keyBy('name');

        $this->assertSame('savings', $template['Transfer to your Emergency Fund']['category']);
        $this->assertSame('needs', $template['Bill payment to City School (SCHOOL-AUTO)']['category']);
        $this->assertFalse($template->has('Transfer from your Emergency Fund'));
        $this->assertFalse($template->has('Transfer to Ben (123000-1)'));
    }

    public function test_an_admin_cant_reverse_one_side_of_a_move(): void
    {
        [$ana] = $this->student(1000);
        $line = Transaction1::create(['user_id' => $ana->id, 'sid' => 1, 'transaction_date' => now(), 'description' => 'Auto Transfer to Emergency Fund',
            'type' => 'debit', 'category' => 'Savings', 'amount' => 790.48, 'balance' => 209.52, 'is_penalty' => false]);

        $this->actingAs(User::factory()->create(['role' => 1]))->post(route('admin.ledger.reverse', $line->id), ['reason' => 'Testing the rule'])
            ->assertSessionHas('error');
        $this->assertEquals(209.52, $this->balance($ana));
    }

    public function test_the_survey_refuses_negative_amounts(): void
    {
        [$ana] = $this->student(100);

        $this->actingAs($ana)->postJson(route('storesurvey'), [
            'diet' => array_key_first(trans('surveys.diets', [], 'en')),
            'groceries' => [['id' => 1, 'name' => 'Rice', 'total_price' => 500]],
            'lunch' => -450, 'transport' => 0, 'activities' => 0, 'restaurants' => 0,
        ])->assertStatus(422)->assertJsonValidationErrors('lunch');
        $this->assertEquals(100, $this->balance($ana));
    }

    public function test_onboarding_doesnt_charge_the_months_bills_twice(): void
    {
        [$ana, $account] = $this->student(3000);
        foreach (['Rent' => 300, 'School Fees' => 1000] as $bill => $amount) { // charged by the nightly run already
            Transaction1::create(['user_id' => $ana->id, 'sid' => 1, 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth()->addDays(6),
                'description' => $bill, 'type' => 'debit', 'category' => 'Needs', 'amount' => $amount, 'balance' => $this->balance($ana) - $amount, 'is_penalty' => false]);
        }

        $this->actingAs($ana)->post('/bank-account/store-auto-debit-authorization-form', [
            'services' => ['rent' => 'R-1'], 'services_meta' => ['rent' => ['name' => 'Rent', 'account' => 'R-1', 'amount' => 300]],
            'bankname' => 'Universal Bank', 'bankaccountnumber' => $account->primary_savings_account_number,
        ]);

        $this->assertSame(1, Transaction1::where('user_id', $ana->id)->where('description', 'Rent')->count());
        $this->assertSame(1, Transaction1::where('user_id', $ana->id)->where('description', 'School Fees')->count());
        // the running balance adds up (the month's other bills may be charged too, from the 7th)
        $out = (float) Transaction1::where('user_id', $ana->id)->where('type', 'debit')->sum('amount');
        $this->assertEquals(round(3000 - $out, 2), $this->balance($ana));
    }

    /** Daniel, 2026-10-30: "we don't allow minus balance" - a bill waits for the money. */
    public function test_a_bill_waits_until_the_money_is_there_and_the_balance_never_goes_below_zero(): void
    {
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::create(2026, 10, 12, 12, 0, 0, 'Europe/Berlin'));
        [$ana, $account] = $this->student(150);
        $bank = app(\App\Http\Controllers\BankController::class);

        \App\Models\AutoDebitRequest::create(['user_id' => $ana->id, 'sid' => 1, 'type' => 'Rent']); // the Overview, not the sign-up steps

        $bank->ensureMonthlyBills($ana);
        $this->assertGreaterThanOrEqual(0, $this->balance($ana));
        $this->assertSame(0, Transaction1::where('user_id', $ana->id)->whereIn('description', ['Rent', 'School Fees'])->count());
        $this->actingAs($ana)->get(route('bank.index'))->assertSee('Not paid yet: there isn’t enough money in your account.');

        // money comes in: the next run pays them, dated the day they are paid
        \App\Services\LedgerService::post($ana->id, 'credit', 'Income', 2000, 'Bonus', ['bank_account_id' => $account->id]);
        $bank->ensureMonthlyBills($ana);
        $this->assertSame(1, Transaction1::where('user_id', $ana->id)->where('description', 'Rent')->count());
        $this->assertSame('2026-10-12', Transaction1::where('user_id', $ana->id)->where('description', 'Rent')->value('transaction_date')->toDateString());

        // every line adds up: start + in - out = balance
        $in = (float) Transaction1::where('user_id', $ana->id)->where('type', 'credit')->sum('amount');
        $out = (float) Transaction1::where('user_id', $ana->id)->where('type', 'debit')->sum('amount');
        $this->assertEquals(round($in - $out, 2), $this->balance($ana));
        \Carbon\Carbon::setTestNow();
    }

    public function test_the_penalty_takes_at_most_what_is_there_and_says_so(): void
    {
        [$ana] = $this->student(30);

        $line = \App\Services\LedgerService::post($ana->id, 'debit', 'Penalty', 500, 'Entertainment', ['floor_at_zero' => true, 'is_penalty' => true]);
        $this->assertEquals(30, $line->amount); // the line says what was taken
        $this->assertEquals(0, $this->balance($ana));

        $this->assertNull(\App\Services\LedgerService::post($ana->id, 'debit', 'Penalty', 10, 'Entertainment', ['floor_at_zero' => true]));
        $this->assertNull(\App\Services\LedgerService::post($ana->id, 'debit', 'Needs', 10, 'Rent', ['only_if_enough' => true]));
        $this->assertSame(2, Transaction1::where('user_id', $ana->id)->count());
    }

    public function test_the_tutor_sees_this_months_budget_and_the_savings_accounts(): void
    {
        $class = SchoolClass::create(['sid' => 1, 'name' => '9A']);
        [$ana] = $this->student(3952.40, $class, 790.48);
        Transaction1::create(['user_id' => $ana->id, 'sid' => 1, 'transaction_date' => now(), 'description' => 'Rent',
            'type' => 'debit', 'category' => 'Needs', 'amount' => 300, 'balance' => 3652.40, 'is_penalty' => false]);
        $tutor = User::factory()->create(['role' => 3, 'sid' => 1, 'grade' => $class->id]);
        $class->tutors()->attach($tutor->id);

        $this->actingAs($tutor)->get(route('admin.tutor.my-students.show', $ana->id))->assertOk()
            ->assertSee('Emergency Fund: Ƶ 790.48')->assertSee('300.00');
    }
}
