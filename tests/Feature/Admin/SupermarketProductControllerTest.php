<?php

namespace Tests\Feature\Admin;

use App\Models\Supermarket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\SupermarketProductController - written
 * while extracting it out of AdminEducationController (planning.md
 * Phase 3 #4). Previously uncovered.
 */
class SupermarketProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_supermarket_products(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->postJson('/admin/education/supermarket-store', [
            'products' => [
                ['product_name' => 'Apples', 'type' => 'good', 'category' => 'Needs', 'price' => 3, 'quantity' => 10],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('supermarkets', ['product_name' => 'Apples', 'sid' => 5001]);
    }

    public function test_admin_can_list_supermarket_products(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        Supermarket::create(['product_name' => 'Bananas', 'type' => 'good', 'category' => 'Needs', 'price' => 2, 'quantity' => 5]);

        $response = $this->actingAs($admin)->getJson('/admin/education/supermarket-list');

        $response->assertOk();
        $response->assertJsonFragment(['product_name' => 'Bananas']);
    }

    public function test_admin_can_update_a_supermarket_product(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $product = Supermarket::create(['product_name' => 'Old', 'type' => 'good', 'category' => 'Needs', 'price' => 1, 'quantity' => 1]);

        $response = $this->actingAs($admin)->postJson("/admin/education/supermarket-update/{$product->id}", [
            'product_name' => 'New',
            'type' => 'good',
            'category' => 'Wants',
            'price' => 9,
            'quantity' => 2,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('supermarkets', ['id' => $product->id, 'product_name' => 'New', 'price' => 9]);
    }

    public function test_admin_can_delete_a_supermarket_product_with_matching_confirm_code(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $product = Supermarket::create(['product_name' => 'Delete Me', 'type' => 'good', 'category' => 'Needs', 'price' => 1, 'quantity' => 1]);

        $response = $this->actingAs($admin)->delete("/admin/education/supermarket-delete/{$product->id}", [
            'confirm_code' => 'ABC',
            'delete_code' => 'ABC',
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('supermarkets', ['id' => $product->id]);
    }
}
