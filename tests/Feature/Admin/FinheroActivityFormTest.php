<?php

namespace Tests\Feature\Admin;

use App\Models\FinheroActivityDefault;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
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

    /**
     * Daniel, 2026-10-21 (Esperanza bug): a FinHero activity saved without
     * a class is invisible to every class-scoped read downstream (Required
     * Activities, the student's own calendar) - cid/classId is required
     * now, so every test here needs a real class to submit.
     */
    private function aClass(): SchoolClass
    {
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);

        return SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
    }

    public function test_adding_an_activity_without_max_points_falls_back_to_the_global_default(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $class = $this->aClass();
        FinheroActivityDefault::create(['activity_key' => 'task_9', 'type' => 'task', 'max_points' => 42]);

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_9',
            'activity_name' => 'Test Task',
            'position' => 'required',
            'type' => 'task',
            'is_active' => '1',
            'classId' => $class->id,
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
        $class = $this->aClass();

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_9',
            'activity_name' => 'Test Task',
            'position' => 'required',
            'type' => 'task',
            'is_active' => '1',
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
            'classId' => $class->id,
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
        $class = $this->aClass();

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_3',
            'activity_name' => 'Test Library Item',
            'position' => 'optional',
            'type' => 'library',
            'is_active' => '1',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-15',
            'classId' => $class->id,
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
        $class = $this->aClass();

        $id = DB::table('finhero_activity_registry')->insertGetId([
            'activity_key' => 'task_9', 'activity_name' => 'Old Name', 'type' => 'task',
            'cid' => $class->id,
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
            'classId' => $class->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('finhero_activity_registry', [
            'id' => $id,
            'max_points' => null,
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-05',
        ]);
    }

    /**
     * Daniel, 2026-10-21: "classes have the same tasks, should be for
     * grade, the selection, by default... One grade can have many
     * classes." Assigning to the Grade reaches every class under it.
     */
    public function test_an_activity_assigned_to_a_grade_reaches_every_class_in_it(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::first() ?? GradeLevel::create(['name' => 'Rookie', 'sort_order' => 1]);
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $classA = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'A ' . uniqid()]);
        $classB = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'B ' . uniqid()]);

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_9',
            'activity_name' => 'Grade-wide Task',
            'position' => 'required',
            'type' => 'task',
            'is_active' => '1',
            'grade_id' => $grade->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('finhero_activity_registry', [
            'activity_key' => 'task_9',
            'grade_id' => $grade->id,
            'cid' => null,
        ]);
        // One calendar_events row per class in the grade, not just one.
        $this->assertDatabaseHas('calendar_events', ['title' => 'task-Grade-wide Task', 'classId' => $classA->id]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'task-Grade-wide Task', 'classId' => $classB->id]);
    }

    public function test_re_saving_an_activity_that_had_no_class_replaces_its_classless_calendar_entry(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $class = $this->aClass();

        $id = DB::table('finhero_activity_registry')->insertGetId([
            'activity_key' => 'task_1', 'activity_name' => 'Classify Your Salary', 'type' => 'task',
            'sid' => 1, 'cid' => null, 'position' => 'required', 'is_active' => 1, 'salary' => 2000,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('calendar_events')->insert([
            'sid' => 1, 'classId' => null, 'title' => 'task-Classify Your Salary', 'position' => 'required',
            'start' => now(), 'end' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->withSession(['selected_school' => 1])->put('/admin/finhero/activities/' . $id, [
            'activity_name' => 'Classify Your Salary', 'position' => 'required', 'salary' => 2000, 'is_active' => '1',
            'grade_id' => $class->grade_id,
        ])->assertRedirect();

        $this->assertDatabaseMissing('calendar_events', ['title' => 'task-Classify Your Salary', 'classId' => null]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'task-Classify Your Salary', 'classId' => $class->id]);
    }

    public function test_an_end_date_before_the_start_date_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $class = $this->aClass();

        $response = $this->actingAs($admin)->post('/admin/finhero/activities', [
            'activity_key' => 'task_9',
            'activity_name' => 'Test Task',
            'position' => 'required',
            'type' => 'task',
            'is_active' => '1',
            'start_date' => '2026-11-06',
            'end_date' => '2026-11-02',
            'classId' => $class->id,
        ]);

        $response->assertSessionHasErrors('end_date');
        $this->assertDatabaseMissing('finhero_activity_registry', ['activity_key' => 'task_9']);
    }
}
