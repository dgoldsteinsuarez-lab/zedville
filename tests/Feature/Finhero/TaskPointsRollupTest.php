<?php

namespace Tests\Feature\Finhero;

use App\Models\User;
use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Real, pre-existing gap found while rebuilding Tasks 1-4: savePoints()
 * (the Approach System path) stores earned points with source_type='task'
 * and a per-question source_key ("task_1_question_1"), but
 * FinheroBadgeCalculatorService::getActivityPoints() only ever summed
 * source_type='activity' rows where source_key equals the activity_key
 * directly. Every point a student earned from Tasks 1-5 was saved
 * correctly but never counted toward their monthly FinHero badge at all.
 * Fixed by rolling per-question 'task' rows up to their parent task's
 * activity_key alongside the existing 'activity' rows.
 */
class TaskPointsRollupTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_question_points_roll_up_into_the_monthly_calculation(): void
    {
        $student = User::factory()->create(['role' => 4]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => null, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_1', 'activity_name' => 'Classify the Salary',
            'max_points' => null, 'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Earn Task 1's single question at tier 0 (3 pts), same as a real
        // save through /finhero/save-points would record.
        $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_1_question_1',
            'approach' => 0,
        ])->assertJson(['success' => true, 'points_awarded' => 3]);

        $calculator = app(FinheroBadgeCalculatorService::class);
        $method = (new \ReflectionClass($calculator))->getMethod('getActivityPoints');
        $method->setAccessible(true);

        $activityPts = $method->invoke($calculator, $student->id, now()->month, now()->year);

        $this->assertSame(3, $activityPts);
    }

    public function test_multiple_questions_in_one_task_roll_up_and_cap_at_the_tasks_max(): void
    {
        $student = User::factory()->create(['role' => 4]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => null, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_2', 'activity_name' => 'Budget Calculations',
            'max_points' => 5, // artificially low cap to prove the min() clamp works
            'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_2_question_1', 'approach' => 0, // 3 pts
        ])->assertJson(['success' => true]);
        $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_2_question_2', 'approach' => 0, // 3 pts, total would be 6
        ])->assertJson(['success' => true]);

        $calculator = app(FinheroBadgeCalculatorService::class);
        $method = (new \ReflectionClass($calculator))->getMethod('getActivityPoints');
        $method->setAccessible(true);

        $activityPts = $method->invoke($calculator, $student->id, now()->month, now()->year);

        $this->assertSame(5, $activityPts); // capped at the task's max_points, not 6
    }
}
