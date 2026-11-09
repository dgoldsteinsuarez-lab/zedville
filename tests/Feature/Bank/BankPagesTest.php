<?php

namespace Tests\Feature\Bank;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\Beneficiary;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The redesigned bank (Daniel, 2026-10-29: "the userfriendlyness is more
 * important than the design ... for kids but also for grade 11 and 12"):
 * Overview, Pay & transfer, Statements, Card & details.
 */
class BankPagesTest extends TestCase
{
    use RefreshDatabase;

    private function student(int $level = 2, array $lines = [[100, 'credit', 'Income', 'Opening balance']]): array
    {
        $gradeLevel = GradeLevel::firstOrCreate(['name' => 'Level ' . $level], ['sort_order' => $level]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $gradeLevel->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $user = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
        $account = BankAccount::create([
            'student_id' => $user->id, 'sid' => 1, 'student_name' => 'Ana López', 'student_email' => $user->email,
            'primary_savings_account_number' => '123000-555111', 'emergency_fund_account_number' => '7000000001',
            'money_market_account_number' => '8000000001', 'primary_savings_account_amount' => 3952.40,
            'card_iban' => 'ZV9288864684', 'card_swift' => 'UNIBYTHU', 'card_number' => '2000833288170212',
            'card_valid' => now()->addYear()->toDateString(), 'card_cvv' => '795', 'card_pin' => '1234', 'bank_name' => 'Universal Bank',
        ]);
        AutoDebitRequest::create(['user_id' => $user->id, 'sid' => 1, 'type' => 'Rent']);
        $balance = 0;
        foreach ($lines as [$amount, $type, $category, $text]) {
            $balance += $type === 'credit' ? $amount : -$amount;
            Transaction1::create(['user_id' => $user->id, 'sid' => 1, 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth()->addDay(),
                'description' => $text, 'type' => $type, 'category' => $category, 'amount' => $amount, 'balance' => $balance, 'is_penalty' => false]);
        }

        return [$user, $account];
    }

    public function test_the_overview_names_each_account_once_and_says_what_it_is_for(): void
    {
        [$user] = $this->student();

        $this->actingAs($user)->get(route('bank.index'))->assertOk()
            ->assertSeeInOrder(['Overview', 'Pay &amp; transfer', 'Statements', 'Card &amp; details'], false)
            ->assertSeeInOrder(['Current account', 'For everyday spending', 'Emergency Fund', 'Money put aside for surprises', 'Money Market Account', 'Long-term savings'])
            ->assertSee('Bills this month')->assertSee('Left in your current account')
            // gone: the savings card that showed the current account, the fake features, the duplicate buttons
            ->assertDontSee('Total Savings')->assertDontSee('Primary Savings Account')->assertDontSee('Freeze Card')
            ->assertDontSee('Add to Wallet')->assertDontSee('Quick Actions');
    }

    public function test_money_is_written_one_way_per_language(): void
    {
        app()->setLocale('en');
        $this->assertSame('Ƶ 1,234.56', Money::format(1234.56));
        $this->assertSame('−Ƶ 20.00', Money::format(-20));
        $this->assertSame('+Ƶ 5.00', Money::format(5, true));
        app()->setLocale('es');
        $this->assertSame("1.234,56\u{00A0}Ƶ", Money::format(1234.56));
        app()->setLocale('fr');
        $this->assertSame("1\u{202F}234,56\u{00A0}Ƶ", Money::format(1234.56));
    }

    public function test_pay_and_transfer_offers_three_choices_and_the_friend_form_is_like_a_real_bank(): void
    {
        [$user] = $this->student();
        Beneficiary::create(['user_id' => $user->id, 'sid' => 1, 'name' => 'Maxi', 'account_number' => '123000-999999', 'bank' => 'Universal Bank']);

        $this->actingAs($user)->get(route('bank.transfer'))->assertOk()
            ->assertSeeInOrder(['Send to a friend', 'Between my accounts', 'Pay a bill'])
            ->assertSee('name="beneficiary_name"', false)->assertSee('name="account_number"', false)
            ->assertSee('Their account number or IBAN')->assertSee('Ask your friend: they find it under Card &amp; details.', false)
            ->assertDontSee('name="pin"', false) // online banking: the check step confirms, no card PIN
            ->assertSee('Saved beneficiaries')->assertSee('123000-999999')
            // a younger student sends now; dates and standing orders are for the older ones
            ->assertDontSee('value="recurring"', false);
    }

    public function test_older_students_can_schedule_and_set_up_standing_orders(): void
    {
        [$user, $account] = $this->student(4);
        Transfer::create(['user_id' => $user->id, 'sid' => 1, 'from_account' => 'Primary Savings Account', 'account_number' => $account->emergency_fund_account_number,
            'beneficiary_name' => 'Ana López', 'amount' => 25, 'type' => 'recurring', 'frequency' => 'monthly', 'kind' => 'transfer',
            'status' => 'scheduled', 'next_run_at' => now()->addDays(3), 'start_date' => now()->addDays(3), 'end_date' => now()->addYear()]);

        $this->actingAs($user)->get(route('bank.transfer'))->assertOk()
            ->assertSee('value="later"', false)->assertSee('value="recurring"', false)->assertSee('value="yearly"', false)
            ->assertSee('Scheduled transfers and standing orders')
            ->assertSeeInOrder(['Emergency Fund', 'Every month'])
            ->assertSee(route('bank.transfer_cancel', Transfer::first()), false);
    }

    public function test_the_statement_adds_up(): void
    {
        [$user] = $this->student(2, [[3952.40, 'credit', 'Income', 'Monthly Salary Credit - ' . now()->format('F Y')], [300, 'debit', 'Needs', 'Rent'], [75, 'debit', 'Wants', 'Activities & Hobbies'], [40, 'debit', 'Fund Transfer', 'Transfer to Maxi (123000-999999)']]);

        $this->actingAs($user)->get(route('bank.bank_statement_show'))->assertOk()
            ->assertSeeInOrder(['Opening balance', 'Ƶ 0.00', 'Money in', '+Ƶ 3,952.40', 'Money out', '−Ƶ 415.00', 'Closing balance', 'Ƶ 3,537.40'])
            ->assertSee('Opening balance + money in − money out = closing balance')
            ->assertSeeInOrder(['Rent', 'Activities &amp; Hobbies', 'Transfer to Maxi'], false)
            ->assertSeeInOrder(['Where the money went', 'Needs', 'Ƶ 300.00', 'Wants', 'Ƶ 75.00', 'Transfers to others', 'Ƶ 40.00']);
    }

    public function test_the_statement_picks_a_month_from_the_old_links_and_never_shows_another_students(): void
    {
        [$user] = $this->student();
        [$other] = $this->student(2, [[999, 'credit', 'Income', 'Secret Salary']]);
        $last = now()->startOfMonth()->subMonth();

        $this->actingAs($user)->get(route('bank.bank_statement_show', ['from_date' => $last->format('Y-m-d'), 'to_date' => $last->copy()->endOfMonth()->format('Y-m-d')]))
            ->assertOk()->assertSee('No movements this month.')->assertDontSee('Secret Salary');
        $this->actingAs($user)->get('/bank-account/view-statements/' . $last->format('Y/n'))
            ->assertRedirect(route('bank.bank_statement_show', ['month' => $last->format('Y-m')]));
        $this->actingAs($user)->get('/bank-account/view-statements/abc/1')->assertRedirect(route('bank.bank_statement_show'));
        $this->actingAs($user)->get(route('bank.bank_statement_show', ['month' => 'nonsense']))->assertOk();
    }

    public function test_card_and_details_give_a_friend_what_they_need_to_send_money(): void
    {
        [$user] = $this->student();

        $this->actingAs($user)->get(route('bank.my_account'))->assertOk()
            ->assertSee('Your details to receive money')
            ->assertSeeInOrder(['Account holder', 'Ana López', 'Account number', '123000-555111', 'IBAN', 'ZV92 8886 4684', 'BIC', 'UNIBYTHU'])
            ->assertSee('Copy all my details')
            ->assertSee('Keep your PIN secret')
            ->assertDontSee('2000833288170212') // the card number only when asked
            ->assertDontSee('Freeze Card')->assertDontSee('Add to Wallet');
    }

    public function test_a_form_the_bank_can_t_accept_says_why(): void
    {
        [$user] = $this->student();

        $this->actingAs($user)->from(route('bank.transfer'))->post(route('bank.transfer_store'), ['to' => 'friend', 'amount' => 0])
            ->assertRedirect(route('bank.transfer'));
        $this->actingAs($user)->get(route('bank.transfer'))->assertSee('Please check this:')->assertSee('The amount must be at least 0.01.');
    }

    public function test_the_old_pages_lead_to_the_new_ones(): void
    {
        [$user] = $this->student();

        foreach ([
            '/bank-account/pay-bills' => route('bank.transfer', ['to' => 'bill']),
            '/bank-account/manage-payee' => route('bank.transfer', ['to' => 'bill']),
            '/bank-account/schedule-transfers' => route('bank.transfer'),
            '/bank-account/recurring-payment' => route('bank.transfer'),
            '/bank-account/payment-history' => route('bank.bank_statement_show'),
            '/bank-account/statements' => route('bank.bank_statement_show'),
        ] as $old => $new) {
            $this->actingAs($user)->get($old)->assertRedirect($new);
        }
        $this->actingAs($user)->get('/bank-account/banks-penalty')->assertNotFound();
    }

    public function test_the_bank_speaks_the_students_language(): void
    {
        [$user] = $this->student();
        $user->update(['preferred_language' => 'de']);

        $this->actingAs($user)->get(route('bank.index'))->assertOk()
            ->assertSeeInOrder(['Übersicht', 'Überweisungen &amp; Zahlungen', 'Kontoauszüge', 'Karte &amp; Kontodaten'], false)
            ->assertSeeInOrder(['Girokonto', 'Notgroschen-Konto', 'Geldmarktkonto']);
        $this->actingAs($user)->get(route('bank.transfer'))->assertOk()
            ->assertSee('An einen Freund oder eine Freundin')->assertSee('Kontonummer oder IBAN');
    }
}
