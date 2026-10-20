<?php

namespace Tests\Feature\Admin;

use App\Models\FinheroActivityDefault;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21, from a screenshot of the "Add Activity" (FinHero
 * Task/Library) modal: "i can still see all the unnecesarry settings here
 * and i need to be able to add start and due date in all activities,
 * including the tasks, library and so on."
 *
 * "Unnecessary settings" read in context of his earlier statement the
 * same day ("there is one about adding points, but that is solved, as we
 * already assigned points in badges separately") - Max Points, not
 * Salary (he separately asked for a sensible default for that, not its
 * removal). Max Points is no longer collected per-activity; every
 * activity now always falls back to the global default
 * (FinheroActivityDefault), the same mechanism already used whenever an
 * activity's own max_points is null.
 */
class FinheroActivityFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_an_activity_without_max_points_falls_back_to_the_global_default(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        FinheroActivityDefault::create(['activity_key' => 'task_9', 'type' => 'task', 'max_points' => 42]);

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_9',
            'activity_name' => 'Test Task',
            'position' => 'required',
            'type' => 'task',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        // The form never collects max_points at all any more - the row
        // itself stays null, relying entirely on the global default.
        $this->assertDatabaseHas('finhero_activity_registry', [
            'activity_key' => 'task_9',
            'max_points' => null,
        ]);
    }

    public function test_an_admin_can_set_a_start_and_due_date_on_a_task_activity(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_9',
            'activity_name' => 'Test Task',
            'position' => 'required',
            'type' => 'task',
            'is_active' => '1',
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('finhero_activity_registry', [
            'activity_key' => 'task_9',
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);
        // Same FullCalendar exclusive-end +1-day adjustment as the other
        // "position" screens.
        $this->assertDatabaseHas('calendar_events', [
            'title' => 'task-Test Task',
            'start' => '2026-11-02 00:00:00',
            'end' => '2026-11-07 00:00:00',
        ]);
    }

    public function test_an_admin_can_set_a_start_and_due_date_on_a_library_activity(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_3',
            'activity_name' => 'Test Library Item',
            'position' => 'optional',
            'type' => 'library',
            'is_active' => '1',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-15',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('finhero_activity_registry', [
            'activity_key' => 'task_3',
            'type' => 'library',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-15',
        ]);
    }

    public function test_editing_an_activity_clears_max_points_and_updates_the_date_range(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $id = DB::table('finhero_activity_registry')->insertGetId([
            'activity_key' => 'task_9', 'activity_name' => 'Old Name', 'type' => 'task',
            'max_points' => 30, 'position' => 'required', 'is_active' => 1, 'salary' => 2000,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put('/admin/finhero/activities/' . $id, [
            'activity_name' => 'Old Name',
            'position' => 'required',
            'salary' => 2000,
            'is_active' => '1',
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('finhero_activity_registry', [
            'id' => $id,
            'max_points' => null,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
        ]);
    }

    public function test_an_end_date_before_the_start_date_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_9',
            'activity_name' => 'Test Task',
            'position' => 'required',
            'type' => 'task',
            'is_active' => '1',
            'start_date' => '2026-11-06',
            'end_date' => '2026-11-02',
        ]);

        $response->assertSessionHasErrors('end_date');
        $this->assertDatabaseMissing('finhero_activity_registry', ['activity_key' => 'task_9']);
    }
}
