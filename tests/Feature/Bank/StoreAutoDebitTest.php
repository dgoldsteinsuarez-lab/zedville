<?php

namespace Tests\Feature\Bank;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BankController::store_auto_debit() checked the balance against the
 * legacy `Transaction` model/table instead of `Transaction1` (the one
 * every other balance check in the app uses, and the one with 0 rows
 * ever written to it in the live dev database). That table is always
 * empty, so the balance pre-check always read 0 and the endpoint always
 * rejected with "Insufficient balance", no matter the student's real
 * balance. Six real forms post here, including the citizen-activation
 * onboarding flow.
 */
class StoreAutoDebitTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_auto_debit_succeeds_when_the_student_has_sufficient_real_balance(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 7001]);
        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 7001,
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 7001,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 500,
            'balance' => 500,
            'is_penalty' => false,
        ]);

        $response = $this->actingAs($user)->post('/bank-account/store-auto-debit', [
            'type' => 'Internet',
            'fullname' => $user->name,
            'email' => $user->email,
            'accountno' => 'ACC-1',
            'serviceaddress' => '123 Test St',
            'bankname' => 'Universal Bank',
            'backaccountnumber' => '000111',
            'amount' => 20,
            'startDate' => now()->toDateString(),
            'signature' => $user->name,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertStringNotContainsString('error', (string) session('error'));
        $this->assertDatabaseHas('transactions1', [
            'user_id' => $user->id,
            'category' => 'Internet',
            'amount' => 20,
        ]);
    }

    public function test_store_auto_debit_rejects_genuinely_insufficient_balance(): void
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 7002]);
        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 7002,
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 7002,
            'bank_account_id' => $account->id,
            'transaction_date' => now(),
            'description' => 'Opening balance',
            'type' => 'credit',
            'category' => 'Income',
            'amount' => 5,
            'balance' => 5,
            'is_penalty' => false,
        ]);

        $response = $this->actingAs($user)->post('/bank-account/store-auto-debit', [
            'type' => 'Internet',
            'fullname' => $user->name,
            'email' => $user->email,
            'accountno' => 'ACC-1',
            'serviceaddress' => '123 Test St',
            'bankname' => 'Universal Bank',
            'backaccountnumber' => '000111',
            'amount' => 20,
            'startDate' => now()->toDateString(),
            'signature' => $user->name,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertSame('Insufficient balance for this auto-debit request.', session('error'));
        $this->assertDatabaseMissing('transactions1', [
            'user_id' => $user->id,
            'category' => 'Internet',
        ]);
    }
}
