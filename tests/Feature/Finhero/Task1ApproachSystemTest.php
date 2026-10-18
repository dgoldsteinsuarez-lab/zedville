<?php

namespace Tests\Feature\Finhero;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Covers two things found while rebuilding Task 1 to match
 * Zedville_FinHero_Point_Values_v1.pdf §2 "The Approach System":
 *
 * 1. A real, pre-existing bug: FinheroStudentController::savePoints()
 *    validated the per-question source_key ("task_1_question_1") directly
 *    against finhero_activity_registry.activity_key, which only ever has
 *    rows for the whole task ("task_1") - every save call would 422.
 *    finhero_student_points had zero rows in the live dev DB, consistent
 *    with this never having worked. Fixed by deriving the base
 *    activity_key the task belongs to.
 * 2. The new tiered scoring path: the server resolves points for a given
 *    attempt tier from FinheroApproachSystem, never trusting a raw point
 *    number from the client (same principle as awardPoints()).
 */
class Task1ApproachSystemTest extends TestCase
{
    use RefreshDatabase;

    private function registerTask1(): void
    {
        DB::table('finhero_activity_registry')->insert([
            'sid' => null, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_1', 'activity_name' => 'Classify the Salary',
            'max_points' => null, // inherits the seeded default (3)
            'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_per_question_source_key_is_validated_against_the_parent_task_not_itself(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $this->registerTask1();

        $response = $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_1_question_1',
            'approach' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'points_awarded' => 3]);
        $this->assertDatabaseHas('finhero_student_points', [
            'student_id' => $student->id,
            'source_key' => 'task_1_question_1',
            'points_earned' => 3,
        ]);
    }

    public function test_each_approach_tier_awards_the_docs_point_value(): void
    {
        $this->registerTask1();

        foreach ([0 => 3, 1 => 2, 2 => 1, 3 => 0] as $tier => $expectedPoints) {
            $student = User::factory()->create(['role' => 4]);

            $response = $this->actingAs($student)->postJson('/finhero/save-points', [
                'source_key' => 'task_1_question_1',
                'approach' => $tier,
            ]);

            $response->assertOk();
            $response->assertJson(['success' => true, 'points_awarded' => $expectedPoints]);
        }
    }

    public function test_tier_4_awards_zero_points_and_is_not_recorded(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $this->registerTask1();

        $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_1_question_1',
            'approach' => 3,
        ]);

        $this->assertDatabaseMissing('finhero_student_points', ['student_id' => $student->id]);
    }

    public function test_the_same_question_cannot_be_scored_twice_in_the_same_month(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $this->registerTask1();

        $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_1_question_1',
            'approach' => 0,
        ])->assertJson(['success' => true]);

        $response = $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_1_question_1',
            'approach' => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_inactive_task_rejects_the_save(): void
    {
        $student = User::factory()->create(['role' => 4]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => null, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_1', 'activity_name' => 'Classify the Salary',
            'max_points' => 3, 'position' => 'required', 'is_active' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_1_question_1',
            'approach' => 0,
        ]);

        $response->assertStatus(422);
    }
}
