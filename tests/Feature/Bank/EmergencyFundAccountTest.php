<?php

namespace Tests\Feature\Bank;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for BankController::emengercyfundaccount() - opening
 * the Emergency Fund Account debits config('zedville.emargency_fund_persentage')%
 * of the monthly salary from the student's balance. Written while
 * migrating its Transaction1::create(...) call onto LedgerService.
 */
class EmergencyFundAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_emergency_fund_account_debits_the_configured_percentage_and_stamps_sid(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 8001]);
        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 8001,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'is_open_emergency_account' => 0,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 8001,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 1000,
            'balance' => 1000,
            'is_penalty' => false,
        ]);

        $expectedSavings = round(config('zedville.monthly_salary', 3952.40) * (config('zedville.emargency_fund_persentage', 20) / 100), 2);

        $response = $this->actingAs($user)->post('/bank-account/emengercy-fund-account');

        $response->assertRedirect();

        $txn = Transaction1::where('user_id', $user->id)->where('category', 'Savings')->first();
        $this->assertNotNull($txn, 'Expected a Savings debit transaction.');
        $this->assertEquals(8001, $txn->sid);
        $this->assertEquals($expectedSavings, (float) $txn->amount);
        $this->assertEquals(round(1000 - $expectedSavings, 2), (float) $txn->balance);

        $account->refresh();
        $this->assertEquals(1, (int) $account->is_open_emergency_account);
    }

    public function test_opening_emergency_fund_account_rejects_insufficient_balance(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 8002]);
        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 8002,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'is_open_emergency_account' => 0,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 8002,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 5,
            'balance' => 5,
            'is_penalty' => false,
        ]);

        $response = $this->actingAs($user)->post('/bank-account/emengercy-fund-account');

        $response->assertRedirect();
        $this->assertDatabaseMissing('transactions1', [
            'user_id' => $user->id,
            'category' => 'Savings',
        ]);
    }
}
