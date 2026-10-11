<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\PenaltyWantsItemController - written
 * while extracting it out of AdminEducationController (planning.md
 * Phase 3 #4). Previously uncovered.
 */
class PenaltyWantsItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_wants_items(): void
    {
        $admin = User::factory()->create(['role' => 1, 'sid' => 5001]);
        session(['selected_school' => 'SCHOOL-A']);

        $response = $this->actingAs($admin)->postJson('/admin/education/wants-iteams-store', [
            'products' => [
                ['product_name' => 'Fancy Shoes', 'price' => 50],
                ['product_name' => 'Video Game', 'price' => 30],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('penalty_wants_items', ['item_name' => 'Fancy Shoes', 'price' => 50]);
        $this->assertDatabaseHas('penalty_wants_items', ['item_name' => 'Video Game', 'price' => 30]);
    }

    public function test_admin_can_list_wants_items(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        DB::table('penalty_wants_items')->insert(['item_name' => 'Item A', 'price' => 10, 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($admin)->getJson('/admin/education/wants-iteams-list');

        $response->assertOk();
        $response->assertJsonFragment(['item_name' => 'Item A']);
    }

    public function test_admin_can_update_a_wants_item(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $id = DB::table('penalty_wants_items')->insertGetId(['item_name' => 'Old Item', 'price' => 10, 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($admin)->postJson("/admin/education/wants-iteams-update/{$id}", [
            'product_name' => 'New Item',
            'price' => 25,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('penalty_wants_items', ['id' => $id, 'item_name' => 'New Item', 'price' => 25]);
    }

    public function test_admin_can_delete_a_wants_item_with_matching_confirm_code(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $id = DB::table('penalty_wants_items')->insertGetId(['item_name' => 'Delete Me', 'price' => 10, 'created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($admin)->delete("/admin/education/wants-iteams-delete/{$id}", [
            'confirm_code' => 'ABC',
            'delete_code' => 'ABC',
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('penalty_wants_items', ['id' => $id]);
    }
}
