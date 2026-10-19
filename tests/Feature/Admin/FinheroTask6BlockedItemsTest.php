<?php

namespace Tests\Feature\Admin;

use App\Models\FinheroTask6BlockedItem;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\Supermarket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Substitute Goods feature, FinHero Task 6 (2026-10-19). Admin/tutor-level
 * screen to mark which Supermarket items are "temporarily unavailable"
 * for a Class.
 */
class FinheroTask6BlockedItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mark_items_blocked_for_a_class(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A']);
        $chicken = Supermarket::create(['product_name' => 'Chicken', 'type' => 'omnivore', 'category' => 'Needs', 'price' => 10, 'quantity' => 1]);

        session(['selected_school' => $school->id]);

        $response = $this->actingAs($admin)->post(route('finhero.task6-blocked-items.update'), [
            'classId' => $class->id,
            'blocked' => [$chicken->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('finhero_task6_blocked_items', [
            'sid' => $school->id,
            'cid' => $class->id,
            'supermarket_id' => $chicken->id,
        ]);
    }

    public function test_saving_an_empty_selection_clears_previously_blocked_items(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A']);
        $chicken = Supermarket::create(['product_name' => 'Chicken', 'type' => 'omnivore', 'category' => 'Needs', 'price' => 10, 'quantity' => 1]);
        FinheroTask6BlockedItem::create(['sid' => $school->id, 'cid' => $class->id, 'supermarket_id' => $chicken->id]);

        session(['selected_school' => $school->id]);

        $this->actingAs($admin)->post(route('finhero.task6-blocked-items.update'), [
            'classId' => $class->id,
            'blocked' => [],
        ]);

        $this->assertDatabaseMissing('finhero_task6_blocked_items', [
            'sid' => $school->id,
            'cid' => $class->id,
        ]);
    }

    public function test_tutors_cannot_change_blocked_items(): void
    {
        $tutor = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($tutor)->get(route('finhero.task6-blocked-items.index'));

        $response->assertForbidden();
    }
}
