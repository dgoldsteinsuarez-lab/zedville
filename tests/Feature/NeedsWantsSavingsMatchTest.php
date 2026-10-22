<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "the Match Needs and Wants, is the one called
 * Monthly Budget Activity. You can move it to Finhero Badge Activity and
 * Call It... Needs, Wants and Savings Match... 3 points if done all
 * correct the first time, and let two attempts before loosing one point."
 *
 * The drag-and-drop game (public/asset/front/js/mba.js) never lets a wrong
 * drop land, so "first time" is measured by a client-reported mistake
 * count, not a server-graded answer key - these tests exercise
 * EFDController::storemba()'s scoring of that count directly.
 */
class NeedsWantsSavingsMatchTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::factory()->create(['role' => 4]);
    }

    public function test_zero_mistakes_earns_three_finhero_points(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student)->postJson(route('education.storemba'), [
            'budget' => ['needs' => []],
            'mistakes' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['pointsAwarded' => 3]);
        $this->assertDatabaseHas('finhero_student_points', [
            'student_id' => $student->id, 'source_type' => 'activity',
            'source_key' => 'needs_wants_savings_match', 'points_earned' => 3,
        ]);
    }

    public function test_one_or_two_mistakes_earns_two_points(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student)->postJson(route('education.storemba'), [
            'budget' => ['needs' => []],
            'mistakes' => 2,
        ]);

        $response->assertJson(['pointsAwarded' => 2]);
    }

    public function test_three_or_more_mistakes_earns_one_point(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student)->postJson(route('education.storemba'), [
            'budget' => ['needs' => []],
            'mistakes' => 7,
        ]);

        $response->assertJson(['pointsAwarded' => 1]);
    }

    public function test_only_the_first_completion_this_month_is_scored(): void
    {
        $student = $this->student();

        $this->actingAs($student)->postJson(route('education.storemba'), [
            'budget' => ['needs' => []], 'mistakes' => 5,
        ]);
        $second = $this->actingAs($student)->postJson(route('education.storemba'), [
            'budget' => ['needs' => []], 'mistakes' => 0,
        ]);

        $second->assertJson(['pointsAwarded' => 0]);
        $this->assertSame(
            1,
            DB::table('finhero_student_points')
                ->where('student_id', $student->id)
                ->where('source_key', 'needs_wants_savings_match')
                ->count(),
            'replaying the same month should not pay out a second time'
        );
    }

    public function test_the_activity_still_counts_toward_engagement_participation_as_before(): void
    {
        $student = $this->studentInAClass();
        // handleActivity() (the pre-existing Participation/Engagement
        // award this request asked to keep, not replace) looks up the
        // student's own class's required/optional config in mba_position -
        // without a row there it no-ops, same as it always has.
        $admin = User::factory()->create(['role' => 1]);
        DB::table('mba_position')->insert([
            'user_id' => $admin->id, 'activity_key' => 'mba', 'required' => 1, 'optional' => 0,
            'sid' => $student->sid, 'cid' => $student->grade,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($student)->postJson(route('education.storemba'), [
            'budget' => ['needs' => []], 'mistakes' => 0,
        ]);

        // Unrelated to the new FinHero scoring - the pre-existing
        // Participation/Engagement award (handleActivity()) is additive,
        // not replaced.
        $this->assertDatabaseHas('participation_logs', [
            'student_id' => $student->id,
            'action_detail' => 'mba',
        ]);
    }

    /**
     * It used to always credit calendar event #1, whichever class the
     * student was in.
     */
    public function test_finishing_it_completes_the_students_own_class_event_once_a_month(): void
    {
        $student = $this->studentInAClass();
        $admin = User::factory()->create(['role' => 1]);
        DB::table('mba_position')->insert([
            'user_id' => $admin->id, 'activity_key' => 'mba', 'required' => 1, 'optional' => 0,
            'sid' => $student->sid, 'cid' => $student->grade,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $otherClassEvent = DB::table('calendar_events')->insertGetId([
            'title' => 'Needs, Wants & Savings Match', 'classId' => $student->grade + 1000, 'position' => 'required',
            'start' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $ownEvent = DB::table('calendar_events')->insertGetId([
            'title' => 'Needs, Wants & Savings Match', 'classId' => $student->grade, 'position' => 'required',
            'start' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($student)->postJson(route('education.storemba'), ['budget' => ['needs' => []], 'mistakes' => 0]);
        $this->actingAs($student)->postJson(route('education.storemba'), ['budget' => ['needs' => []], 'mistakes' => 0]);

        $this->assertSame(1, DB::table('activity_completions')->where('student_id', $student->id)->count());
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_id' => $ownEvent, 'activity_type' => 'mba']);
        $this->assertDatabaseMissing('activity_completions', ['activity_id' => $otherClassEvent]);
    }

    private function studentInAClass(): User
    {
        $class = \App\Models\SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
    }
}
