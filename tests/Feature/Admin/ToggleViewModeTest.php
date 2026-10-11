<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AdminDashboardController::toggleViewMode() re-implemented the
 * admin/tutor role check locally (in_array($user->role, [1,2,3,...]))
 * instead of calling User::isAdminOrTutor(), and the local array was
 * missing role 0 ("Legacy Admin") - the same bug already found and
 * fixed in ClosetController. A Legacy Admin calling this got silently
 * redirected to /dashboard instead of actually toggling view mode.
 */
class ToggleViewModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_admin_role_0_can_toggle_into_citizen_view(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $response = $this->actingAs($admin)->get('/toggle-view-mode');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('view_mode', 'citizen');
    }

    public function test_school_admin_role_2_can_still_toggle_view_mode(): void
    {
        $admin = User::factory()->create(['role' => 2]);

        $response = $this->actingAs($admin)->get('/toggle-view-mode');

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('view_mode', 'citizen');
    }
}
