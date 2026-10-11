<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\SpendBudgetProductController - written
 * while extracting it out of AdminEducationController (planning.md
 * Phase 3 #4). Previously uncovered.
 */
class SpendBudgetProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_products(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 'SCHOOL-A']);

        $response = $this->actingAs($admin)->postJson('/admin/education/product-store', [
            'products' => [
                ['product_name' => 'Notebook', 'type' => 'good', 'category' => 'Needs', 'goods_type' => 'Non-Durable', 'price' => 5],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('products', [
            'product_name' => 'Notebook',
            'sid' => 'SCHOOL-A',
            'price' => 5,
        ]);
    }

    public function test_admin_can_list_products(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        Product::create(['product_name' => 'Pen', 'type' => 'good', 'category' => 'Needs', 'price' => 1]);

        $response = $this->actingAs($admin)->getJson('/admin/education/products-list');

        $response->assertOk();
        $response->assertJsonFragment(['product_name' => 'Pen']);
    }

    public function test_admin_can_update_a_product(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $product = Product::create(['product_name' => 'Old', 'type' => 'good', 'category' => 'Needs', 'price' => 1]);

        $response = $this->actingAs($admin)->postJson("/admin/education/products-update/{$product->id}", [
            'product_name' => 'New',
            'type' => 'good',
            'category' => 'Wants',
            'goods_type' => 'Non-Durable',
            'price' => 9,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'product_name' => 'New', 'price' => 9]);
    }

    public function test_admin_can_delete_a_product_with_matching_confirm_code(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $product = Product::create(['product_name' => 'Delete Me', 'type' => 'good', 'category' => 'Needs', 'price' => 1]);

        $response = $this->actingAs($admin)->delete("/admin/education/products-delete/{$product->id}", [
            'confirm_code' => 'ABC',
            'delete_code' => 'ABC',
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
