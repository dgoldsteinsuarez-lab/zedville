<?php

namespace Tests\Feature\Finhero;

use App\Models\FinheroActivityDefault;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Task 3 rebuilt to the Approach System, same shared backend path Tasks 1
 * and 2 use. Task 3 only has 10 of the doc's 11 questions built (the 11th
 * was never added to buildQuestions() - not invented here, see
 * ProjectArchitecture.md §14.4), so its real achievable max is 30 (10 x 3),
 * not the doc's 33 - this is specifically asserted so a future question
 * addition is a deliberate, visible change to this number.
 */
class Task3ApproachSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_3_page_renders(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get('/finhero/task-3');

        $response->assertOk();
        $response->assertSee('Finhero Badge Points: up to 30');
    }

    public function test_default_reflects_the_ten_built_questions_not_the_docs_eleven(): void
    {
        $default = FinheroActivityDefault::where('activity_key', 'task_3')->first();

        $this->assertSame(30, $default->max_points);
    }

    public function test_a_question_in_task_3_scores_through_the_shared_tiered_endpoint(): void
    {
        $student = User::factory()->create(['role' => 4]);

        DB::table('finhero_activity_registry')->insert([
            'sid' => null, 'cid' => null, 'type' => 'task',
            'activity_key' => 'task_3', 'activity_name' => '50/30/20 Rule',
            'max_points' => null, 'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($student)->postJson('/finhero/save-points', [
            'source_key' => 'task_3_question_7',
            'approach' => 2,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'points_awarded' => 1]);
    }
}
