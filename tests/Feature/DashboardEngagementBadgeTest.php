<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * DashboardController::badges() had its own 4th, independent ad-hoc
 * Engagement badge calculation (calculateEngagementBadge()), querying
 * calendar_events.activityType - a column no admin screen ever actually
 * writes to for required/optional marking (they all write `position`).
 * It also only fell back to reading the real student_badge_records table
 * when BOTH Engagement and FinHero's ad-hoc calculations produced
 * nothing, so an earned Engagement badge could be hidden by an unrelated
 * FinHero quirk. Replaced with a direct read from the real
 * BadgeCalculatorService/student_badge_records (bugs.md, 2026-10-20).
 */
class DashboardEngagementBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_a_real_earned_engagement_badge(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $now = now();

        $eventId = DB::table('calendar_events')->insertGetId([
            'title'      => 'Required Activity',
            'position'   => 'required',
            'start'      => $now->toDateTimeString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('activity_completions')->insert([
            'student_id'    => $student->id,
            'activity_id'   => $eventId,
            'activity_type' => 'required',
            'completed_at'  => $now,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        DB::table('activity_completions')->insert([
            'student_id'    => $student->id,
            'activity_type' => 'optional',
            'completed_at'  => $now,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        for ($i = 0; $i < 5; $i++) {
            DB::table('participation_logs')->insert([
                'student_id'    => $student->id,
                'action_type'   => 'optional',
                'action_detail' => 'poster_room_enter',
                'points_earned' => 1,
                'month'         => $now->month,
                'year'          => $now->year,
                'academic_year' => '2026-2027',
                'earned_at'     => $now,
            ]);
        }

        $this->actingAs($student)->get('/dashboard')
            ->assertOk()
            ->assertSee('Engagement (Bronze)');
    }
}
