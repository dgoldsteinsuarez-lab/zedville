<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * "Wants Items" are "Fix for all schools" (Admin Structure Proposal doc
 * §7.1), same as Supermarket/City Mall/NPOs - banks_penalty() already reads
 * this table without any sid filter, so items must be seeded (and added by
 * admin) as global (sid null), not tied to whichever school happens to be
 * selected in the admin's session at the time.
 */
class PenaltyWantsItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_wants_items_are_global_not_tied_to_a_school(): void
    {
        $count = DB::table('penalty_wants_items')->whereNull('sid')->count();

        $this->assertGreaterThanOrEqual(23, $count);
    }

    public function test_admin_adding_an_item_while_a_school_is_selected_still_saves_it_as_global(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $this->withSession(['selected_school' => 'some-school-id']);

        $this->actingAs($admin)->postJson(route('admin.admin.education.wants-iteams-store'), [
            'products' => [
                ['product_name' => 'Test Item', 'price' => 10],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('penalty_wants_items', [
            'item_name' => 'Test Item',
            'sid' => null,
        ]);
    }

    public function test_admin_can_edit_a_global_item_while_a_school_is_selected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $itemId = DB::table('penalty_wants_items')->insertGetId([
            'sid' => null, 'item_name' => 'Old Name', 'price' => 10,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->withSession(['selected_school' => 'some-school-id']);

        $this->actingAs($admin)->post(route('admin.admin.education.wants-iteams-update', $itemId), [
            'product_name' => 'New Name',
            'price' => 20,
        ])->assertOk();

        $this->assertDatabaseHas('penalty_wants_items', [
            'id' => $itemId,
            'item_name' => 'New Name',
            'price' => 20,
        ]);
    }
}
