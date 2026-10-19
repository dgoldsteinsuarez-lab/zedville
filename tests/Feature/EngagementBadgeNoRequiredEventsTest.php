<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: "how are you doing with the engagement badge, is
 * it all good?" - no: ProfileController::calculateMonthlyEngagement()
 * forced requiredPct to 0 whenever a month had zero required
 * calendar_events assigned, instead of treating "nothing required" as
 * "nothing missed." Since every badge tier requires requiredPct == 100,
 * this made every tier mathematically impossible in a month with no
 * required events - confirmed live: 0 calendar_events exist at all right
 * now, so every student's Engagement badge was silently stuck at NONE
 * regardless of how much they participated.
 */
class EngagementBadgeNoRequiredEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_can_still_earn_a_badge_tier_when_nothing_is_required_this_month(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $now = now();

        // Enough optional participation for at least Bronze
        // (optionalCount >= 1, participationPts >= 5), with zero
        // calendar_events (matching the real live state) - this used to
        // be impossible to turn into any badge at all.
        DB::table('participation_logs')->insert([
            'student_id' => $student->id,
            'action_type' => 'optional',
            'action_detail' => 'poster_room_enter',
            'points_earned' => 5,
            'month' => $now->month - 1, // calculateMonthlyEngagement uses 0-based months
            'year' => $now->year,
            'academic_year' => '2026-2027',
            'earned_at' => $now,
        ]);

        $response = $this->actingAs($student)->get('/profile');

        $response->assertOk();
        // $engagement is embedded as raw JSON inside a <script> block as
        // `engData` (@json() in a non-attribute context doesn't
        // HTML-escape) - check that specific blob's "monthly" value, not
        // a bare substring match, since the page also embeds a separate
        // FinHero `fhData` block with its own unrelated "monthly" field.
        $response->assertSee('engData = {"monthly":"BRONZE"', false);
    }
}
