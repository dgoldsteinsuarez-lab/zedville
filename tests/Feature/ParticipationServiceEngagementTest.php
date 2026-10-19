<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ParticipationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: "how are you doing with the engagement badge, is
 * it all good?" - ParticipationService::award() (used by every real
 * call site - transfer, donation, poster_room_enter, emergency_fund_
 * open, money_market_open) stored the specific action name directly in
 * `action_type` (e.g. 'donation'), but ProfileController::
 * calculateMonthlyEngagement() only ever counts a row toward the
 * Engagement badge's "optional activities" requirement when
 * `action_type` literally equals 'optional'. Every point ever logged
 * through award() had never once counted.
 */
class ParticipationServiceEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_stores_the_optional_bucket_participation_logs_expects(): void
    {
        $student = User::factory()->create(['role' => 4]);

        app(ParticipationService::class)->award($student->id, 'poster_room_enter');

        $this->assertDatabaseHas('participation_logs', [
            'student_id' => $student->id,
            'action_type' => 'optional',
            'action_detail' => 'poster_room_enter',
        ]);
    }

    public function test_the_sub_identifier_still_lands_somewhere_without_colliding(): void
    {
        $student = User::factory()->create(['role' => 4]);

        app(ParticipationService::class)->award($student->id, 'donation', "Children's Orphanage");

        $this->assertDatabaseHas('participation_logs', [
            'student_id' => $student->id,
            'action_type' => 'optional',
            'action_detail' => 'donation',
            'action_ref' => "Children's Orphanage",
        ]);
    }

    public function test_dedup_still_works_after_the_action_type_fix(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $service = app(ParticipationService::class);

        $first = $service->award($student->id, 'poster_room_enter');
        $second = $service->award($student->id, 'poster_room_enter');

        $this->assertTrue($first);
        $this->assertFalse($second); // once per day
        $this->assertSame(1, \DB::table('participation_logs')->where('student_id', $student->id)->count());
    }

    public function test_awarded_points_now_actually_land_in_the_current_month(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $service = app(ParticipationService::class);

        foreach (['poster_room_enter', 'transfer', 'money_market_open', 'emergency_fund_open', 'voting'] as $action) {
            $service->award($student->id, $action);
        }

        $now = now();
        $count = \DB::table('participation_logs')
            ->where('student_id', $student->id)
            ->where('month', $now->month - 1) // calculateMonthlyEngagement's 0-based convention
            ->where('year', $now->year)
            ->where('action_type', 'optional')
            ->count();

        // Confirms the month-numbering and action_type fixes line up with
        // what calculateMonthlyEngagement actually reads - full badge
        // award also needs a completed Required calendar activity that
        // month (separate from Participation), not exercised here.
        $this->assertSame(5, $count);
    }
}
