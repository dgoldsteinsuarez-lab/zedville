<?php

namespace Tests\Feature\Bank;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * app/Jobs/ProcessScheduledBillPayment.php had no `namespace App\Jobs;`
 * declaration at all, so the class actually lived in the global
 * namespace. BankController imports and dispatches it as
 * App\Jobs\ProcessScheduledBillPayment, which the autoloader could never
 * resolve - any "schedule" or "recurring" bill payment would fatal with
 * "Class not found" the moment it ran. The job also duplicated
 * processBillPayment()'s logic instead of calling it, and that copy was
 * missing sid, the DB transaction/row lock, and the floor-at-zero rule.
 */
class ScheduledBillPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_bill_payment_job_class_resolves_under_its_namespace(): void
    {
        $this->assertTrue(
            class_exists(\App\Jobs\ProcessScheduledBillPayment::class),
            'App\\Jobs\\ProcessScheduledBillPayment must be autoloadable under its namespace.'
        );
    }

    public function test_dispatching_a_scheduled_bill_payment_actually_debits_the_account(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 6001]);
        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 6001,
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 6001,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 100,
            'balance' => 100,
            'is_penalty' => false,
        ]);

        $transfer = Transfer::create([
            'user_id' => $user->id,
            'sid' => 6001,
            'from_account' => 'Primary Savings Account',
            'account_number' => '999999',
            'beneficiary_name' => 'Test Biller',
            'amount' => 40,
            'type' => 'later',
        ]);

        (new \App\Jobs\ProcessScheduledBillPayment($transfer->id))->handle();

        $txn = Transaction1::where('user_id', $user->id)->where('category', 'Bill Payment')->first();
        $this->assertNotNull($txn, 'Expected a Bill Payment transaction to be created.');
        $this->assertEquals(60.0, (float) $txn->balance);
        $this->assertEquals(6001, $txn->sid, 'The scheduled bill payment must carry the student\'s own sid.');
    }
}
