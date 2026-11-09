<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Finhero\Credit\CompareLoans;
use App\Services\Finhero\QuizTasks;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;
use App\Support\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FinHero Tasks 16, «Compara dos préstamos», and 17, «Tu dinero crece»: the
 * student's own numbers, graded on the server.
 */
class LoanAndGrowthTasksTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        Level::flush();
        ZedDirecto::flush();
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', 'Intermediate')->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);

        return $user;
    }

    private function right(string $task, User $student): array
    {
        return array_map(fn ($q) => $q['options'][$q['answer']], QuizTasks::questions($task, (int) $student->id));
    }

    public function test_compare_two_loans(): void
    {
        $student = $this->student();
        $this->assertSame(2000.0, CompareLoans::amount($student->id));   // half of 3,952.40, in hundreds
        $offers = CompareLoans::offers($student->id);
        $this->assertSame([174.58, 94.96], [$offers['a']['instalment'], $offers['a']['interest']]);
        $this->assertSame([89.35, 144.4], [$offers['b']['instalment'], $offers['b']['interest']]);
        // B: the lower instalment; A: 94.96 of interest and the cheaper in all; same term: B; the bank says yes
        $this->assertSame([__('credit.compare.offer_b'), zeds(94.96), __('credit.compare.offer_a'), __('credit.compare.offer_b'), __('credit.compare.yes')],
            $this->right('task_16', $student));

        $this->actingAs($student)->get(route('task.page', ['task' => 'task-16']))->assertOk()
            ->assertSee(__('credit.compare.title'))->assertSee(zeds(174.58));
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-16'), ['answers' => array_column(QuizTasks::questions('task_16', $student->id), 'answer')])
            ->assertOk()->assertJson(['done' => true]);
    }

    public function test_your_money_grows_with_the_students_own_money_market(): void
    {
        $student = $this->student();
        $page = $this->actingAs($student)->get(route('task.page', ['task' => 'task-17']))->assertOk();
        $page->assertSee(__('compound.grows.title'))->assertSee(zeds(1000));   // nothing in it yet: the sample

        $account = BankAccount::where('student_id', $student->id)->first();
        $account->forceFill(['is_open_money_market_account' => 1])->save();
        SavingsLedger::post($account, 'money_market', 'credit', 'Own Transfer', 2345, 'In');
        $rate = SavingsLedger::rate('money_market', $student);
        $right = $this->right('task_17', $student);
        $this->assertSame(zeds(round(2340 * (1 + $rate) ** 5, 2)), $right[0]);   // their balance, in tens
        $this->assertSame(zeds(round(2340 * (1 + $rate * 10), 2)), $right[1]);
        foreach (QuizTasks::questions('task_17', $student->id) as $question) {
            $this->assertCount(3, array_unique($question['options']));
        }
    }
}
