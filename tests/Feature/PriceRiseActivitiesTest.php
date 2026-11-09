<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Finhero\QuizTasks;
use App\Services\LedgerService;
use App\Services\PriceRise;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The price rise's activities, FinHero Tasks 9-13 (Daniel, 2026-11-08: "qué
 * subió y cuánto, el poder adquisitivo y cuánto ahorra ahora"): a chart and
 * questions to choose an answer for, with the student's own numbers, graded on
 * the server.
 */
class PriceRiseActivitiesTest extends TestCase
{
    use RefreshDatabase;

    private const TASKS = ['task_9', 'task_10', 'task_11', 'task_12', 'task_13', 'task_14'];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 5, 12, 10, 0, 0, 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
    }

    private function student(): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', 'Intermediate')->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        LedgerService::post($user->id, 'credit', 'Income', 3952.40, 'Monthly Salary', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->subDays(6)]);

        return $user;
    }

    private function right(string $task, User $student): array
    {
        return array_map(fn ($q) => $q['options'][$q['answer']], QuizTasks::questions($task, (int) $student->id));
    }

    public function test_every_question_has_three_different_options_and_one_right(): void
    {
        $student = $this->student();
        foreach (self::TASKS as $task) {
            foreach (QuizTasks::questions($task, (int) $student->id) as $i => $question) {
                $this->assertCount(isset($question['chart']) ? 4 : 3, array_unique($question['options']), "$task question $i");
                $this->assertArrayHasKey($question['answer'], $question['options']);
                $this->assertNotEmpty($question['why']);
            }
        }
    }

    public function test_what_went_up_and_the_salary_s_worth(): void
    {
        $student = $this->student();
        $rise = $this->right('task_9', $student);
        $this->assertSame(__('prices.groups.electronics'), $rise[2]);

        $salary = $this->right('task_11', $student);
        $this->assertSame([__('inflation.salary.a_less'), PriceRise::pct(95.2, false), zeds(4150.02), __('inflation.salary.a_power')], $salary);
    }

    public function test_my_shopping_and_what_is_left_use_the_students_own_month(): void
    {
        $student = $this->student();
        ConsumerSurvey::create(['user_id' => $student->id, 'sid' => '1', 'diet' => 'omnivore', 'survey_month' => 'Apr-2026', 'groceries' => [],
            'basket_cost' => 100, 'lunch' => 0, 'transport' => 100, 'activities' => 0, 'restaurants' => 0, 'total_cost' => 200]);

        // groceries without items rise like the index (5 %), transport 8 %: 200 → 213
        $basket = $this->right('task_10', $student);
        $this->assertSame([zeds(213), zeds(13), PriceRise::pct(6.5, false), zeds(156)], $basket);

        // bills 1,440 → 1,511.60, the shopping 200 → 213, the plan of the month 15 % of the salary + 4 %
        $plan = round(0.15 * 3952.40, 2);
        $after = round(1511.6 + 213 + round($plan * 1.04, 2), 2);
        $left = $this->right('task_12', $student);
        $this->assertSame(zeds(round($after - (1440 + 200 + $plan), 2)), $left[0]);
        $this->assertSame(zeds(round(3952.40 - $after, 2)), $left[1]);
    }

    public function test_the_real_interest_of_the_money_market(): void
    {
        $student = $this->student();
        $real = $this->right('task_13', $student);
        // the Intermediate's Money Market pays 5 % in its second month: 5 % - 5 %; with 1,000 Ƶ, 1,050 against 1,050
        $this->assertSame(PriceRise::pct(0, false), $real[0]);
        $this->assertSame(__('inflation.real.a_exact'), $real[1]);
        $this->assertSame(PriceRise::pct(-5, false), $real[2]);
    }

    public function test_the_pages_keep_the_answers_on_the_server_and_an_assigned_one_is_done(): void
    {
        $student = $this->student();
        foreach (self::TASKS as $task) {
            $page = $this->actingAs($student)->get(route('task.page', ['task' => str_replace('_', '-', $task)]))->assertOk()
                ->assertSee(__('inflation.' . QuizTasks::ALL[$task]['key'] . '.title'));
            $this->assertStringNotContainsString('"answer"', $page->getContent(), $task);
        }

        DB::table('finhero_activity_registry')->insert(['sid' => '1', 'cid' => $student->grade, 'type' => 'task', 'activity_key' => 'task_11',
            'activity_name' => 'What is my salary worth?', 'max_points' => 3, 'position' => 'required', 'is_active' => 1,
            'start_date' => '2026-05-01', 'end_date' => '2026-05-31', 'created_at' => now(), 'updated_at' => now()]);
        $answers = array_column(QuizTasks::questions('task_11', (int) $student->id), 'answer');
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-11'), ['answers' => $answers])
            ->assertOk()->assertJson(['done' => true, 'message' => __('quiz.all_right')]);
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => 'task_11']);
    }

    public function test_the_spanish_page(): void
    {
        $student = $this->student();
        $student->forceFill(['preferred_language' => 'es'])->save();

        $this->actingAs($student)->get(route('task.page', ['task' => 'task-9']))->assertOk()
            ->assertSee('¿Qué ha subido?')->assertSee('Fruta y verdura')->assertSee('Comprobar');
    }

    public function test_move_the_line_for_each_piece_of_news(): void
    {
        $student = $this->student();
        $questions = QuizTasks::questions('task_14', (int) $student->id);
        $this->assertCount(6, $questions);

        // the drought (Zedville's own rise) moves supply left, a good harvest right, saving more moves demand left, a bonus right
        $moves = ['drought' => 0, 'harvest' => 1, 'save' => 2, 'bonus' => 3];
        $shift = array_values(array_filter($questions, fn ($q) => ($q['chart'] ?? null) === 'shift'));
        $this->assertCount(4, $shift);
        foreach ($moves as $news => $right) {
            $question = collect($shift)->first(fn ($q) => str_starts_with($q['text'], __('inflation.shift.news_' . $news)));
            $this->assertNotNull($question, $news);
            $this->assertSame($right, $question['answer'], $news);
            $this->assertSame(__('inflation.shift.a_supply_left'), $question['options'][0]);   // the chart reads the options in this order
        }
        $right = array_map(fn ($q) => $q['options'][$q['answer']], array_slice($questions, 4));
        $this->assertSame([__('inflation.shift.a_up_less'), __('inflation.shift.a_cost_push')], $right);

        // @js writes the page's data with each quote as backslash-u0022
        $quote = chr(92) . 'u0022';
        $page = $this->actingAs($student)->get(route('task.page', ['task' => 'task-14']))->assertOk()
            ->assertSee(__('inflation.shift.title'))->assertSee($quote . 'chart' . $quote . ':' . $quote . 'shift' . $quote, false)
            ->assertSee(__('inflation.shift.supply'));
        $this->assertStringNotContainsString('"answer"', $page->getContent());
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-14'), ['answers' => array_column($questions, 'answer')])
            ->assertOk()->assertJson(['done' => true]);
    }
}
