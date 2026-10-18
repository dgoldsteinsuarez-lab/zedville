<?php

namespace Tests\Feature\Admin;

use App\Models\CityProposal;
use App\Models\Grade;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: City Proposals is a minor feature (students voting on
 * a city name/quote, Tutors agreeing on it) that doesn't deserve its own
 * top-level admin nav item - moved it into the Grade's own "General
 * Information" profile page instead, scoped to that one Grade. The
 * standalone page/route still exists but is no longer linked from the nav.
 */
class GradeProfileCityProposalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_pending_proposals_for_this_grade_only(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8A']);
        $otherGrade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 9A']);
        $student = User::factory()->create(['role' => 4]);

        CityProposal::create([
            'sid' => $school->id,
            'grade_id' => $grade->id,
            'type' => 'name',
            'value' => 'New City Name',
            'proposed_by' => $student->id,
            'status' => 'pending',
        ]);
        CityProposal::create([
            'sid' => $school->id,
            'grade_id' => $otherGrade->id,
            'type' => 'name',
            'value' => 'Other Grade Proposal',
            'proposed_by' => $student->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.grades.general-information', $grade->id));

        $response->assertOk();
        $response->assertSee('New City Name');
        $response->assertDontSee('Other Grade Proposal');
    }

    public function test_approving_from_the_grade_profile_uses_the_existing_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $school = SchoolDomain::create([
            'school_name' => 'Test School',
            'school_code' => 'TS-' . uniqid(),
            'school_domain' => uniqid() . '.test',
        ]);
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8A']);
        $student = User::factory()->create(['role' => 4]);
        $proposal = CityProposal::create([
            'sid' => $school->id,
            'grade_id' => $grade->id,
            'type' => 'name',
            'value' => 'New City Name',
            'proposed_by' => $student->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.city-proposals.approve', $proposal->id));

        $response->assertRedirect();
        $this->assertSame('approved', $proposal->fresh()->status);
    }

    public function test_city_proposals_nav_link_is_gone_from_the_sidebar(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee('City Proposals');
    }
}
