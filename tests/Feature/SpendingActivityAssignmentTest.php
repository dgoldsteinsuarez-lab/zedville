<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "did you find all the activities for Spending
 * activities?" - found 20 real activities (10 High + 10 Low), hardcoded
 * in two Blade files, never admin-manageable, and never actually shown to
 * a student (the old selection mechanism, calendar_events.activityType,
 * was never written with spending-activity intent by any real admin
 * flow). Confirmed via AskUserQuestion: make them real (seeded in the DB)
 * and selectable (admin assigns one per class via
 * Admin\SpendingActivityController, see its own test file).
 *
 * This covers the student-facing side: EFDController::
 * high_spending_activities() resolving the class's actual assignment
 * instead of a hardcoded/broken lookup.
 *
 * Later the same day: "you can remove low spending activities from the
 * system" - Low removed entirely; this file covers High only now.
 */
class SpendingActivityAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function studentInClass(): array
    {
        $level = GradeLevel::first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Test Grade', 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Test Class']);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        return [$student, $class];
    }

    private function assign(SchoolClass $class, Activity $activity): void
    {
        DB::table('mba_position')->insert([
            'user_id' => 1,
            'activity_key' => 'hba_position',
            'activity_id' => $activity->id,
            'sid' => 1,
            'cid' => $class->id,
            'required' => 1,
            'optional' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_high_spending_page_shows_no_activity_assigned_when_nothing_is_set(): void
    {
        [$student] = $this->studentInClass();

        $response = $this->actingAs($student)->get('/education/high-spending-activities');

        $response->assertOk();
        $response->assertSee('No activity assigned');
    }

    public function test_high_spending_page_shows_the_assigned_activity_not_a_hardcoded_one(): void
    {
        [$student, $class] = $this->studentInClass();
        $activity = Activity::where('type', 'high')->where('number', 5)->first();
        $this->assign($class, $activity);

        $response = $this->actingAs($student)->get('/education/high-spending-activities');

        $response->assertOk();
        $response->assertSee('Music Program Donation');
        // The shop name shown must be the real product-type string, not
        // the catalog's original shorter display text.
        $response->assertSee('Beats - Music Store', false);
    }

    public function test_a_student_only_sees_their_own_classs_activity_not_another_classs(): void
    {
        [$student, $class] = $this->studentInClass();
        $otherGrade = Grade::create(['sid' => 1, 'name' => 'Other Grade', 'grade_level_id' => GradeLevel::first()->id]);
        $otherClass = SchoolClass::create(['sid' => 1, 'grade_id' => $otherGrade->id, 'name' => 'Other Class']);

        $this->assign($otherClass, Activity::where('type', 'high')->where('number', 1)->first());
        $this->assign($class, Activity::where('type', 'high')->where('number', 9)->first());

        $response = $this->actingAs($student)->get('/education/high-spending-activities');

        $response->assertOk();
        $response->assertSee('Computer Donation');
        $response->assertDontSee('Birthday Party Budget');
    }
}
