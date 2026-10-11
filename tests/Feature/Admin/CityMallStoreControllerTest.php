<?php

namespace Tests\Feature\Admin;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\CityMallStoreController - written while
 * extracting it out of AdminEducationController (planning.md Phase 3
 * #4). Previously uncovered.
 */
class CityMallStoreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_stores(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 'SCHOOL-A']);

        $response = $this->actingAs($admin)->postJson('/admin/education/create-city-mall-store', [
            'stores' => [
                ['store_name' => 'Gadget Hub'],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('stores', ['store_name' => 'Gadget Hub', 'sid' => 'SCHOOL-A']);
    }

    public function test_admin_can_list_stores(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        Store::create(['store_name' => 'Existing Store']);

        $response = $this->actingAs($admin)->getJson('/admin/education/store-list');

        $response->assertOk();
        $response->assertJsonFragment(['store_name' => 'Existing Store']);
    }

    public function test_admin_can_update_a_store(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $store = Store::create(['store_name' => 'Old Name']);

        $response = $this->actingAs($admin)->postJson("/admin/education/store-update/{$store->id}", [
            'store_name' => 'New Name',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'store_name' => 'New Name']);
    }

    public function test_admin_can_delete_a_store_with_matching_confirm_code(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $store = Store::create(['store_name' => 'Delete Me']);

        $response = $this->actingAs($admin)->delete("/admin/education/store-delete/{$store->id}", [
            'confirm_code' => 'ABC',
            'delete_code' => 'ABC',
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
    }
}
