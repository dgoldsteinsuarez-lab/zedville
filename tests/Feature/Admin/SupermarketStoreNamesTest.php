<?php

namespace Tests\Feature\Admin;

use App\Models\Supermarketname;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * bugs.md #19: nothing in the app ever created Supermarketname rows - the
 * admin product form could only assign a product to an EXISTING store, but
 * there was no screen to create the store itself. This blocked the
 * consumer-profile survey entirely, since its diet step needs a store
 * literally named Omnivore/Vegetarian/Pescatarian/Vegan to find any
 * groceries for that diet.
 */
class SupermarketStoreNamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_store_name(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post(route('admin.admin.education.supermarket-store-names.store'), [
            'name' => 'Omnivore',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supermarketname', ['name' => 'Omnivore']);
    }

    public function test_a_product_assigned_to_a_diet_named_store_shows_up_in_that_diets_grocery_list(): void
    {
        $student = User::factory()->create(['role' => 4]);

        Supermarketname::create(['sid' => $student->sid, 'name' => 'Vegan']);

        DB::table('supermarkets')->insert([
            'sid' => $student->sid,
            'product_name' => 'Tofu',
            'type' => 'Vegan',
            'category' => 'Needs',
            'price' => 5.00,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->getJson('/api/groceries/vegan');

        $response->assertOk();
        $response->assertJsonFragment(['product_name' => 'Tofu']);
    }

    public function test_admin_can_remove_a_store_name(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $store = Supermarketname::create(['name' => 'Test Store']);

        $response = $this->actingAs($admin)->delete(route('admin.admin.education.supermarket-store-names.delete', $store->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('supermarketname', ['id' => $store->id]);
    }
}
