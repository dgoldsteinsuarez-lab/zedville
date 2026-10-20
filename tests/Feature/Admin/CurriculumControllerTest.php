<?php

namespace Tests\Feature\Admin;

use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\GradeLevel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-20: "for PISA, i should be able to add tasks for each
 * level, for students to practice and also practice exams like in the real
 * pisa exams" + "i dont see the levels in admin" (his own planning doc's
 * Activities/Exams/Graphics-per-Level structure). Confirmed via
 * AskUserQuestion: real scored quizzes, kept separate from the existing
 * per-Grade Education settings.
 */
class CurriculumControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_seeded_per_level_matching_the_planning_doc(): void
    {
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $intermediate = GradeLevel::where('name', 'Intermediate')->first();
        $advance = GradeLevel::where('name', 'Advance')->first();
        $advanceEconomist = GradeLevel::where('name', 'Advance Economist')->first();

        $names = fn ($level) => CurriculumCategory::where('grade_level_id', $level->id)->orderBy('sort_order')->pluck('name')->all();

        $this->assertSame(['Activities', 'Exams', 'Graphics', 'PISA Practice'], $names($rookie));
        $this->assertSame(['Activities', 'Exams', 'Graphics', 'Insurance', 'Emergency Costs', 'Credit', 'PISA Practice'], $names($intermediate));
        $this->assertSame(['Activities', 'Exams', 'Graphics', 'Competition', 'Inflation', 'PISA Practice'], $names($advance));
        $this->assertSame(['Activities', 'Exams', 'Graphics', 'Voting', 'PISA Practice'], $names($advanceEconomist));
    }

    public function test_every_level_has_exactly_one_pisa_practice_category(): void
    {
        foreach (GradeLevel::all() as $level) {
            $this->assertSame(
                1,
                CurriculumCategory::where('grade_level_id', $level->id)->where('is_pisa_practice', true)->count()
            );
        }
    }

    public function test_a_super_admin_can_view_curriculum_pages(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();
        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('name', 'Activities')->first();

        $this->actingAs($admin)->get('/admin/curriculum')->assertOk();
        $this->actingAs($admin)->get('/admin/curriculum/levels/' . $level->id . '/categories')->assertOk();
        $this->actingAs($admin)->get('/admin/curriculum/categories/' . $category->id . '/items')->assertOk();
    }

    public function test_a_school_admin_cannot_reach_curriculum_pages(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);

        $this->actingAs($schoolAdmin)->get('/admin/curriculum')->assertForbidden();
    }

    public function test_a_super_admin_can_add_a_category(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $level = GradeLevel::where('name', 'Rookie')->first();

        $response = $this->actingAs($admin)->post('/admin/curriculum/levels/' . $level->id . '/categories', [
            'name' => 'Savings',
            'sort_order' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('curriculum_categories', ['grade_level_id' => $level->id, 'name' => 'Savings']);
    }

    public function test_a_super_admin_can_add_and_edit_an_item(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();

        $response = $this->actingAs($admin)->post('/admin/curriculum/categories/' . $category->id . '/items', [
            'title' => 'Budgeting basics',
            'description' => 'Intro quiz',
            'type' => 'quiz',
            'sort_order' => 1,
            'is_active' => '1',
        ]);
        $response->assertRedirect();
        $item = CurriculumItem::where('category_id', $category->id)->where('title', 'Budgeting basics')->first();
        $this->assertNotNull($item);
        $this->assertTrue($item->is_active);

        $this->actingAs($admin)->put('/admin/curriculum/items/' . $item->id, [
            'title' => 'Budgeting basics (updated)',
            'description' => 'Intro quiz',
            'type' => 'quiz',
            'sort_order' => 1,
        ])->assertRedirect();

        $item->refresh();
        $this->assertSame('Budgeting basics (updated)', $item->title);
        $this->assertFalse($item->is_active, 'omitting is_active on update should turn it off, matching the unchecked checkbox');
    }

    public function test_a_super_admin_can_add_a_question_with_options(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Quiz', 'type' => 'quiz', 'sort_order' => 1]);

        $response = $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'question_text' => 'What is a budget?',
            'sort_order' => 1,
            'options' => ['A plan for money', 'A type of snack', '', ''],
            'correct' => 0,
        ]);

        $response->assertRedirect();
        $question = CurriculumQuestion::where('item_id', $item->id)->first();
        $this->assertNotNull($question);
        $this->assertSame(2, $question->options()->count());
        $this->assertTrue($question->options()->where('is_correct', true)->first()->option_text === 'A plan for money');
    }

    public function test_saving_a_question_with_no_correct_option_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Quiz', 'type' => 'quiz', 'sort_order' => 1]);

        $response = $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'question_text' => 'Bad question',
            'sort_order' => 1,
            'options' => ['Only one option', '', '', ''],
            'correct' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('curriculum_questions', ['question_text' => 'Bad question']);
    }

    public function test_a_super_admin_can_edit_a_question_and_its_options(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::where('is_pisa_practice', false)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Quiz', 'type' => 'quiz', 'sort_order' => 1]);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Original', 'sort_order' => 1]);
        $optionA = CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => true, 'sort_order' => 1]);
        $optionB = CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'B', 'is_correct' => false, 'sort_order' => 2]);

        $this->actingAs($admin)->post('/admin/curriculum/items/' . $item->id . '/questions/save', [
            'id' => $question->id,
            'question_text' => 'Edited',
            'sort_order' => 1,
            'options' => ['A', 'B'],
            'option_ids' => [0 => $optionA->id, 1 => $optionB->id],
            'correct' => 1,
        ])->assertRedirect();

        $question->refresh();
        $this->assertSame('Edited', $question->question_text);
        $this->assertFalse($optionA->fresh()->is_correct);
        $this->assertTrue($optionB->fresh()->is_correct);
        $this->assertSame(2, $question->options()->count(), 'editing should not create duplicate options');
    }

    public function test_a_super_admin_can_delete_a_category_item_and_question(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $category = CurriculumCategory::create(['grade_level_id' => GradeLevel::first()->id, 'name' => 'Temp', 'sort_order' => 99]);
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Temp item', 'type' => 'quiz', 'sort_order' => 1]);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Temp q', 'sort_order' => 1]);

        $this->actingAs($admin)->delete('/admin/curriculum/questions/' . $question->id)->assertRedirect();
        $this->assertDatabaseMissing('curriculum_questions', ['id' => $question->id]);

        $this->actingAs($admin)->delete('/admin/curriculum/items/' . $item->id)->assertRedirect();
        $this->assertDatabaseMissing('curriculum_items', ['id' => $item->id]);

        $this->actingAs($admin)->delete('/admin/curriculum/categories/' . $category->id)->assertRedirect();
        $this->assertDatabaseMissing('curriculum_categories', ['id' => $category->id]);
    }
}
