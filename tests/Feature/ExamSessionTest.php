<?php

namespace Tests\Feature;

use App\Models\CurriculumAttempt;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\ExamSession;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-31: "quiero que el tutor pueda abrir o poner el examen para
 * el día concreto del examen ... el tutor les da una contraseña para que
 * puedan abrir el examen. El examen online lo corrige el sistema y da
 * automáticamente el resultado al tutor y al alumno cuando el tutor lo libere."
 */
class ExamSessionTest extends TestCase
{
    use RefreshDatabase;

    private CurriculumItem $exam;
    private CurriculumQuestionOption $right;
    private CurriculumQuestion $question;

    protected function setUp(): void
    {
        parent::setUp();
        $level = GradeLevel::where('name', 'Rookie')->first();
        $category = CurriculumCategory::firstOrCreate(['grade_level_id' => $level->id, 'name' => 'Exams'], ['sort_order' => 2]);
        $this->exam = CurriculumItem::create(['category_id' => $category->id, 'title' => 'Year Exam', 'type' => 'quiz',
            'is_real_exam' => true, 'is_active' => true, 'sort_order' => 1]);
        $this->question = CurriculumQuestion::create(['item_id' => $this->exam->id, 'question_text' => 'What is a budget?', 'sort_order' => 1]);
        $this->right = CurriculumQuestionOption::create(['question_id' => $this->question->id, 'option_text' => 'A plan for your money', 'is_correct' => true, 'sort_order' => 1]);
        CurriculumQuestionOption::create(['question_id' => $this->question->id, 'option_text' => 'A bank card', 'is_correct' => false, 'sort_order' => 2]);
    }

    private function classWithTutor(): array
    {
        $grade = Grade::create(['sid' => 1, 'name' => 'Grade 9', 'grade_level_id' => GradeLevel::where('name', 'Rookie')->value('id')]);
        $class = SchoolClass::create(['sid' => 1, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $tutor = User::factory()->create(['role' => 3, 'sid' => 1]);
        DB::table('class_tutor')->insert(['class_id' => $class->id, 'user_id' => $tutor->id]);
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => $class->id]);

        return [$class, $tutor, $student];
    }

    private function answer(User $student)
    {
        return $this->actingAs($student)->post(route('academy.item.submit', $this->exam), ['answers' => [$this->question->id => $this->right->id]]);
    }

    public function test_the_exam_is_closed_until_the_tutor_opens_it_for_the_class_today(): void
    {
        [$class, $tutor, $student] = $this->classWithTutor();

        $this->actingAs($student)->get(route('academy.item', $this->exam))->assertOk()
            ->assertSee('This exam is not open')->assertSee('Your tutor opens it on the day of the exam')
            ->assertDontSee('startQuiz', false)->assertDontSee('name="password"', false);
        $this->answer($student)->assertRedirect(route('academy.item', $this->exam));
        $this->assertSame(0, CurriculumAttempt::count());

        // planned for another day: still closed, with the day
        $this->actingAs($tutor)->post(route('admin.exam-results.open', [$this->exam, $class]), ['exam_date' => today()->addDays(3)->toDateString(), 'password' => 'abc234'])
            ->assertSessionHas('success');
        $this->actingAs($student)->get(route('academy.item', $this->exam))->assertOk()
            ->assertSee('Your tutor has planned it for')->assertDontSee('name="password"', false);
        $this->assertSame('ABC234', ExamSession::first()->password);
        $this->assertDatabaseHas('calendar_events', ['classId' => $class->id, 'title' => 'exam-Year Exam', 'start' => today()->addDays(3)->startOfDay()]);
    }

    public function test_on_the_day_the_password_opens_it_the_tutor_sees_the_mark_and_releases_it(): void
    {
        [$class, $tutor, $student] = $this->classWithTutor();
        [, , $otherClassStudent] = $this->classWithTutor();

        $this->actingAs($tutor)->get(route('admin.exam-results.classes', $this->exam))->assertOk()
            ->assertSee($class->name)->assertSee('Not opened')->assertDontSee($otherClassStudent->classname ?? 'never-there');
        $this->actingAs($tutor)->post(route('admin.exam-results.open', [$this->exam, $class]), ['exam_date' => today()->toDateString(), 'password' => 'ZED42K'])
            ->assertSessionHas('success', 'Year Exam is open for ' . $class->name . ' on ' . today()->format('d M Y') . ' with the password ZED42K.');

        // the other class isn't open; this one asks for the password
        $this->actingAs($otherClassStudent)->get(route('academy.item', $this->exam))->assertSee('This exam is not open');
        $this->actingAs($student)->get(route('academy.item', $this->exam))->assertOk()
            ->assertSee('Exam password')->assertSee('name="password"', false)->assertDontSee('startQuiz', false);

        $this->actingAs($student)->from(route('academy.item', $this->exam))->post(route('academy.item.unlock', $this->exam), ['password' => 'WRONG1'])
            ->assertSessionHasErrors(['password' => 'That password is not right. Ask your tutor.']);
        $this->actingAs($student)->post(route('academy.item.unlock', $this->exam), ['password' => ' zed42k '])
            ->assertRedirect(route('academy.item', $this->exam));
        $this->actingAs($student)->get(route('academy.item', $this->exam))->assertOk()->assertSee('startQuiz', false);

        // marked by the system: the tutor sees it, the student doesn't yet
        $this->answer($student)->assertOk()->assertSee('Exam submitted')->assertDontSee('1 / 1');
        $this->actingAs($tutor)->get(route('admin.exam-results.index'))->assertSee('1 / 1');
        $this->actingAs($tutor)->get(route('admin.exam-results.classes', $this->exam))->assertSee('Open today')->assertSee('1 / 1')->assertSee('Release results');

        // released: the student sees the mark and the right answers
        $this->actingAs($tutor)->post(route('admin.exam-results.release', ExamSession::first()))->assertSessionHas('success');
        $this->actingAs($student)->get(route('academy.item', $this->exam))->assertOk()
            ->assertSee('Your result')->assertSee('1 / 1')->assertSee('Check the right answers');
        $this->actingAs($student)->get(route('academy.category', $this->exam->category))->assertSee('Your result: 1/1');

        // in Spanish too
        $student->update(['preferred_language' => 'es']);
        $this->actingAs($student)->get(route('academy.item', $this->exam))->assertSee('Tu resultado');
    }

    public function test_a_closed_exam_takes_no_answers_and_tutors_only_run_their_own_classes(): void
    {
        [$class, $tutor, $student] = $this->classWithTutor();
        [$otherClass] = $this->classWithTutor();

        $this->actingAs($tutor)->post(route('admin.exam-results.open', [$this->exam, $otherClass]), ['exam_date' => today()->toDateString(), 'password' => 'ABCD'])
            ->assertNotFound();
        $this->actingAs($tutor)->post(route('admin.exam-results.open', [$this->exam, $class]), ['exam_date' => today()->subDay()->toDateString(), 'password' => 'ABCD'])
            ->assertSessionHasErrors('exam_date');

        $this->actingAs($tutor)->post(route('admin.exam-results.open', [$this->exam, $class]), ['exam_date' => today()->toDateString(), 'password' => 'ABCD']);
        $this->actingAs($student)->post(route('academy.item.unlock', $this->exam), ['password' => 'abcd']);
        $this->actingAs($tutor)->post(route('admin.exam-results.close', ExamSession::first()));

        $this->answer($student)->assertRedirect(route('academy.item', $this->exam));
        $this->assertSame(0, CurriculumAttempt::count());
        $this->actingAs($student)->get(route('academy.item', $this->exam))->assertSee('This exam is not open');
    }
}
