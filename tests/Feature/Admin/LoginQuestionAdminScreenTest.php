<?php

namespace Tests\Feature\Admin;

use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Confirms the Login Question admin screen actually renders with the new
 * Level column/dropdown (daniel asked "I don't see the different level,
 * rookie, intermediate, etc. in the admin" - this is the fix).
 */
class LoginQuestionAdminScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_screen_renders_and_lists_all_levels(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.admin.login-question.login-question'));

        $response->assertOk();
        foreach (['Rookie', 'Intermediate', 'Advance', 'Advance Economist'] as $name) {
            $response->assertSee($name);
        }
    }

    public function test_admin_can_create_a_question_with_a_level(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $month = \App\Models\Month::where('name', 'Month 1')->first();

        $response = $this->actingAs($admin)->post(route('admin.admin.login-question.loginQuestionStore'), [
            'grade_level_id' => $rookie->id,
            'month_id' => $month->id,
            'question' => 'What is a budget?',
            'type' => 'true_false',
            'options' => ['True', 'False'],
            'correct' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('login_questions', [
            'question' => 'What is a budget?',
            'grade_level_id' => $rookie->id,
            'month_id' => $month->id,
        ]);
    }
}
