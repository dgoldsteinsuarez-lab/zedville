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
            'bankaccountnumber' => '000111',
            'amount' => 20,
            'startDate' => now()->toDateString(),
            'signature' => $user->name,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertStringNotContainsString('error', (string) session('error'));
        $this->assertDatabaseHas('auto_debit_requests', ['user_id' => $user->id, 'type' => 'Internet']);
        // A permission, not a payment: the bank pays the bill on the 7th
        // (bank review, 2026-10-29 - it was charged here too, so twice).
        $this->assertDatabaseMissing('transactions1', ['user_id' => $user->id, 'type' => 'debit']);
    }

    public function test_signing_needs_no_money_on_the_account(): void
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
            'bankaccountnumber' => '000111',
            'amount' => 20,
            'startDate' => now()->toDateString(),
            'signature' => $user->name,
            'date' => now()->toDateString(),
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('auto_debit_requests', ['user_id' => $user->id, 'type' => 'Internet']);
        $this->assertDatabaseMissing('transactions1', ['user_id' => $user->id, 'type' => 'debit']);
    }
}
