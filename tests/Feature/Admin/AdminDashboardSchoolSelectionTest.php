<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-20: "when I enter the admin, I should be able to
 * select the school right away." The Dashboard's own inline school-select
 * form was removed in favor of a prompt reachable from every admin page
 * (the header badge, and - added later the same day after Daniel said the
 * badge alone was too easy to miss - the full-width "Managing"/"No school
 * selected" banner in layouts/admin.blade.php, see
 * ManagingSchoolBannerTest) - this guards that the Dashboard still
 * renders correctly without the old inline form, and that the underlying
 * AdminDashboardController::setSchool() the modal posts to still works
 * (bugs.md, 2026-10-20).
 */
class AdminDashboardSchoolSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_for_a_super_admin_with_no_school_selected(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('NO SCHOOL SELECTED');
    }

    public function test_selecting_a_school_sets_the_session_and_dashboard_reflects_it(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Zedville Academy',
            'school_code' => 'ZED1',
            'school_domain' => 'zedville-academy.test',
        ]);

        $this->actingAs($superAdmin)
            ->post('/admin/school', ['school_id' => $school->id])
            ->assertRedirect('/admin/dashboard');

        $this->assertSame($school->id, session('selected_school'));

        $response = $this->actingAs($superAdmin)->get('/admin/dashboard');
        $response->assertOk();
        $response->assertSee('Zedville Academy');
        $response->assertDontSee('NO SCHOOL SELECTED');
    }
}
