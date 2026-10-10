<?php

namespace Tests\Feature\Bank;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Baseline coverage for OrderController::placeOrder() - the one order
 * placement path all three previously-duplicated methods converged on
 * (placeOrderold/placeOrderActivityold/placeOrderActivity were dead,
 * unrouted code and have been removed). Guards the server-side price
 * trust and the locked-balance check (bugs.md #5) before any further
 * LedgerService-style refactor touches this file.
 */
class PlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithFunds(float $balance): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 3001]);

        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 3001,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'card_pin' => '1234',
            'is_open_emergency_account' => 0,
            'is_open_money_market_account' => 0,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 3001,
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

    private function makeProduct(float $price): int
    {
        return DB::table('products')->insertGetId([
            'product_name' => 'Test Widget',
            'type' => 'good',
            'category' => 'Needs',
            'price' => $price,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_placing_an_order_deducts_the_server_calculated_total_and_stamps_the_students_sid(): void
    {
        $user = $this->makeStudentWithFunds(100);
        $productId = $this->makeProduct(30);

        $response = $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234',
            'cart' => [
                ['id' => $productId, 'quantity' => 2],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true, 'balance' => 40.0, 'total' => 60.0]);

        $txn = Transaction1::where('user_id', $user->id)->where('category', '!=', 'Income')->first();
        $this->assertNotNull($txn);
        $this->assertEquals(3001, $txn->sid);
        $this->assertEquals(40.0, (float) $txn->balance);
    }

    public function test_placing_an_order_ignores_a_client_submitted_price_and_uses_the_catalogue_price(): void
    {
        $user = $this->makeStudentWithFunds(100);
        $productId = $this->makeProduct(30);

        $response = $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234',
            // Client tries to claim the item is free - server must ignore this
            // and use the products table price instead.
            'cart' => [
                ['id' => $productId, 'quantity' => 1, 'price' => 0],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true, 'balance' => 70.0, 'total' => 30.0]);
    }

    public function test_placing_an_order_rejects_insufficient_balance(): void
    {
        $user = $this->makeStudentWithFunds(10);
        $productId = $this->makeProduct(30);

        $response = $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234',
            'cart' => [
                ['id' => $productId, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['status' => false]);

        $this->assertDatabaseMissing('transactions1', [
            'user_id' => $user->id,
            'category' => 'Needs',
        ]);
    }

    public function test_placing_an_order_rejects_the_wrong_pin(): void
    {
        $user = $this->makeStudentWithFunds(100);
        $productId = $this->makeProduct(30);

        $response = $this->actingAs($user)->postJson('/order/place', [
            'pin' => '0000',
            'cart' => [
                ['id' => $productId, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJson(['status' => false, 'message' => 'Invalid PIN.']);
    }
}
