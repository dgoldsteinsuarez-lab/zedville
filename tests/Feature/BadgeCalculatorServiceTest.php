<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BadgeCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * BadgeCalculatorService had two real query bugs (bugs.md, 2026-10-19):
 * getRequiredAssigned()/getRequiredCompleted() filtered on
 * calendar_events.position (always null/unused) instead of the real
 * activityType column, and getParticipationPoints() filtered on a
 * created_at column participation_logs doesn't have. Both meant a
 * student could never earn anything above NONE even with everything
 * actually completed.
 */
class BadgeCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_who_completes_everything_actually_earns_a_real_tier(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $now = now();

        $eventId = DB::table('calendar_events')->insertGetId([
            'title'        => 'Required Activity',
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

        for ($i = 0; $i < 4; $i++) {
            DB::table('activity_completions')->insert([
                'student_id'    => $student->id,
                'activity_type' => 'optional',
                'completed_at'  => $now,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }

        for ($i = 0; $i < 15; $i++) {
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

        $record = app(BadgeCalculatorService::class)
            ->calculateForStudent($student->id, $now->month, $now->year);

        $this->assertSame('PLATINUM', $record->monthly_badge);
    }
}
