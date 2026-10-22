<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * ProfileController::getEngagementData() now reads from the real,
 * spec-delivered BadgeCalculatorService/student_badge_records instead of
 * its own inline recalculation (bugs.md, 2026-10-19) - confirms the
 * Settings page actually shows an earned badge, not just the NONE edge
 * case covered by EngagementBadgeNoRequiredEventsTest.
 */
class EngagementBadgeSettingsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_shows_a_real_earned_badge(): void
    {
        $student = User::factory()->create(['role' => 4]);
        // Required events are counted per class, so the student needs one.
        $class = \App\Models\SchoolClass::create(['sid' => $student->sid ?? '1', 'name' => 'Class ' . uniqid()]);
        $student->forceFill(['grade' => $class->id])->save();
        $now = now();

        $eventId = DB::table('calendar_events')->insertGetId([
            'title'        => 'Required Activity',
            'classId'        => $class->id,
            'position'     => 'required',
            'start'        => $now->toDateTimeString(),
            'created_at'   => $now,
            'updated_at'   => $now,
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

        $response = $this->actingAs($student)->get('/profile');

        $response->assertOk();
        $response->assertSee('engData = {"monthly":"BRONZE"', false);
    }
}
