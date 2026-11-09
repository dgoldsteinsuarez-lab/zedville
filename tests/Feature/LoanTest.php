<?php

namespace Tests\Feature;

use App\Http\Controllers\BankController;
use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\Loan;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\LoanService;
use App\Services\PriceRise;
use App\Services\ZedDirecto;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The personal loan, from the Intermediate (proposal «El crédito», step 11):
 * 500 to 4,000 Ƶ over 6, 12 or 24 months at 8 % TAE, equal instalments on the
 * 7th, all of them at most 20 % of the salary, 14 days to change one's mind,
 * paying off with no more interest, and late instalments in the credit history.
 */
class LoanTest extends TestCase
{
    use RefreshDatabase;

    private function setDay(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00', 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
        ZedDirecto::flush();
    }

    private function student(string $level = 'Intermediate', float $money = 3952.40): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        LedgerService::post($user->id, 'credit', 'Income', $money, 'Monthly Salary Credit', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->subDays(4)]);

        return $user;
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    public function test_the_table_has_equal_instalments_and_the_proposals_cost(): void
    {
        // 1,000 Ƶ over 12 months: about 87 a month and 42 of interest (the proposal's example)
        $this->assertSame(['instalment' => 86.86, 'total' => 1042.32, 'interest' => 42.32], LoanService::quote(1000, 12));
        $table = LoanService::table(1000, 12);
        $this->assertSame(0.0, end($table)['remaining']);
        $this->assertSame(1000.0, round(array_sum(array_column($table, 'capital')), 2));
        $this->assertGreaterThan($table[11]['interest'], $table[0]['interest']);   // at first more interest
    }

    public function test_a_loan_is_granted_within_20_percent_and_paid_on_the_7th(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student();
        $this->assertSame('loan_not_offered', LoanService::refusal($this->student('Rookie'), 1000, 12));
        $this->assertSame('loan_amount', LoanService::refusal($student, 300, 12));
        $this->assertSame('loan_term', LoanService::refusal($student, 1000, 9));

        $loan = LoanService::grant($student, 4000, 6);   // 681.76 a month
        $this->assertInstanceOf(Loan::class, $loan);
        $this->assertSame(3952.40 + 4000, $this->balance($student));
        $this->assertSame('2026-07-07', $loan->instalments()->first()->due_on->toDateString());
        // all instalments at most 20 % of 3,952.40, 790.48 a month (agreed 2026-11-08): another 4,000 over
        // 6 months is too much, 500 over 24 (22.55) still fits, and then 1,000 over 12 (86.86) doesn't
        $this->assertSame(790.48, LoanService::maxLoad($student->id));
        $this->assertSame('loan_too_much', LoanService::refusal($student, 4000, 6));
        $this->assertNull(LoanService::refusal($student, 500, 24));
        LoanService::grant($student, 500, 24);
        $this->assertSame('loan_too_much', LoanService::refusal($student, 1000, 12));

        $this->setDay('2026-07-07');
        LoanService::chargeDue($student);
        $this->assertSame(2, Transaction1::where('user_id', $student->id)->where('description', LoanService::LINE_INSTALMENT)->count());
        $this->assertSame(['paid', 'paid'], Loan::where('user_id', $student->id)->get()->map(fn ($l) => $l->instalments()->first()->status)->all());
    }

    public function test_fourteen_days_to_change_ones_mind_and_paying_it_off(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student();
        $loan = LoanService::grant($student, 1000, 12);
        $this->assertNull(LoanService::cancel($student, $loan));
        $this->assertSame('cancelled', $loan->fresh()->status);
        $this->assertSame(3952.40, $this->balance($student));   // the money back, no interest

        $loan = LoanService::grant($student, 1000, 12);
        $this->setDay('2026-06-25');
        $this->assertSame('loan_cancel_late', LoanService::cancel($student, $loan));
        $this->setDay('2026-07-07');
        LoanService::chargeDue($student);   // the first instalment
        $owed = LoanService::owed($loan);
        $this->assertSame(round(1000 - (float) $loan->instalments()->first()->capital, 2), $owed);
        $this->assertNull(LoanService::payOff($student, $loan));
        $this->assertSame('paid', $loan->fresh()->status);
        $this->assertSame(0, $loan->instalments()->whereIn('status', ['pending', 'late'])->count());
    }

    public function test_late_instalments_stop_new_loans_for_six_months(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student('Intermediate', 3000);
        // a salary of 3,000: at most 600 a month in instalments (20 %), so 3,000 over 6 months (511.32)
        $loan = LoanService::grant($student, 3000, 6);
        // all the money goes: nothing for the instalments of July and August
        LedgerService::post($student->id, 'debit', 'Wants', $this->balance($student), 'A big shopping trip', ['sid' => '1']);

        // not paid in July: on 1 August the bank takes it anyway, into overdraft (step 14)
        $instalment = (float) $loan->instalment;   // 511.32
        $this->setDay('2026-08-01');
        LoanService::chargeDue($student);
        $this->assertSame(-$instalment, $this->balance($student));
        $this->assertSame(1, \App\Models\Mailbox::where('student_id', $student->id)->where('template', 'overdraft')->count());
        // July ended at zero: nothing to pay for it
        $this->assertNull(\App\Services\Overdraft::chargeMonthEnd($student));

        $this->setDay('2026-09-01');
        LoanService::chargeDue($student);
        $this->assertSame(['paid_late', 'paid_late'], $loan->instalments()->whereIn('number', [1, 2])->pluck('status')->all());
        $this->assertTrue(LoanService::blocked($student->id));
        $this->assertSame('loan_blocked', LoanService::refusal($student, 500, 6));
        // August ended at -511.32: 15 % a year on it and 8 Ƶ, once
        $cost = round($instalment * 0.15 / 12 + 8, 2);   // 14.39
        $this->assertSame($cost, \App\Services\Overdraft::chargeMonthEnd($student));
        $this->assertNull(\App\Services\Overdraft::chargeMonthEnd($student));
        $this->assertSame(round(-2 * $instalment - $cost, 2), $this->balance($student));
        $this->assertSame(2, \App\Models\Mailbox::where('student_id', $student->id)->where('template', 'overdraft')->count());   // one a month: August's and September's

        // the salary covers the overdraft, and the third instalment is due on 7 September
        LedgerService::post($student->id, 'credit', 'Income', 3952.40, 'Monthly Salary Credit', ['sid' => '1']);
        LoanService::chargeDue($student);
        $this->assertSame('pending', $loan->instalments()->where('number', 3)->value('status'));
        $this->assertSame(round(3952.40 - 2 * $instalment - $cost, 2), $this->balance($student));
    }

    public function test_the_overdraft_is_only_from_the_intermediate(): void
    {
        $this->setDay('2026-07-01');
        $rookie = $this->student('Rookie', 100);
        LedgerService::post($rookie->id, 'debit', 'Wants', 150, 'Something', ['sid' => '1', 'transaction_date' => Carbon::parse('2026-06-20')]);
        $this->assertNull(\App\Services\Overdraft::chargeMonthEnd($rookie));
    }

    public function test_the_page(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student();
        $this->actingAs($student)->get(route('bank.loans'))->assertOk()->assertSee(__('loans.sim_title'))->assertSee(__('loans.none'));
        $this->actingAs($student)->post(route('bank.loans.store'), ['amount' => 1000, 'months' => 12, 'accept' => 1])->assertSessionHas('success');
        $this->actingAs($student)->get(route('bank.loans'))->assertOk()->assertSee(__('loans.table_hint'))->assertSee(zeds(86.86))
            ->assertSee(__('bank_ui.tab_loans'));   // the bank's tab
        $this->actingAs($this->student('Rookie'))->get(route('bank.loans'))->assertNotFound();

        // Admin > Bank > Loans
        $this->actingAs(User::factory()->create(['role' => 1]))->get(route('admin.loans.index'))->assertOk()
            ->assertSee('Loans')->assertSee('1,000.00 Ƶ')->assertSee('0 / 12');
    }
}
