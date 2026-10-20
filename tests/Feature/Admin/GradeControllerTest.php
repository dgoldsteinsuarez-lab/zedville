<?php

namespace Tests\Feature\Admin;

use App\Models\Grade;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel: "I can not get inside grades in Admin" - GradeController::index()
 * hard-aborted with a raw 422 exception page ("Select a school first.")
 * whenever no school was selected, instead of a friendly redirect. No
 * test previously existed for this controller at all. Fixed alongside
 * the "Managing: [School]" banner (layouts/admin.blade.php) that makes
 * the current/missing selection clearly visible on every admin page
 * (bugs.md, 2026-10-20).
 */
class GradeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_grades_with_no_school_selected_redirects_instead_of_crashing(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/grades');

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_visiting_grades_with_a_school_selected_lists_only_that_schools_grades(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create(['school_name' => 'Zedville Academy', 'school_code' => 'ZEDG', 'school_domain' => 'zedg.test']);
        $otherSchool = SchoolDomain::create(['school_name' => 'Other School', 'school_code' => 'OTHG', 'school_domain' => 'othg.test']);

        Grade::create(['sid' => $school->id, 'name' => 'Grade 5']);
        Grade::create(['sid' => $otherSchool->id, 'name' => 'Grade 9']);

        session(['selected_school' => $school->id]);

        $response = $this->actingAs($admin)->get('/admin/grades');

        $response->assertOk();
        $response->assertSee('Grade 5');
        $response->assertDontSee('Grade 9');
    }

    public function test_creating_a_grade_with_no_school_selected_redirects_instead_of_crashing(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post('/admin/grades', ['name' => 'Grade 6']);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertDatabaseMissing('grades', ['name' => 'Grade 6']);
    }
}
