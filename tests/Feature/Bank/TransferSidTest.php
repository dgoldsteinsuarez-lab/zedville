<?php

namespace Tests\Feature\Bank;

use App\Http\Controllers\BankController;
use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BankController::processTransfer() stamps BOTH the sender's and the
 * recipient's new ledger row with session('sid') - the SENDER's own
 * session value, which can be anything (an admin acting as the student, a
 * queued run with no session at all). Each row must carry its own account's
 * school. Since the bank review (2026-10-29) money only goes between
 * classmates, so the session here is set to a different value.
 */
class TransferSidTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithAccount(int $sid, array $accountOverrides = []): array
    {
        $class = SchoolClass::firstOrCreate(['sid' => $sid, 'name' => 'Class of school ' . $sid]);
        $user = User::factory()->create(['role' => 4, 'sid' => $sid, 'grade' => $class->id]);

        $account = BankAccount::create(array_merge([
            'student_id' => $user->id,
            'sid' => $sid,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'primary_savings_account_number' => (string) random_int(100000, 999999),
            'is_open_emergency_account' => 0,
            'is_open_money_market_account' => 0,
        ], $accountOverrides));

        return [$user, $account];
    }

    public function test_recipient_transaction_gets_the_recipients_own_sid_not_the_senders(): void
    {
        [$sender, $senderAccount] = $this->makeStudentWithAccount(2001);
        [$recipient, $recipientAccount] = $this->makeStudentWithAccount(2001);
        session(['sid' => 9999]);

        // Give the sender enough balance to cover the transfer.
        Transaction1::create([
            'user_id' => $sender->id,
            'sid' => 2001,
            'bank_account_id' => $senderAccount->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 100,
            'balance' => 100,
            'is_penalty' => false,
        ]);

        $transfer = Transfer::create([
            'user_id' => $sender->id,
            'sid' => 2001,
            'from_account' => 'Primary Savings Account',
            'account_number' => $recipientAccount->primary_savings_account_number,
            'beneficiary_name' => $recipient->name,
            'amount' => 25,
            'type' => 'now',
        ]);

        // session('sid') is somebody else's school here
        (new BankController())->processTransfer($transfer->id, now());

        $recipientTxn = Transaction1::where('user_id', $recipient->id)->where('type', 'credit')->first();
        $senderTxn = Transaction1::where('user_id', $sender->id)->where('type', 'debit')->first();

        $this->assertNotNull($recipientTxn, 'Expected the recipient to receive a credit transaction.');
        $this->assertNotNull($senderTxn, 'Expected the sender to receive a debit transaction.');

        $this->assertEquals(2001, $recipientTxn->sid, "Recipient's transaction must carry the recipient's own sid.");
        $this->assertEquals(2001, $senderTxn->sid, "Sender's transaction must carry the sender's own sid.");
        $this->assertEquals(75.0, (float) $senderTxn->balance);
        $this->assertEquals(25.0, (float) $recipientTxn->balance);
    }

    /** Daniel, 2026-10-29: "transfers only same class" - nothing moves to another school either. */
    public function test_no_transfer_to_a_student_of_another_school(): void
    {
        [$sender, $senderAccount] = $this->makeStudentWithAccount(2001);
        [$recipient, $recipientAccount] = $this->makeStudentWithAccount(2002);
        Transaction1::create(['user_id' => $sender->id, 'sid' => 2001, 'bank_account_id' => $senderAccount->id, 'transaction_date' => now(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income', 'amount' => 100, 'balance' => 100, 'is_penalty' => false]);
        $transfer = Transfer::create(['user_id' => $sender->id, 'sid' => 2001, 'from_account' => 'Primary Savings Account',
            'account_number' => $recipientAccount->primary_savings_account_number, 'beneficiary_name' => $recipient->name, 'amount' => 25, 'type' => 'now']);

        $result = (new BankController())->processTransfer($transfer->id, now());

        $this->assertSame(['ok' => false, 'error' => 'other_class'], $result);
        $this->assertSame(0, Transaction1::where('user_id', $recipient->id)->count());
        $this->assertEquals(100.0, (float) Transaction1::where('user_id', $sender->id)->latest('id')->value('balance'));
    }
}
