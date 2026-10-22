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

    /**
     * Daniel, 2026-10-21: "For Academics and PISA we should also give
     * Engagement Badge Points, add this too." Investigated rather than
     * assumed - this already works, and has since AcademyController::
     * submit() was first built: it calls ParticipationService::award(...,
     * 'curriculum_item_complete', ...) on every completed Academy or PISA
     * item (PISA reuses the same submit() route), which lands in
     * participation_logs exactly like every other participation source,
     * and getParticipationPoints() sums all of participation_logs with no
     * per-source filtering. Nothing to build - this is a regression guard
     * confirming the existing wiring, not new behavior.
     */
    public function test_academy_and_pisa_completions_already_count_toward_engagement_participation_points(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $now = now();

        app(\App\Services\ParticipationService::class)->award($student->id, 'curriculum_item_complete', '123');
        app(\App\Services\ParticipationService::class)->award($student->id, 'curriculum_item_complete', '456');

        $record = app(BadgeCalculatorService::class)
            ->calculateForStudent($student->id, $now->month, $now->year);

        $this->assertSame(2, $record->participation_points);
    }

    /**
     * calendar_events are one row per class. Counting every class's
     * required events made 100% completion impossible once a second class
     * existed.
     */
    public function test_only_the_students_own_class_events_count_as_required(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $ownClass = \App\Models\SchoolClass::create(['sid' => '1', 'name' => 'Own ' . uniqid()]);
        $otherClass = \App\Models\SchoolClass::create(['sid' => '1', 'name' => 'Other ' . uniqid()]);
        $student->forceFill(['grade' => $ownClass->id])->save();

        foreach ([$ownClass, $otherClass] as $class) {
            DB::table('calendar_events')->insert([
                'title' => 'Required Activity', 'classId' => $class->id, 'position' => 'required',
                'start' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $record = app(BadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);

        $this->assertSame(1, $record->required_assigned);
    }

    /**
     * Completions store the activity's own key in activity_type (the EFD
     * blackboard hides finished activities by it), so whether one was
     * optional comes from the calendar event it points at.
     */
    public function test_finishing_an_optional_calendar_activity_counts_as_optional(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $class = \App\Models\SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $student->forceFill(['grade' => $class->id])->save();

        $eventId = DB::table('calendar_events')->insertGetId([
            'title' => 'library-Savings', 'classId' => $class->id, 'position' => 'optional',
            'start' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('activity_completions')->insert([
            'student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'library_task_3',
            'completed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $record = app(BadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);

        $this->assertSame(1, $record->optional_completed);
    }
}
