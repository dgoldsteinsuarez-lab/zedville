<?php

namespace Tests\Feature\Academy;

use App\Models\CurriculumAttempt;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-20: "for PISA, i should be able to add tasks for each
 * level, for students to practice and also practice exams like in the real
 * pisa exams." Student-facing "Academy": a student only sees/attempts their
 * own Grade Level's curriculum, answers are graded server-side (never
 * exposing is_correct in the markup), retakes are unlimited but only the
 * first completion per item per month earns the participation point.
 */
class AcademyControllerTest extends TestCase
{
    use RefreshDatabase;

    private function studentAtLevel(string $levelName): User
    {
        $level = GradeLevel::where('name', $levelName)->first();
        $grade = Grade::create(['sid' => 1, 'name' => 'Test Grade', 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Test Class']);

        return User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
    }

    private function quizItemWithQuestions(string $levelName, int $questionCount = 2): CurriculumItem
    {
        $level = GradeLevel::where('name', $levelName)->first();
        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('name', 'Activities')->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Test Quiz', 'type' => 'quiz', 'sort_order' => 1]);

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => "Q{$i}", 'sort_order' => $i]);
            CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'Right', 'is_correct' => true, 'sort_order' => 1]);
            CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'Wrong', 'is_correct' => false, 'sort_order' => 2]);
        }

        return $item->fresh();
    }

    private function quizItemInCategory(string $levelName, string $categoryName): CurriculumItem
    {
        $level = GradeLevel::where('name', $levelName)->first();
        $category = CurriculumCategory::where('grade_level_id', $level->id)->where('name', $categoryName)->first();
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Item ' . uniqid(), 'type' => 'quiz', 'sort_order' => 1]);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Q', 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'Right', 'is_correct' => true, 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'Wrong', 'is_correct' => false, 'sort_order' => 2]);

        return $item->fresh();
    }

    private function submitAnswer(User $student, CurriculumItem $item, bool $correct = true)
    {
        $question = $item->questions()->with('options')->first();
        $optionId = $correct
            ? $question->options->firstWhere('is_correct', true)->id
            : $question->options->firstWhere('is_correct', false)->id;

        return $this->actingAs($student)->post(route('academy.item.submit', $item), [
            'answers' => [$question->id => $optionId],
        ]);
    }

    public function test_a_student_sees_their_own_level_categories_on_the_academy_index(): void
    {
        $student = $this->studentAtLevel('Intermediate');

        $response = $this->actingAs($student)->get(route('academy.index'));

        $response->assertOk();
        $response->assertSee('Insurance');
        $response->assertDontSee('Voting');
    }

    /**
     * Daniel, 2026-10-21, from a screenshot of the Academy page: "I see
     * Academy, but it should be Academy for Rookie level... Pisa should not
     * be there. Pisa should only be in PISA."
     */
    public function test_the_academy_index_shows_the_students_level_name_and_excludes_pisa_practice(): void
    {
        $student = $this->studentAtLevel('Intermediate');

        $response = $this->actingAs($student)->get(route('academy.index'));

        $response->assertOk();
        $response->assertSee('Intermediate Level');
        $response->assertDontSee('PISA Practice');
    }

    public function test_a_student_cannot_view_a_category_belonging_to_a_different_level(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $advanceCategory = CurriculumCategory::where('grade_level_id', GradeLevel::where('name', 'Advance')->first()->id)
            ->where('name', 'Competition')->first();

        $this->actingAs($student)->get(route('academy.category', $advanceCategory))->assertForbidden();
    }

    public function test_quiz_answers_are_never_exposed_in_the_item_page_markup(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $item = $this->quizItemWithQuestions('Rookie');

        $response = $this->actingAs($student)->get(route('academy.item', $item));

        $response->assertOk();
        // data-correct is the prototype's (quiz-multiple-question.blade.php)
        // actual leak mechanism - the real page must never use it. (Not
        // asserting against the bare substring "is_correct" - the shared
        // layouts/profile.blade.php already contains it legitimately, as a
        // JSON field name in the unrelated daily login-quiz widget's JS.)
        $response->assertDontSee('data-correct', false);
    }

    public function test_submitting_correct_and_incorrect_answers_scores_correctly_and_records_an_attempt(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $item = $this->quizItemWithQuestions('Rookie', 2);
        $questions = $item->questions()->with('options')->get();

        $answers = [];
        // Answer the first question correctly, the second incorrectly.
        $answers[$questions[0]->id] = $questions[0]->options->firstWhere('is_correct', true)->id;
        $answers[$questions[1]->id] = $questions[1]->options->firstWhere('is_correct', false)->id;

        $response = $this->actingAs($student)->post(route('academy.item.submit', $item), ['answers' => $answers]);

        $response->assertOk();
        $this->assertDatabaseHas('curriculum_attempts', [
            'item_id' => $item->id,
            'student_id' => $student->id,
            'score' => 1,
            'total_questions' => 2,
        ]);
        $this->assertSame(2, \App\Models\CurriculumAttemptAnswer::whereHas('attempt', fn ($q) => $q->where('item_id', $item->id))->count());
    }

    /**
     * Daniel, 2026-10-21: "I want to create an Exam at the end of the year
     * to show or have evidence of learning." A real exam only gets one
     * completed attempt - unlike every practice item, which stays
     * unlimited-retake (see the test right below this one).
     */
    public function test_a_real_exam_blocks_a_second_submission(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $item = $this->quizItemWithQuestions('Rookie', 1);
        $item->update(['is_real_exam' => true]);
        $question = $item->questions()->with('options')->first();
        $correctOptionId = $question->options->firstWhere('is_correct', true)->id;

        $this->actingAs($student)->post(route('academy.item.submit', $item), [
            'answers' => [$question->id => $correctOptionId],
        ])->assertOk();

        $response = $this->actingAs($student)->post(route('academy.item.submit', $item), [
            'answers' => [$question->id => $correctOptionId],
        ]);

        $response->assertForbidden();
        $this->assertSame(1, CurriculumAttempt::where('item_id', $item->id)->where('student_id', $student->id)->count());
    }

    public function test_opening_a_completed_real_exam_shows_the_existing_result_instead_of_the_quiz_form(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $item = $this->quizItemWithQuestions('Rookie', 1);
        $item->update(['is_real_exam' => true]);
        $question = $item->questions()->with('options')->first();
        $correctOptionId = $question->options->firstWhere('is_correct', true)->id;

        $this->actingAs($student)->post(route('academy.item.submit', $item), [
            'answers' => [$question->id => $correctOptionId],
        ]);

        $response = $this->actingAs($student)->get(route('academy.item', $item));

        $response->assertOk();
        $response->assertSee('1 / 1');
        $response->assertDontSee('startQuiz', false);
    }

    /**
     * Daniel, 2026-10-21, revising a flat "1 point/month" first draft: "one
     * point for each category... if he has done more than 4, he already
     * gets one point, and thats it." Per category, not per item or flat
     * per month - these exercise Activities and Graphics as the two
     * example categories, but the rule itself isn't hardcoded to either.
     */
    public function test_more_than_four_perfect_items_in_a_category_earns_one_finhero_point_for_it(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $category = CurriculumCategory::where('grade_level_id', GradeLevel::where('name', 'Rookie')->first()->id)
            ->where('name', 'Graphics')->first();

        for ($i = 0; $i < 5; $i++) {
            $this->submitAnswer($student, $this->quizItemInCategory('Rookie', 'Graphics'));
        }

        $this->assertDatabaseHas('finhero_student_points', [
            'student_id' => $student->id, 'source_type' => 'activity',
            'source_key' => 'academy_cat_' . $category->id, 'points_earned' => 1,
        ]);
    }

    public function test_exactly_four_perfect_items_does_not_yet_earn_the_point(): void
    {
        $student = $this->studentAtLevel('Rookie');

        for ($i = 0; $i < 4; $i++) {
            $this->submitAnswer($student, $this->quizItemInCategory('Rookie', 'Graphics'));
        }

        $this->assertDatabaseMissing('finhero_student_points', ['student_id' => $student->id]);
    }

    public function test_a_sixth_perfect_item_the_same_month_does_not_award_a_second_point(): void
    {
        $student = $this->studentAtLevel('Rookie');

        for ($i = 0; $i < 6; $i++) {
            $this->submitAnswer($student, $this->quizItemInCategory('Rookie', 'Graphics'));
        }

        $this->assertSame(1, DB::table('finhero_student_points')->where('student_id', $student->id)->count());
    }

    public function test_categories_earn_their_finhero_point_independently(): void
    {
        $student = $this->studentAtLevel('Rookie');

        for ($i = 0; $i < 5; $i++) {
            $this->submitAnswer($student, $this->quizItemInCategory('Rookie', 'Graphics'));
        }
        for ($i = 0; $i < 5; $i++) {
            $this->submitAnswer($student, $this->quizItemInCategory('Rookie', 'Activities'));
        }

        $this->assertSame(2, DB::table('finhero_student_points')->where('student_id', $student->id)->count());
    }

    public function test_an_imperfect_completion_does_not_count_toward_the_threshold(): void
    {
        $student = $this->studentAtLevel('Rookie');

        for ($i = 0; $i < 4; $i++) {
            $this->submitAnswer($student, $this->quizItemInCategory('Rookie', 'Graphics'));
        }
        // A 5th item, answered wrong - doesn't count as "done".
        $this->submitAnswer($student, $this->quizItemInCategory('Rookie', 'Graphics'), correct: false);

        $this->assertDatabaseMissing('finhero_student_points', ['student_id' => $student->id]);
    }

    public function test_retaking_the_same_item_does_not_inflate_the_distinct_item_count(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $item = $this->quizItemInCategory('Rookie', 'Graphics');

        // Same single item, completed perfectly 5 times - still only 1
        // distinct item of practice, not 5.
        for ($i = 0; $i < 5; $i++) {
            $this->submitAnswer($student, $item);
        }

        $this->assertDatabaseMissing('finhero_student_points', ['student_id' => $student->id]);
    }

    public function test_a_real_exam_never_counts_toward_the_voluntary_finhero_threshold(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $examsCategory = CurriculumCategory::where('grade_level_id', GradeLevel::where('name', 'Rookie')->first()->id)
            ->where('name', 'Exams')->first();

        for ($i = 0; $i < 5; $i++) {
            $item = CurriculumItem::create([
                'category_id' => $examsCategory->id, 'title' => 'Exam ' . uniqid(), 'type' => 'quiz',
                'is_real_exam' => true, 'sort_order' => 1,
            ]);
            $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'Q', 'sort_order' => 1]);
            CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'Right', 'is_correct' => true, 'sort_order' => 1]);
            $this->submitAnswer($student, $item->fresh());
        }

        $this->assertDatabaseMissing('finhero_student_points', ['student_id' => $student->id]);
    }

    public function test_the_first_completion_this_month_awards_a_participation_point_and_a_retake_does_not_double_award(): void
    {
        $student = $this->studentAtLevel('Rookie');
        $item = $this->quizItemWithQuestions('Rookie', 1);
        $question = $item->questions()->with('options')->first();
        $correctOptionId = $question->options->firstWhere('is_correct', true)->id;

        $this->actingAs($student)->post(route('academy.item.submit', $item), [
            'answers' => [$question->id => $correctOptionId],
        ]);
        $this->assertDatabaseHas('participation_logs', [
            'student_id' => $student->id,
            'action_detail' => 'curriculum_item_complete',
            'action_ref' => (string) $item->id,
        ]);
        $firstCount = \Illuminate\Support\Facades\DB::table('participation_logs')
            ->where('student_id', $student->id)->where('action_detail', 'curriculum_item_complete')->count();

        // Retake - should record a second attempt but not a second award.
        $this->actingAs($student)->post(route('academy.item.submit', $item), [
            'answers' => [$question->id => $correctOptionId],
        ]);

        $this->assertSame(2, CurriculumAttempt::where('item_id', $item->id)->where('student_id', $student->id)->count());
        $secondCount = \Illuminate\Support\Facades\DB::table('participation_logs')
            ->where('student_id', $student->id)->where('action_detail', 'curriculum_item_complete')->count();
        $this->assertSame($firstCount, $secondCount);
    }
}
