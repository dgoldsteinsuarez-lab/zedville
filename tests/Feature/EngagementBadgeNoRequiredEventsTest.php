<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel's real spec (engagement_badge_spec_v2.docx, §7 Edge Cases,
 * 2026-10-19): "No Required activities assigned that month → No badge
 * can be earned that month. The Required category cannot be satisfied if
 * the calendar is empty." An earlier pass "fixed" this the other way
 * (treating 0 assigned as 100%) before this doc was available - reverted
 * once it turned out to be intended behavior, not a bug. Now that the
 * Settings page reads from the real BadgeCalculatorService/
 * student_badge_records instead of its own inline query, this exercises
 * that path end to end.
 */
class EngagementBadgeNoRequiredEventsTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_badge_is_possible_when_nothing_is_required_this_month(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $now = now();

        // Plenty of optional participation, but zero calendar_events
        // (matching the real live state) - per spec, still NONE.
        DB::table('participation_logs')->insert([
            'student_id' => $student->id,
            'action_type' => 'optional',
            'action_detail' => 'poster_room_enter',
            'points_earned' => 5,
            'month' => $now->month, // 1-based, matching BadgeCalculatorService
            'year' => $now->year,
            'academic_year' => '2026-2027',
            'earned_at' => $now,
        ]);

        $response = $this->actingAs($student)->get('/profile');

        $response->assertOk();
        // $engagement is embedded as raw JSON inside a <script> block as
        // `engData` (@json() in a non-attribute context doesn't
        // HTML-escape) - check that specific blob's "monthly" value.
        $response->assertSee('engData = {"monthly":"NONE"', false);
    }
}
