<?php

namespace Tests\Feature;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Finhero\CompoundInterest\EarlyStart;
use App\Services\Finhero\CompoundInterest\Snowball;
use App\Services\Finhero\QuizTasks;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Compound interest for the Intermediate, FinHero Tasks 6-8 (Daniel, 2026-11-08:
 * financial education, not the Money Factory): the snowball; who will have more,
 * where Bea saves quite a lot more from later and still ends with less; and the
 * rule of 72. A chart and questions to choose an answer for, graded on the server.
 */
class CompoundInterestActivitiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-10 10:00:00');
    }

    private function student(): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', 'Intermediate')->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
    }

    private function rightOptions(string $task, User $student): array
    {
        return array_map(fn ($q) => $q['options'][$q['answer']], QuizTasks::questions($task, (int) $student->id));
    }

    private function answers(string $task, User $student): array
    {
        return array_column(QuizTasks::questions($task, (int) $student->id), 'answer');
    }

    public function test_the_snowball_grows_with_compound_interest(): void
    {
        $this->assertSame(1480.24, Snowball::compound(1000, 10, 0.04));   // 1,000 × 1.04^10
        $this->assertSame(1400.0, Snowball::simple(1000, 10, 0.04));
        $this->assertSame(1790.85, Snowball::compound(1000, 10, 0.06));

        $student = $this->student();
        $right = $this->rightOptions('task_6', $student);
        $rows = QuizTasks::stage('task_6', (int) $student->id)['rows'];
        $this->assertSame($rows[2]['compound'], $right[0]);   // the chart's 10 years is the answer
        $this->assertSame(__('compound.snowball.a_why_interest'), $right[2]);
    }

    public function test_bea_puts_in_more_from_later_and_ana_still_has_more(): void
    {
        // at every Money Market rate a student can have: 4 % (Rookie), 5 % (Intermediate), 6 % (from its third month)
        foreach ([0.04, 0.05, 0.06] as $rate) {
            foreach (EarlyStart::MONTHLY as $ana) {
                $bea = (int) (round($ana * EarlyStart::BEA_TIMES / 10) * 10);
                $this->assertGreaterThan($ana * 12 * 30, $bea * 12 * 20, "Bea puts in more ($ana)");
                $this->assertGreaterThan(EarlyStart::saved($bea, 25, 45, $rate), EarlyStart::saved($ana, 15, 45, $rate), "Ana has more ($ana at $rate)");
            }
        }

        $right = $this->rightOptions('task_7', $this->student());
        $this->assertSame(['Bea', 'Ana'], [$right[0], $right[1]]);
        $this->assertSame(__('compound.early.a_lesson_early'), $right[3]);
    }

    public function test_the_rule_of_72(): void
    {
        $student = $this->student();
        foreach (QuizTasks::questions('task_8', (int) $student->id) as $i => $question) {
            $this->assertCount(3, array_unique($question['options']), "question $i has three different options");
        }
        // the student's own rate: in November the Intermediate's Money Market pays 6 % (from its third month)
        $this->assertSame(trans_choice('compound.rule72.a_years', 12, ['n' => 12]), $this->rightOptions('task_8', $student)[3]);   // 72 ÷ 6%
    }

    public function test_the_page_keeps_the_answers_on_the_server_and_grades_them(): void
    {
        $student = $this->student();
        $page = $this->actingAs($student)->get(route('task.page', ['task' => 'task-7']))->assertOk()
            ->assertSee(__('compound.early.title'))->assertSee(__('compound.early.chart'));
        $this->assertStringNotContainsString('"answer"', $page->getContent());

        $right = $this->answers('task_7', $student);
        $wrong = $right;
        $wrong[1] = ($wrong[1] + 1) % 3;
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-7'), ['answers' => $wrong])->assertOk()
            ->assertJson(['right' => [true, false, true, true], 'done' => false]);
        // not assigned to the class: right, but nothing recorded
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-7'), ['answers' => $right])->assertOk()->assertJson(['done' => true]);
        $this->assertDatabaseMissing('activity_completions', ['student_id' => $student->id]);
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-99'), ['answers' => [0]])->assertNotFound();
    }

    public function test_an_assigned_one_done_gives_the_task_and_its_points(): void
    {
        $student = $this->student();
        DB::table('finhero_activity_registry')->insert(['sid' => '1', 'cid' => $student->grade, 'type' => 'task', 'activity_key' => 'task_6',
            'activity_name' => 'The snowball: compound interest', 'max_points' => 3, 'position' => 'required', 'is_active' => 1,
            'start_date' => '2026-11-01', 'end_date' => '2026-11-30', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-6'), ['answers' => $this->answers('task_6', $student)])
            ->assertOk()->assertJson(['done' => true, 'message' => __('quiz.all_right')]);

        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => 'task_6']);
        $this->assertSame(1, DB::table('finhero_student_points')->where('student_id', $student->id)->where('source_key', 'task_6_question_1')->count());
        // again: no second points this month
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-6'), ['answers' => $this->answers('task_6', $student)])->assertOk();
        $this->assertSame(1, DB::table('finhero_student_points')->where('student_id', $student->id)->where('source_key', 'task_6_question_1')->count());
    }

    public function test_the_spanish_page(): void
    {
        $student = $this->student();
        $student->forceFill(['preferred_language' => 'es'])->save();

        $this->actingAs($student)->get(route('task.page', ['task' => 'task-6']))->assertOk()
            ->assertSee('La bola de nieve: el interés compuesto')->assertSee('Con interés compuesto')->assertSee('Comprobar');
    }
}
