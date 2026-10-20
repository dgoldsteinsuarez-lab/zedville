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

    public function test_a_student_sees_their_own_level_categories_on_the_academy_index(): void
    {
        $student = $this->studentAtLevel('Intermediate');

        $response = $this->actingAs($student)->get(route('academy.index'));

        $response->assertOk();
        $response->assertSee('Insurance');
        $response->assertDontSee('Voting');
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

    public function test_the_pisa_shortcut_redirects_to_the_students_own_level_pisa_practice_category(): void
    {
        $student = $this->studentAtLevel('Intermediate');
        $expected = CurriculumCategory::where('grade_level_id', GradeLevel::where('name', 'Intermediate')->first()->id)
            ->where('is_pisa_practice', true)->first();

        $response = $this->actingAs($student)->get(route('academy.pisa'));

        $response->assertRedirect(route('academy.category', $expected));
    }
}
