<?php

namespace Tests\Feature\Bank;

use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: bought groceries in the Supermarket, but the bank
 * statement showed two completely different items he never bought
 * ("Acoustic Guitar", "Competition Basketball"). Root cause: the
 * Supermarket's grocery catalog lives in a separate `supermarkets` table
 * with its own auto-incrementing id sequence - unrelated to the City
 * Mall's `products` table - but OrderController::placeOrder() always
 * resolved `cart.*.id` against `products` regardless of which store the
 * order came from. A grocery item's id (e.g. 47, "Chicken") silently
 * collided with an unrelated products.id (47, "Competition Basketball"),
 * charging for - and recording - the wrong item and the wrong price.
 *
 * Fixed with an explicit `catalog` field ('products' default, or
 * 'supermarkets') the Supermarket pages now send on checkout.
 */
class PlaceOrderSupermarketCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithFunds(float $balance): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => 3002]);

        $account = BankAccount::create([
            'student_id' => $user->id,
            'sid' => 3002,
            'student_name' => $user->name,
            'student_email' => $user->email,
            'card_pin' => '1234',
            'is_open_emergency_account' => 0,
            'is_open_money_market_account' => 0,
        ]);

        Transaction1::create([
            'user_id' => $user->id,
            'sid' => 3002,
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

    public function test_a_colliding_id_still_charges_the_correct_supermarket_item_not_the_unrelated_product(): void
    {
        $user = $this->makeStudentWithFunds(100);

        // Deliberately the same id in both tables, different items/prices -
        // this is exactly what happened live (id 47: Chicken vs Competition
        // Basketball).
        $groceryId = DB::table('supermarkets')->insertGetId([
            'product_name' => 'Chicken',
            'type' => 'omnivore',
            'category' => 'Needs',
            'price' => 7.72,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('products')->insert([
            'id' => $groceryId,
            'product_name' => 'Competition Basketball',
            'type' => 'BeSpirit - Sport Shop',
            'category' => 'Sports',
            'price' => 70.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234',
            'cart' => [
                ['id' => $groceryId, 'quantity' => 1],
            ],
            'catalog' => 'supermarkets',
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true, 'total' => 7.72]);

        $this->assertDatabaseHas('order_items', [
            'name' => 'Chicken',
            'subtotal' => 7.72,
        ]);
        $this->assertDatabaseMissing('order_items', [
            'name' => 'Competition Basketball',
        ]);
    }

    public function test_omitting_catalog_still_defaults_to_products_for_city_mall_orders(): void
    {
        $user = $this->makeStudentWithFunds(100);
        $productId = DB::table('products')->insertGetId([
            'product_name' => 'Test Widget',
            'type' => 'good',
            'category' => 'Needs',
            'price' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/order/place', [
            'pin' => '1234',
            'cart' => [
                ['id' => $productId, 'quantity' => 1],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['status' => true, 'total' => 30.0]);
    }
}
