<?php

namespace Tests\Feature\Finhero;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Task 4 rebuilt to the Approach System, same shared backend path Tasks
 * 1-3 use. Mixes "number" and "choice" question types - choice questions
 * have no extra step content to redact (their hint already stops short of
 * the answer), so their "fuller hint" tier just keeps showing the hint
 * rather than ever revealing which option is correct.
 */
class Task4ApproachSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_4_page_renders(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get('/finhero/task-4');

        $response->assertOk();
        $response->assertSee('Finhero Badge Points: up to 24');
    }

    public function test_a_question_in_task_4_scores_through_the_shared_tiered_endpoint(): void
    {
        $student = User::factory()->create(['role' => 4]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => null, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_4', 'activity_name' => 'Budget Reality Check',
            'max_points' => null, 'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_4_question_2',
            'approach' => 0,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'points_awarded' => 3]);
    }
}
