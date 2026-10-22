<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel (2026-10-19): Levels (Rookie/Intermediate/Advance/Advance
 * Economist) are one fixed set shared by every school - not something each
 * school types in independently. Which Level a given Grade is assigned
 * stays per-school/per-Grade (Grade.grade_level_id); the LIST to choose
 * from is now global. Before this fix, GradeLevel::where('sid', $sid)
 * meant a school with no levels of its own saw an empty dropdown - the one
 * live row was an admin mistakenly typing "Grade 8" into what should have
 * been a Level picker with nothing real to pick from.
 */
class GradeLevelsGlobalTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_levels_exist_globally_after_migration(): void
    {
        // Junior was added 2026-10-21 (see 2026_10_21_180000_add_junior_
        // grade_level.php) - this asserts the current real list, not a
        // historical snapshot of the 2026-10-19 migration this test was
        // originally written against.
        $names = GradeLevel::orderBy('sort_order')->pluck('name');

        $this->assertSame(['Junior', 'Rookie', 'Intermediate', 'Advance', 'Advance Economist'], $names->all());
    }

    public function test_two_different_schools_can_both_assign_the_same_global_level(): void
    {
        $rookie = GradeLevel::where('name', 'Rookie')->first();

        $admin = User::factory()->create(['role' => 1]);

        $this->withSession(['selected_school' => 'school-a']);
        $this->actingAs($admin)->post(route('admin.grades.store'), [
            'name' => 'Grade 8', 'grade_level_id' => $rookie->id,
        ])->assertRedirect();

        $this->withSession(['selected_school' => 'school-b']);
        $this->actingAs($admin)->post(route('admin.grades.store'), [
            'name' => 'Grade 8', 'grade_level_id' => $rookie->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('grades', ['sid' => 'school-a', 'name' => 'Grade 8', 'grade_level_id' => $rookie->id]);
        $this->assertDatabaseHas('grades', ['sid' => 'school-b', 'name' => 'Grade 8', 'grade_level_id' => $rookie->id]);
    }

    public function test_same_school_can_assign_different_levels_to_different_grades(): void
    {
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $intermediate = GradeLevel::where('name', 'Intermediate')->first();
        $admin = User::factory()->create(['role' => 1]);

        $this->withSession(['selected_school' => 'test-school']);
        $this->actingAs($admin)->post(route('admin.grades.store'), ['name' => 'Grade 8', 'grade_level_id' => $rookie->id]);
        $this->actingAs($admin)->post(route('admin.grades.store'), ['name' => 'Grade 9', 'grade_level_id' => $intermediate->id]);

        $this->assertDatabaseHas('grades', ['sid' => 'test-school', 'name' => 'Grade 8', 'grade_level_id' => $rookie->id]);
        $this->assertDatabaseHas('grades', ['sid' => 'test-school', 'name' => 'Grade 9', 'grade_level_id' => $intermediate->id]);
    }

    public function test_only_super_admin_can_add_a_new_level(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);

        $this->actingAs($schoolAdmin)->post(route('admin.admin.grade-level.add'), ['name' => 'Bonus Level'])
            ->assertForbidden();
    }
}
