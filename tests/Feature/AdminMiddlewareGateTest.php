<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * planning.md Phase 0 #2 / bugs.md #6 asked to confirm whether routes/admin.php
 * actually applies the 'admin' (IsAdmin) middleware anywhere, flagged as a
 * potential live authorization gap. It already does - RouteServiceProvider
 * wraps the whole file in ['web','auth','admin'], and the file re-wraps most
 * routes in ['auth','admin'] again. This pins that down with a real request
 * instead of just reading the route registration.
 */
class AdminMiddlewareGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_away_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_student_role_is_forbidden_from_admin_dashboard(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $response->assertForbidden();
    }

    public function test_school_admin_role_can_reach_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 2]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
    }
}
