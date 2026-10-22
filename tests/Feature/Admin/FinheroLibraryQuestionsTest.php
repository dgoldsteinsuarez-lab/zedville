<?php

namespace Tests\Feature\Admin;

use App\Models\FinheroLibraryModule;
use App\Models\FinheroLibraryOption;
use App\Models\FinheroLibraryQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A Library module's questions are authored once and shared by every
 * school/grade/class the module is assigned to, so only a Super Admin can
 * edit them.
 */
class FinheroLibraryQuestionsTest extends TestCase
{
    use RefreshDatabase;

    private function module(): FinheroLibraryModule
    {
        return FinheroLibraryModule::create([
            'id' => 1, 'module_key' => 'task_1', 'title' => 'Introduction to Budget', 'icon' => '🐷', 'sort_order' => 2,
            'lesson' => [['title' => "What's a Budget?", 'lines' => ['A budget is a plan for your money.']]],
        ]);
    }

    public function test_a_super_admin_can_add_a_question_to_a_library_module(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $module = $this->module();

        $response = $this->actingAs($admin)->post("/admin/finhero/library-modules/{$module->id}/questions/save", [
            'question_text' => 'What is a budget?',
            'sort_order' => 1,
            'options' => ['A plan for your money', 'A type of bank account', '', ''],
            'correct' => 0,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('finhero_library_questions', ['module_id' => $module->id, 'question_text' => 'What is a budget?']);
        $this->assertDatabaseHas('finhero_library_options', ['option_text' => 'A plan for your money', 'is_correct' => 1]);
    }

    public function test_saving_a_question_with_no_correct_option_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $module = $this->module();

        $response = $this->actingAs($admin)->post("/admin/finhero/library-modules/{$module->id}/questions/save", [
            'question_text' => 'What is a budget?',
            'sort_order' => 1,
            'options' => ['', 'A type of bank account', '', ''],
            'correct' => 0,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('finhero_library_questions', ['module_id' => $module->id]);
    }

    public function test_the_questions_page_lists_existing_questions(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $module = $this->module();
        $question = FinheroLibraryQuestion::create(['module_id' => $module->id, 'question_text' => 'Existing question', 'sort_order' => 1]);
        FinheroLibraryOption::create(['question_id' => $question->id, 'option_text' => 'Yes', 'is_correct' => true, 'sort_order' => 1]);
        FinheroLibraryOption::create(['question_id' => $question->id, 'option_text' => 'No', 'is_correct' => false, 'sort_order' => 2]);

        $response = $this->actingAs($admin)->get("/admin/finhero/library-modules/{$module->id}/questions");

        $response->assertOk();
        $response->assertSee('Existing question');
        $response->assertSee('shared by every school');
    }

    public function test_a_super_admin_can_delete_a_library_question(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $module = $this->module();
        $question = FinheroLibraryQuestion::create(['module_id' => $module->id, 'question_text' => 'To delete', 'sort_order' => 1]);

        $response = $this->actingAs($admin)->delete("/admin/finhero/library-modules/{$module->id}/questions/{$question->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('finhero_library_questions', ['id' => $question->id]);
    }

    public function test_a_school_admin_cannot_edit_shared_library_questions(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);
        $module = $this->module();

        $this->actingAs($schoolAdmin)->get("/admin/finhero/library-modules/{$module->id}/questions")->assertForbidden();
        $this->actingAs($schoolAdmin)->post("/admin/finhero/library-modules/{$module->id}/questions/save", [
            'question_text' => 'Sneaky edit', 'sort_order' => 1, 'options' => ['A', 'B'], 'correct' => 0,
        ])->assertForbidden();

        $this->assertDatabaseMissing('finhero_library_questions', ['question_text' => 'Sneaky edit']);
    }
}
