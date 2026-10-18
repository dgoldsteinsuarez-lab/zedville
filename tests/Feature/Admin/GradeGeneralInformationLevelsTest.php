<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Same bug as GradeLevelsGlobalTest, found separately by Daniel clicking
 * through the live admin: GradeGeneralInformationController::show() also
 * filtered GradeLevel::where('sid', $grade->sid) - the Grades CRUD screen
 * was fixed, but this General Information page (a different controller
 * showing the same Grade) still filtered out every Level, leaving only
 * "Unassigned" selectable.
 */
class GradeGeneralInformationLevelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_information_page_lists_all_four_levels(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8A']);

        $response = $this->actingAs($admin)->get(route('admin.grades.general-information', $grade->id));

        $response->assertOk();
        foreach (['Rookie', 'Intermediate', 'Advance', 'Advance Economist'] as $name) {
            $response->assertSee($name);
        }
    }

    public function test_assigning_a_level_from_this_page_actually_saves(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8A']);
        $rookie = GradeLevel::where('name', 'Rookie')->first();

        $response = $this->actingAs($admin)->put(route('admin.grades.update', $grade->id), [
            'name' => 'Grade 8A',
            'grade_level_id' => $rookie->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('grades', ['id' => $grade->id, 'grade_level_id' => $rookie->id]);
    }
}
