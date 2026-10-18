<?php

namespace Tests\Feature\Finhero;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Task 2 rebuilt to Zedville_FinHero_Point_Values_v1.pdf's Approach System,
 * same shared server-side tier resolution Task 1 uses (FinheroApproachSystem
 * + savePoints()'s approach param) - this just confirms the page renders
 * (catches blade/JS-in-blade syntax errors the PHP-only linter can't see)
 * and that a question-level save still goes through the same shared,
 * already-tested backend path correctly for this task's activity_key.
 */
class Task2ApproachSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_2_page_renders(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get('/finhero/task-2');

        $response->assertOk();
        $response->assertSee('Finhero Badge Points: up to 24');
    }

    public function test_a_question_in_task_2_scores_through_the_shared_tiered_endpoint(): void
    {
        $student = User::factory()->create(['role' => 4]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => null, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_2', 'activity_name' => 'Budget Calculations',
            'max_points' => null, 'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_2_question_3',
            'approach' => 1,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'points_awarded' => 2]);
    }
}
