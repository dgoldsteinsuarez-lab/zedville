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
 * high_spending_activities()/low_spending_activities() resolving the
 * class's actual assignment instead of a hardcoded/broken lookup.
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

    private function assign(SchoolClass $class, string $activityKey, Activity $activity): void
    {
        DB::table('mba_position')->insert([
            'user_id' => 1,
            'activity_key' => $activityKey,
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
        $this->assign($class, 'hba_position', $activity);

        $response = $this->actingAs($student)->get('/education/high-spending-activities');

        $response->assertOk();
        $response->assertSee('Music Program Donation');
        // The shop name shown must be the real product-type string, not
        // the catalog's original shorter display text.
        $response->assertSee('Beats - Music Store', false);
    }

    public function test_low_spending_page_now_passes_data_and_shows_only_the_assigned_activity(): void
    {
        // Previously low_spending_activities() passed zero data and showed
        // all 10 activities unconditionally with dead shop links.
        [$student, $class] = $this->studentInClass();
        $activity = Activity::where('type', 'low')->where('number', 8)->first();
        $this->assign($class, 'lba_position', $activity);

        $response = $this->actingAs($student)->get('/education/low-spending-activities');

        $response->assertOk();
        $response->assertSee('Clothing Donation');
        $response->assertDontSee('Accessories for Seniors');
        // The shop link must be the real shopping-destination URL, not the
        // prototype's dead placeholder (href="#" is legitimately used
        // elsewhere on the page, e.g. the breadcrumb, so this checks for
        // the real URL rather than a blanket absence of "#").
        $response->assertSee('spending-tracker/basicco?activity_id=' . $activity->id, false);
    }

    public function test_a_student_only_sees_their_own_classs_activity_not_another_classs(): void
    {
        [$student, $class] = $this->studentInClass();
        $otherGrade = Grade::create(['sid' => 1, 'name' => 'Other Grade', 'grade_level_id' => GradeLevel::first()->id]);
        $otherClass = SchoolClass::create(['sid' => 1, 'grade_id' => $otherGrade->id, 'name' => 'Other Class']);

        $this->assign($otherClass, 'hba_position', Activity::where('type', 'high')->where('number', 1)->first());
        $this->assign($class, 'hba_position', Activity::where('type', 'high')->where('number', 9)->first());

        $response = $this->actingAs($student)->get('/education/high-spending-activities');

        $response->assertOk();
        $response->assertSee('Computer Donation');
        $response->assertDontSee('Birthday Party Budget');
    }

    public function test_high_and_low_assignments_for_the_same_class_are_independent(): void
    {
        [$student, $class] = $this->studentInClass();
        $this->assign($class, 'hba_position', Activity::where('type', 'high')->where('number', 2)->first());
        $this->assign($class, 'lba_position', Activity::where('type', 'low')->where('number', 7)->first());

        $high = $this->actingAs($student)->get('/education/high-spending-activities');
        $low = $this->actingAs($student)->get('/education/low-spending-activities');

        $high->assertSee('Digital Upgrade')->assertDontSee('Community Tech Items');
        $low->assertSee('Community Tech Items')->assertDontSee('Digital Upgrade');
    }
}
