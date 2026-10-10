<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ClosetController::requireAdmin()/requireAdminOrTutor() re-implemented
 * the admin/tutor role check locally instead of using User::isAdmin()/
 * isAdminOrTutor() - and the local version's array was missing role 0
 * ("Legacy Admin"), which every other admin check in the app (including
 * the IsAdmin middleware itself) treats as a real admin. A Legacy Admin
 * got wrongly 403'd on the closet catalog write endpoints, which have no
 * route-level admin middleware to fall back on.
 */
class ClosetAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_admin_role_0_can_write_to_the_closet_catalog(): void
    {
        $admin = User::factory()->create(['role' => 0]);

        $response = $this->actingAs($admin)->postJson('/closet/catalog', [
            'name' => 'Test Item',
            'category' => 'tech',
            'icon' => '📱',
            'priceZeds' => 100,
            'lifespanYears' => 3,
        ]);

        $response->assertStatus(201);
    }

    public function test_student_still_cannot_write_to_the_closet_catalog(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->postJson('/closet/catalog', [
            'name' => 'Test Item',
            'category' => 'tech',
            'icon' => '📱',
            'priceZeds' => 100,
            'lifespanYears' => 3,
        ]);

        $response->assertStatus(403);
    }
}
