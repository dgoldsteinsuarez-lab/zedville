<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\CurriculumQuestion;
use App\Models\CurriculumQuestionOption;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\BadgeCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-22: "fix all what you say" - the Engagement badge needs
 * 100% of required activities done, but most activities never recorded
 * being finished, so a class with any of them required could never earn
 * it. Each one now records a completion against the student's own class's
 * calendar event (ActivityCompletionService).
 */
class ActivityCompletionTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        $level = GradeLevel::where('name', 'Rookie')->first();
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => '1', 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
    }

    private function requiredEvent(User $student, string $title): int
    {
        return DB::table('calendar_events')->insertGetId([
            'sid' => '1', 'classId' => $student->grade, 'title' => $title, 'position' => 'required',
            'start' => now(), 'end' => now()->addDays(5), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function withFunds(User $student, float $balance): BankAccount
    {
        $account = BankAccount::create([
            'student_id' => $student->id, 'sid' => 1, 'student_name' => $student->name, 'student_email' => $student->email,
            'card_pin' => '1234', 'is_open_emergency_account' => 0, 'is_open_money_market_account' => 0,
        ]);
        Transaction1::create([
            'user_id' => $student->id, 'sid' => 1, 'bank_account_id' => $account->id, 'transaction_date' => now(),
            'description' => 'Opening balance', 'type' => 'credit', 'category' => 'Income',
            'amount' => $balance, 'balance' => $balance, 'is_penalty' => false,
        ]);

        return $account;
    }

    private function engagement(User $student)
    {
        return app(BadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);
    }

    public function test_finishing_an_assigned_finhero_task_completes_it(): void
    {
        $student = $this->student();
        DB::table('finhero_activity_registry')->insert([
            'activity_key' => 'task_2', 'activity_name' => 'Budget Calculations', 'type' => 'task',
            'sid' => 1, 'cid' => $student->grade, 'position' => 'required', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $eventId = $this->requiredEvent($student, 'task-Budget Calculations');

        $this->actingAs($student)->postJson(route('finhero.task-complete'), ['activity_key' => 'task_2'])
            ->assertJson(['success' => true, 'recorded' => true]);
        $this->actingAs($student)->postJson(route('finhero.task-complete'), ['activity_key' => 'task_2'])
            ->assertJson(['success' => true, 'recorded' => false]);

        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'task_2']);
        $this->assertSame(1, (int) $this->engagement($student)->required_completed);
    }

    public function test_a_task_that_isnt_assigned_to_the_class_cant_be_completed(): void
    {
        $student = $this->student();

        $this->actingAs($student)->postJson(route('finhero.task-complete'), ['activity_key' => 'task_2'])->assertNotFound();
        $this->assertDatabaseMissing('activity_completions', ['student_id' => $student->id]);
    }

    public function test_checking_out_the_assigned_spending_activity_completes_it(): void
    {
        $student = $this->student();
        $this->withFunds($student, 500);
        $activityId = DB::table('activities')->value('id');
        DB::table('mba_position')->insert([
            'user_id' => $student->id, 'activity_key' => 'hba_position', 'activity_id' => $activityId,
            'sid' => '1', 'cid' => $student->grade, 'required' => 1, 'optional' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $eventId = $this->requiredEvent($student, 'Spending budget activity');
        $productId = DB::table('supermarkets')->insertGetId([
            'product_name' => 'Rice', 'type' => 'omnivore', 'category' => 'Needs', 'price' => 10,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($student)->postJson('/order/placeActivity', [
            'pin' => '1234', 'cart' => [['id' => $productId, 'quantity' => 2]], 'total' => 20,
            'catalog' => 'supermarkets', 'activity_id' => $activityId,
        ])->assertJson(['status' => true]);

        $this->assertDatabaseHas('activity_completions', [
            'student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'high_budget_activity',
        ]);
    }

    public function test_a_normal_shopping_trip_doesnt_complete_the_spending_activity(): void
    {
        $student = $this->student();
        $this->withFunds($student, 500);
        $productId = DB::table('supermarkets')->insertGetId([
            'product_name' => 'Rice', 'type' => 'omnivore', 'category' => 'Needs', 'price' => 10,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($student)->postJson('/order/placeActivity', [
            'pin' => '1234', 'cart' => [['id' => $productId, 'quantity' => 1]], 'total' => 10, 'catalog' => 'supermarkets',
        ])->assertJson(['status' => true]);

        $this->assertDatabaseMissing('activity_completions', ['student_id' => $student->id]);
    }

    public function test_opening_the_emergency_fund_account_completes_the_emergency_fund_activity(): void
    {
        $student = $this->student();
        $this->withFunds($student, 2000);
        $eventId = $this->requiredEvent($student, 'Emergency Fund');

        $this->actingAs($student)->post('/bank-account/emengercy-fund-account')->assertRedirect();

        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'emergency_fund']);
        $this->assertSame(1, (int) $this->engagement($student)->required_completed);
    }

    public function test_an_emergency_fund_account_opened_before_the_activity_still_counts(): void
    {
        $student = $this->student();
        $account = $this->withFunds($student, 2000);
        $account->forceFill(['is_open_emergency_account' => 1])->save();
        $this->requiredEvent($student, 'Emergency Fund');

        $record = $this->engagement($student);

        $this->assertSame(1, (int) $record->required_assigned);
        $this->assertSame(1, (int) $record->required_completed);
    }

    public function test_taking_a_real_exam_completes_its_calendar_activity(): void
    {
        $student = $this->student();
        $category = CurriculumCategory::where('grade_level_id', GradeLevel::where('name', 'Rookie')->value('id'))->first();
        $exam = CurriculumItem::create([
            'category_id' => $category->id, 'title' => 'Year Exam', 'type' => 'quiz', 'sort_order' => 1,
            'is_active' => true, 'is_real_exam' => true,
        ]);
        $question = CurriculumQuestion::create(['item_id' => $exam->id, 'question_text' => 'Q', 'sort_order' => 1]);
        $option = CurriculumQuestionOption::create(['question_id' => $question->id, 'option_text' => 'A', 'is_correct' => true, 'sort_order' => 1]);
        $eventId = $this->requiredEvent($student, 'exam-Year Exam');

        $this->actingAs($student)->post(route('academy.item.submit', $exam), ['answers' => [$question->id => $option->id]])->assertOk();

        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_id' => $eventId, 'activity_type' => 'exam_' . $exam->id]);
    }
}
