<?php

namespace Tests\Feature;

use App\Models\CurriculumAttempt;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\PisaProficiencyLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\PisaPracticeContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-22: PISA practice with "click and check the right answers"
 * afterwards; the real exam's result is only for the tutor and school admin.
 */
class PisaExamsTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\OpensRealExams;

    private function classIn(int $sid): SchoolClass
    {
        $level = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => $sid, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);

        return SchoolClass::create(['sid' => $sid, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
    }

    private function student(SchoolClass $class): User
    {
        return User::factory()->create(['role' => 4, 'sid' => $class->sid, 'grade' => $class->id]);
    }

    /** One-question PISA item; returns [item, right option, wrong option]. */
    private function pisaItem(bool $realExam = false, bool $active = true): array
    {
        $category = CurriculumCategory::where('is_pisa_practice', true)->first()
            ?? CurriculumCategory::create(['grade_level_id' => null, 'name' => 'PISA Practice', 'is_pisa_practice' => true, 'sort_order' => 99]);

        $item = CurriculumItem::create([
            'category_id' => $category->id, 'title' => $realExam ? 'Final PISA Exam' : 'Bus Fares Practice', 'type' => 'quiz',
            'is_real_exam' => $realExam, 'is_active' => $active, 'sort_order' => 1,
            'pisa_proficiency_level_id' => $realExam ? null : PisaProficiencyLevel::where('name', 'Level 1')->value('id'),
        ]);
        $question = CurriculumQuestion::create([
            'item_id' => $item->id, 'question_text' => 'Which ticket is cheapest for 40 rides?',
            'explanation' => 'The monthly pass is only 48 zeds.', 'sort_order' => 1,
        ]);
        $right = CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'Monthly pass', 'is_correct' => true, 'sort_order' => 1]);
        $wrong = CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'Single rides', 'is_correct' => false, 'sort_order' => 2]);

        return [$item, $right, $wrong, $question];
    }

    public function test_after_a_practice_the_student_can_check_the_right_answers(): void
    {
        [$item, , $wrong, $question] = $this->pisaItem();

        $this->actingAs($this->student($this->classIn(1)))
            ->post(route('academy.item.submit', $item), ['answers' => [$question->id => $wrong->id]])
            ->assertOk()
            ->assertSee('0 / 1')
            ->assertSee('Check the right answers')
            ->assertSee('Right answer')
            ->assertSee('Monthly pass')
            ->assertSee('The monthly pass is only 48 zeds.')
            ->assertSee('not affiliated with', false);
    }

    public function test_a_student_does_not_see_the_score_of_a_real_exam(): void
    {
        [$item, $right, , $question] = $this->pisaItem(true);
        $student = $this->student($this->classIn(1));
        $this->openExamFor($student, $item);

        $this->actingAs($student)
            ->post(route('academy.item.submit', $item), ['answers' => [$question->id => $right->id]])
            ->assertOk()
            ->assertSee('Exam submitted')
            ->assertDontSee('1 / 1')
            ->assertDontSee('Check the right answers')
            ->assertDontSee('Monthly pass');

        // Opening it again shows the same confirmation, never the score.
        $this->actingAs($student)->get(route('academy.item', $item))
            ->assertOk()->assertSee('Exam submitted')->assertDontSee('1 / 1');

        $this->actingAs($student)->get(route('pisa.index'))
            ->assertOk()->assertSee('Final PISA Exam')->assertDontSee('1/1');
    }

    public function test_a_switched_off_real_exam_cannot_be_opened(): void
    {
        [$item] = $this->pisaItem(true, false);

        $this->actingAs($this->student($this->classIn(1)))->get(route('academy.item', $item))->assertNotFound();
    }

    public function test_tutors_and_school_admins_see_real_exam_results_only_for_their_own_students(): void
    {
        [$item] = $this->pisaItem(true);
        $myClass = $this->classIn(1);
        $otherClassSameSchool = $this->classIn(1);
        $otherSchoolClass = $this->classIn(2);

        $mine = $this->student($myClass);
        $sameSchool = $this->student($otherClassSameSchool);
        $otherSchool = $this->student($otherSchoolClass);
        foreach ([[$mine, 7], [$sameSchool, 5], [$otherSchool, 3]] as [$student, $score]) {
            CurriculumAttempt::create(['item_id' => $item->id, 'student_id' => $student->id, 'score' => $score, 'total_questions' => 10, 'completed_at' => now()]);
        }

        $tutor = User::factory()->create(['role' => 3, 'sid' => 1]);
        DB::table('class_tutor')->insert(['class_id' => $myClass->id, 'user_id' => $tutor->id]);
        $schoolAdmin = User::factory()->create(['role' => 2, 'sid' => 1]);

        $this->actingAs($tutor)->get(route('admin.exam-results.index'))
            ->assertOk()->assertSee($mine->name)->assertSee('7 / 10')
            ->assertDontSee($sameSchool->name)->assertDontSee($otherSchool->name);

        $this->actingAs($schoolAdmin)->get(route('admin.exam-results.index'))
            ->assertOk()->assertSee($mine->name)->assertSee($sameSchool->name)
            ->assertDontSee($otherSchool->name);

        $otherAttempt = CurriculumAttempt::where('student_id', $otherSchool->id)->first();
        $this->actingAs($schoolAdmin)->get(route('admin.exam-results.show', $otherAttempt))->assertNotFound();

        $this->actingAs($mine)->get(route('admin.exam-results.index'))->assertForbidden();
    }

    public function test_a_real_exam_score_never_counts_on_the_leaderboard(): void
    {
        [$exam] = $this->pisaItem(true);
        $student = $this->student($this->classIn(1));
        CurriculumAttempt::create(['item_id' => $exam->id, 'student_id' => $student->id, 'score' => 12, 'total_questions' => 12, 'completed_at' => now()]);

        $ranking = app(\App\Services\PisaLeaderboardService::class)->rankingFor($exam->category_id, 1, now()->month, now()->year);

        $this->assertCount(0, $ranking);
    }

    public function test_the_seeded_practice_has_at_least_two_activities_for_every_pisa_level_and_a_draft_real_exam(): void
    {
        $this->seed(PisaPracticeContentSeeder::class);
        $this->seed(PisaPracticeContentSeeder::class); // safe to run twice

        $category = CurriculumCategory::where('is_pisa_practice', true)->first();
        foreach (PisaProficiencyLevel::all() as $level) {
            $this->assertGreaterThanOrEqual(2, CurriculumItem::where('category_id', $category->id)->where('pisa_proficiency_level_id', $level->id)->count(), $level->name);
        }

        $exam = CurriculumItem::where('category_id', $category->id)->where('is_real_exam', true)->firstOrFail();
        $this->assertFalse($exam->is_active);
        $this->assertSame(12, $exam->questions()->count());
        $this->assertSame(1, CurriculumItem::where('is_real_exam', true)->count());

        // Every question has exactly one right answer and practice questions explain it.
        foreach (CurriculumQuestion::whereIn('item_id', CurriculumItem::where('category_id', $category->id)->pluck('id'))->get() as $question) {
            $this->assertSame(1, $question->options()->where('is_correct', true)->count(), $question->question_text);
            if (!$question->item->is_real_exam) {
                $this->assertNotEmpty($question->explanation, $question->question_text);
            }
        }
    }
}
