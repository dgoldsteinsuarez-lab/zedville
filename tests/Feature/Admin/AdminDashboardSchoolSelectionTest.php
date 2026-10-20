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

    public function test_dashboard_shows_school_month_settings_card_for_super_admin_but_not_tutor(): void
    {
        // Daniel, 2026-10-21: "Its better to add the school month settings
        // in Dashboard." Card is gated the same way the route itself is
        // (Super Admin/School Admin, not Tutor).
        $superAdmin = User::factory()->create(['role' => 1]);
        $this->actingAs($superAdmin)->get('/admin/dashboard')->assertSee('School Month Settings');

        $tutor = User::factory()->create(['role' => 3]);
        $this->actingAs($tutor)->get('/admin/dashboard')->assertDontSee('School Month Settings');
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

    /**
     * Found 2026-10-20: the "Change School" modal's actual <select> was
     * name="search" - a leftover from a copy-pasted user-picker - while
     * AdminDashboardController::setSchool() reads $request->school_id.
     * Submitting the real modal therefore always posted school_id as
     * null, which passed the controller's `nullable` validation rule and
     * redirected with a "School selected successfully!" flash message -
     * while actually clearing the selection instead of setting it. Silent
     * both ways: no error shown, and the test above posts school_id
     * directly, bypassing the real form entirely, so it never would have
     * caught this. This test renders the actual modal markup and checks
     * the field name the controller expects is the one really being sent.
     */
    public function test_the_change_school_modals_select_actually_posts_school_id(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        SchoolDomain::create(['school_name' => 'Zedville Academy', 'school_code' => 'ZED2', 'school_domain' => 'zedville-academy-2.test']);

        $html = $this->actingAs($superAdmin)->get('/admin/dashboard')->getContent();

        $this->assertMatchesRegularExpression(
            '/<select\s+name="school_id"\s+id="schoolSelect"/',
            $html,
            'The Change School modal\'s <select> must post as school_id - that\'s the field name AdminDashboardController::setSchool() reads.'
        );
    }
}
