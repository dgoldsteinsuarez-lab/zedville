<?php

namespace Tests\Feature\Bank;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for EFDController::storeDonation(). The balance check
 * already uses BalanceService::lockedBalance() (bugs.md #5), but the
 * resulting ledger row never set 'sid' at all - every donation would be
 * recorded with a NULL sid, unlike every other transaction type.
 */
class DonationTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithFunds(float $balance): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 4001]);

        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 4001,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'card_pin' => '1234',
            'is_open_emergency_account' => 0,
            'is_open_money_market_account' => 0,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 4001,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => $balance,
            'balance' => $balance,
            'is_penalty' => false,
        ]);

        return $user;
    }

    public function test_donation_transaction_carries_the_students_own_sid(): void
    {
        $user = $this->makeStudentWithFunds(100);

        $response = $this->actingAs($user)->postJson('/education/store-donation', [
            'pin' => '1234',
            'npo_name' => 'Test Charity',
            'account_no' => '12345',
            'amount' => 25,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true]);

        $txn = Transaction1::where('user_id', $user->id)->where('category', 'Donation')->first();
        $this->assertNotNull($txn, 'Expected a Donation transaction to be created.');
        $this->assertEquals(4001, $txn->sid);
        $this->assertEquals(75.0, (float) $txn->balance);
    }

    public function test_donation_rejects_insufficient_balance(): void
    {
        $user = $this->makeStudentWithFunds(10);

        $response = $this->actingAs($user)->postJson('/education/store-donation', [
            'pin' => '1234',
            'npo_name' => 'Test Charity',
            'account_no' => '12345',
            'amount' => 25,
        ]);

        $response->assertOk();
        $response->assertJson(['status' => false, 'message' => 'Insufficient balance.']);

        $this->assertDatabaseMissing('transactions1', [
            'user_id' => $user->id,
            'category' => 'Donation',
        ]);
    }
}
