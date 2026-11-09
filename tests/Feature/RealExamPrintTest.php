<?php

namespace Tests\Feature;

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
 * Daniel, 2026-10-31: "is there an option to put the exam online? Also, the
 * tutor should be able to print the exam in case they don't want to do it
 * online" - the results page lists the real exams for the viewer's classes,
 * and each prints for students or as an answer key, in any language.
 */
class RealExamPrintTest extends TestCase
{
    use RefreshDatabase;

    private function level(string $name): GradeLevel
    {
        return GradeLevel::where('name', $name)->first() ?? GradeLevel::create(['name' => $name, 'sort_order' => 9]);
    }

    private function exam(GradeLevel $level, string $title): CurriculumItem
    {
        $category = CurriculumCategory::firstOrCreate(['grade_level_id' => $level->id, 'name' => 'Exams'], ['sort_order' => 2]);
        $item = CurriculumItem::create(['category_id' => $category->id, 'title' => $title, 'type' => 'quiz', 'is_real_exam' => true,
            'is_active' => true, 'sort_order' => 1, 'start_date' => '2026-11-09', 'end_date' => '2026-11-13']);
        $question = CurriculumQuestion::create(['item_id' => $item->id, 'question_text' => 'What is a budget?', 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'A plan for your money', 'is_correct' => true, 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'A kind of bank card', 'is_correct' => false, 'sort_order' => 2]);

        return $item;
    }

    public function test_a_tutor_sees_their_classes_exams_and_prints_them(): void
    {
        $rookie = $this->level('Rookie');
        $mine = $this->exam($rookie, 'End of year exam');
        $other = $this->exam($this->level('Advance ' . uniqid()), 'Advanced final');

        $grade = Grade::create(['sid' => 1, 'name' => 'Grade 9', 'grade_level_id' => $rookie->id]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => '9A']);
        $tutor = User::factory()->create(['role' => 3, 'sid' => 1]);
        DB::table('class_tutor')->insert(['class_id' => $class->id, 'user_id' => $tutor->id]);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);
        CurriculumAttempt::create(['item_id' => $mine->id, 'student_id' => $student->id, 'score' => 1, 'total_questions' => 1, 'completed_at' => now()]);

        $this->actingAs($tutor)->get(route('admin.exam-results.index'))->assertOk()
            ->assertSeeInOrder(['Real exams', 'End of year exam', 'Available', '09 Nov 2026 – 13 Nov 2026', 'Print exam', 'Answer key'])
            ->assertDontSee('Advanced final');

        // the students' copy: name/class lines, boxes to tick, no answers
        $this->actingAs($tutor)->get(route('admin.exam-results.print', $mine))->assertOk()
            ->assertSeeInOrder(['End of year exam', 'Name', 'Class', 'Points', 'Mark one answer for each question.',
                'Question 1', 'What is a budget?', 'A plan for your money', 'A kind of bank card'])
            ->assertDontSee('class="opt right"', false)->assertDontSee('Answer key</span>', false);

        // the answer key marks the right one
        $this->actingAs($tutor)->get(route('admin.exam-results.print', ['item' => $mine, 'key' => 1]))->assertOk()
            ->assertSeeInOrder(['Answer key', 'class="opt right"', 'A plan for your money', 'Answers', '<li>A</li>'], false);

        // the paper's words in the language picked
        $this->actingAs($tutor)->get(route('admin.exam-results.print', ['item' => $mine, 'lang' => 'es']))->assertOk()
            ->assertSee('<html lang="es">', false)
            ->assertSeeInOrder(['Nombre', 'Clase', 'Fecha', 'Puntos', 'Marca una sola respuesta en cada pregunta.', 'Pregunta 1']);

        // another level's exam isn't theirs, and students can't print
        $this->actingAs($tutor)->get(route('admin.exam-results.print', $other))->assertNotFound();
        $this->actingAs($student)->get(route('admin.exam-results.print', $mine))->assertForbidden();
    }
}
