<?php

namespace Tests\Feature\Academy;

use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\LoginOption;
use App\Models\LoginQuestion;
use App\Models\Month;
use App\Models\SchoolClass;
use App\Models\SchoolDomain;
use App\Models\SchoolMonthSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "add this question in academic and only open the new
 * question for each month, but adding the month, for example, in month
 * two, he can decide to answer both month questions, at the end, a mix of
 * all questions."
 */
class MonthlyQuestionsTest extends TestCase
{
    use RefreshDatabase;

    /** A Rookie student whose school is currently in progression month $reached. */
    private function rookieStudent(int $reached): User
    {
        $school = SchoolDomain::create(['school_name' => 'Test School', 'school_code' => 'TS-' . uniqid(), 'school_domain' => uniqid() . '.test']);
        $rookie = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => (string) $school->id, 'name' => 'Grade 8', 'grade_level_id' => $rookie->id]);
        $class = SchoolClass::create(['sid' => (string) $school->id, 'name' => '8A', 'grade_id' => $grade->id]);

        $startMonth = ((now()->month - ($reached - 1) - 1 + 12) % 12) + 1;
        SchoolMonthSetting::updateOrCreate(['school_id' => $school->id], ['start_month' => $startMonth]);

        return User::factory()->create(['role' => 4, 'sid' => (string) $school->id, 'grade' => $class->id]);
    }

    private function question(string $levelName, int $month, string $text): array
    {
        $question = LoginQuestion::create([
            'grade_level_id' => GradeLevel::where('name', $levelName)->value('id'),
            'month_id' => Month::where('name', "Month {$month}")->value('id'),
            'question' => $text,
            'type' => 'true_false',
        ]);
        $right = LoginOption::create(['question_id' => $question->id, 'option_text' => 'True', 'is_correct' => true]);
        $wrong = LoginOption::create(['question_id' => $question->id, 'option_text' => 'False', 'is_correct' => false]);

        return [$question, $right, $wrong];
    }

    public function test_reached_months_are_open_and_later_months_are_locked(): void
    {
        $student = $this->rookieStudent(2);
        $this->question('Rookie', 1, 'Month one question');
        $this->question('Rookie', 2, 'Month two question');
        $this->question('Rookie', 3, 'Month three question');

        $response = $this->actingAs($student)->get(route('academy.monthly'));

        $response->assertOk();
        $response->assertSee(route('academy.monthly.show', 1), false);
        $response->assertSee(route('academy.monthly.show', 2), false);
        $response->assertDontSee(route('academy.monthly.show', 3), false);
        $response->assertSee('Opens in Month 3');

        $this->actingAs($student)->get(route('academy.monthly.show', 1))->assertOk()->assertSee('Month one question');
        $this->actingAs($student)->get(route('academy.monthly.show', 2))->assertOk()->assertSee('Month two question');
        $this->actingAs($student)->get(route('academy.monthly.show', 3))->assertNotFound();
    }

    public function test_answering_a_month_shows_the_score_and_the_right_answer_and_saves_the_attempt(): void
    {
        $student = $this->rookieStudent(1);
        [$question, , $wrong] = $this->question('Rookie', 1, 'Is saving smart?');

        $response = $this->actingAs($student)->post(route('academy.monthly.submit', 1), [
            'answers' => [$question->id => $wrong->id],
        ]);

        $response->assertOk();
        $response->assertSee('0 / 1');
        $response->assertSee('Correct answer: True');
        $this->assertDatabaseHas('login_practice_attempts', ['student_id' => $student->id, 'month_number' => 1, 'score' => 0, 'total' => 1]);
        $this->assertDatabaseHas('participation_logs', ['student_id' => $student->id, 'action_detail' => 'monthly_questions_complete', 'action_ref' => 'month_1']);
    }

    public function test_practising_never_counts_as_the_daily_log_in_question(): void
    {
        $student = $this->rookieStudent(1);
        [$question, $right] = $this->question('Rookie', 1, 'Is saving smart?');

        $this->actingAs($student)->post(route('academy.monthly.submit', 1), ['answers' => [$question->id => $right->id]]);

        $this->assertSame(0, DB::table('login_quiz_attempts')->count());
        $this->actingAs($student)->getJson('/login-quiz/today')->assertJson(['show' => true, 'id' => $question->id]);
    }

    public function test_the_final_review_opens_with_the_last_month_and_mixes_every_month(): void
    {
        $locked = $this->rookieStudent(2);
        $this->question('Rookie', 1, 'Month one question');
        $this->question('Rookie', 3, 'Month three question');

        $this->actingAs($locked)->get(route('academy.monthly.final'))->assertNotFound();

        $student = $this->rookieStudent(3);
        $response = $this->actingAs($student)->get(route('academy.monthly.final'));

        $response->assertOk();
        $response->assertSee('Month one question');
        $response->assertSee('Month three question');
    }

    public function test_the_final_review_only_grades_this_levels_questions(): void
    {
        $student = $this->rookieStudent(1);
        [$rookieQuestion, $rookieRight] = $this->question('Rookie', 1, 'Rookie question');
        [$otherQuestion, $otherRight] = $this->question('Intermediate', 1, 'Intermediate question');

        $response = $this->actingAs($student)->post(route('academy.monthly.final.submit'), [
            'question_ids' => [$rookieQuestion->id, $otherQuestion->id],
            'answers' => [$rookieQuestion->id => $rookieRight->id, $otherQuestion->id => $otherRight->id],
        ]);

        $response->assertOk();
        $response->assertSee('1 / 1');
        $this->assertDatabaseHas('login_practice_attempts', ['student_id' => $student->id, 'month_number' => null, 'score' => 1, 'total' => 1]);
    }

    public function test_the_academy_home_links_to_monthly_questions(): void
    {
        $student = $this->rookieStudent(1);
        $this->question('Rookie', 1, 'Month one question');

        $this->actingAs($student)->get(route('academy.index'))
            ->assertOk()
            ->assertSee(route('academy.monthly'), false)
            ->assertSee('Monthly Questions');
    }
}
