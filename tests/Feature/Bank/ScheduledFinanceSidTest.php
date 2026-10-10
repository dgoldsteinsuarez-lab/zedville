<?php

namespace Tests\Feature\Bank;

use App\Http\Controllers\BankController;
use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Console\Kernel's scheduled finance job (students:process-monthly-finances
 * equivalent) calls BankController::creditMonthlySalary/ensureMonthlyBills/
 * banks_penalty directly for every student, with no HTTP session in play.
 * These methods write 'sid' from session('sid') instead of the student's
 * own sid, so every scheduled run silently stores NULL sid on every
 * student's ledger rows (bugs.md #14). These tests reproduce that without
 * a session, matching the real scheduled execution path.
 */
class ScheduledFinanceSidTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(int $sid): User
    {
        $user = User::factory()->create([
            'role' => 4,
            'sid' => $sid,
        ]);

        BankAccount::create([
            'student_id' => $user->id,
            'sid' => $sid,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'is_open_emergency_account' => 0,
            'is_open_money_market_account' => 0,
        ]);

        return $user;
    }

    public function test_credit_monthly_salary_stamps_the_students_own_sid_with_no_session(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 10, 12, 0, 0, 'Europe/Berlin'));

        $student = $this->makeStudent(1001);

        // No session('sid') set - matches a console/scheduled invocation.
        (new BankController())->creditMonthlySalary($student->id);

        $txn = Transaction1::where('user_id', $student->id)->where('category', 'Income')->first();

        $this->assertNotNull($txn, 'Expected a salary transaction to be created.');
        $this->assertEquals(1001, $txn->sid);

        Carbon::setTestNow();
    }

    public function test_ensure_monthly_bills_stamps_the_students_own_sid_with_no_session(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 10, 12, 0, 0, 'Europe/Berlin'));

        $student = $this->makeStudent(1002);

        (new BankController())->ensureMonthlyBills($student);

        $bills = Transaction1::where('user_id', $student->id)->where('type', 'debit')->get();

        $this->assertNotEmpty($bills, 'Expected fixed bills to be created.');
        foreach ($bills as $bill) {
            $this->assertEquals(1002, $bill->sid, "Bill '{$bill->description}' has the wrong sid.");
            $this->assertGreaterThanOrEqual(0, (float) $bill->balance, "Bill '{$bill->description}' let the balance go negative.");
        }

        Carbon::setTestNow();
    }

    public function test_ensure_monthly_bills_floors_balance_at_zero_instead_of_going_negative(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 10, 12, 0, 0, 'Europe/Berlin'));

        $student = $this->makeStudent(1004);

        // Starting balance (5) is far less than the combined fixed bills
        // (Electricity + Water + Internet 20 + Rent 300 + School Fees 1000),
        // so every one of them must clamp to 0 rather than go negative.
        Transaction1::create([
            'user_id' => $student->id,
            'sid' => 1004,
            'bank_account_id' => BankAccount::where('student_id', $student->id)->value('id'),
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 5,
            'balance' => 5,
            'is_penalty' => false,
        ]);

        (new BankController())->ensureMonthlyBills($student);

        $bills = Transaction1::where('user_id', $student->id)->where('type', 'debit')->get();
        $this->assertNotEmpty($bills);
        foreach ($bills as $bill) {
            $this->assertEquals(0.0, (float) $bill->balance, "Bill '{$bill->description}' should have floored at 0.");
        }

        Carbon::setTestNow();
    }

    public function test_banks_penalty_stamps_the_students_own_sid_with_no_session(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 31, 12, 0, 0, 'Europe/Berlin'));

        $student = $this->makeStudent(1003);

        // Give the student a balance so there is something to apply a penalty against.
        Transaction1::create([
            'user_id' => $student->id,
            'sid' => 1003,
            'bank_account_id' => BankAccount::where('student_id', $student->id)->value('id'),
            'transaction_date' => now(),
            'description' => 'Monthly Salary Credit - test setup',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 3952.40,
            'balance' => 3952.40,
            'is_penalty' => false,
        ]);

        (new BankController())->banks_penalty(app(\App\Services\StatementGenerator::class), $student);

        $penalties = Transaction1::where('user_id', $student->id)->where('category', 'Penalty')->get();

        $this->assertNotEmpty($penalties, 'Expected at least one penalty transaction (expenses are 0%, well under the 91% threshold).');
        foreach ($penalties as $penalty) {
            $this->assertEquals(1003, $penalty->sid);
        }

        Carbon::setTestNow();
    }

    public function test_banks_penalty_floors_balance_at_zero_instead_of_going_negative(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 31, 12, 0, 0, 'Europe/Berlin'));

        $student = $this->makeStudent(1005);

        // A tiny balance, far less than the penalty the 0%-expenses
        // scenario would otherwise compute (~99% of monthly salary).
        Transaction1::create([
            'user_id' => $student->id,
            'sid' => 1005,
            'bank_account_id' => BankAccount::where('student_id', $student->id)->value('id'),
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 5,
            'balance' => 5,
            'is_penalty' => false,
        ]);

        (new BankController())->banks_penalty(app(\App\Services\StatementGenerator::class), $student);

        $penalties = Transaction1::where('user_id', $student->id)->where('category', 'Penalty')->get();
        $this->assertNotEmpty($penalties);
        $this->assertEquals(0.0, (float) $penalties->last()->balance, 'Balance should have floored at 0, not gone negative.');

        Carbon::setTestNow();
    }
}
