<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /badges and /badges/data (routes/badges.php) exist and are registered
 * via RouteServiceProvider, but nothing in the test suite had ever hit
 * them - confirming they actually render end to end (bugs.md, 2026-10-19).
 */
class BadgeRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_their_badges_page(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/badges')->assertOk();
    }

    public function test_student_can_fetch_their_badge_data(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get('/badges/data');

        $response->assertOk();
        $response->assertJsonStructure([
            'student_id', 'academic_year', 'current_month',
            'accumulated_badge', 'accumulated_points', 'history',
        ]);
    }
}
