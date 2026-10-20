<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-20: "I also would like to be able to switch from my
 * superadmin to all other roles without having to sign in and out...
 * being able to see the rights of School Admin, tutor or student."
 *
 * Real-user impersonation (ImpersonateController) rather than a generic
 * role preview - every isSuperAdmin()/isSchoolAdmin()/isTutor()/
 * isStudent() check reads $this->role straight off the authenticated
 * user, so logging in as a real user of that role shows its exact real
 * rights with nothing else to keep in sync.
 */
class ImpersonateControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_as_a_student_and_return(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $student = User::factory()->create(['role' => 4]);

        $start = $this->actingAs($superAdmin)->post("/admin/impersonate/{$student->id}");
        $start->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($student);
        $this->assertDatabaseHas('impersonation_logs', [
            'impersonator_id' => $superAdmin->id,
            'impersonated_id' => $student->id,
        ]);

        $stop = $this->post('/impersonate/stop');
        $stop->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($superAdmin);
        $this->assertDatabaseHas('impersonation_logs', [
            'impersonator_id' => $superAdmin->id,
            'impersonated_id' => $student->id,
        ]);
        $this->assertNotNull(
            \DB::table('impersonation_logs')
                ->where('impersonator_id', $superAdmin->id)
                ->where('impersonated_id', $student->id)
                ->value('ended_at')
        );
    }

    public function test_super_admin_can_view_as_a_tutor_and_lands_on_admin_dashboard(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $tutor = User::factory()->create(['role' => 3]);

        $response = $this->actingAs($superAdmin)->post("/admin/impersonate/{$tutor->id}");

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($tutor);
    }

    public function test_a_non_super_admin_cannot_start_impersonation(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($schoolAdmin)->post("/admin/impersonate/{$student->id}");

        $response->assertForbidden();
        $this->assertAuthenticatedAs($schoolAdmin);
    }

    public function test_cannot_impersonate_another_super_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $otherSuperAdmin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($superAdmin)->post("/admin/impersonate/{$otherSuperAdmin->id}");

        $response->assertForbidden();
        $this->assertAuthenticatedAs($superAdmin);
    }

    public function test_cannot_start_a_second_impersonation_while_already_impersonating(): void
    {
        $superAdmin = User::factory()->create(['role' => 1]);
        $studentOne = User::factory()->create(['role' => 4]);
        $studentTwo = User::factory()->create(['role' => 4]);

        $this->actingAs($superAdmin)->post("/admin/impersonate/{$studentOne->id}");

        // Still authenticated as studentOne now - but the admin/impersonate
        // route is gated role:super_admin, so a student hitting it is
        // rejected before start() even runs; confirms stacking is blocked
        // either way.
        $response = $this->post("/admin/impersonate/{$studentTwo->id}");

        $response->assertForbidden();
        $this->assertAuthenticatedAs($studentOne);
    }

    public function test_stop_without_an_active_impersonation_is_forbidden(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->post('/impersonate/stop');

        $response->assertForbidden();
    }
}
