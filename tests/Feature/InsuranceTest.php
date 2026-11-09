<?php

namespace Tests\Feature;

use App\Http\Controllers\BankController;
use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\InsurancePolicy;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\Finhero\QuizTasks;
use App\Services\InsuranceService;
use App\Services\LedgerService;
use App\Services\PriceRise;
use App\Services\ZedDirecto;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The health insurance, from the Intermediate (proposal «El seguro médico»,
 * step 12): Basic (30 Ƶ, 500 Ƶ deductible, 30 % co-payment, 3 months' wait for
 * the dentist) or Complete (80 Ƶ, 10 %), chosen in FinHero Task 15 after
 * working out the cases, changed once a year, the premium with the bills; the
 * City Hall gives Basic to whoever has none when their first month ends.
 */
class InsuranceTest extends TestCase
{
    use RefreshDatabase;

    private function setDay(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00', 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
        ZedDirecto::flush();
    }

    private function student(string $level = 'Intermediate'): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        DB::table('classes')->where('id', $class->id)->update(['intermediate_started_on' => now()->startOfMonth()->toDateString()]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        LedgerService::post($user->id, 'credit', 'Income', 3952.40, 'Monthly Salary Credit', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth()->addDays(5)]);

        return $user;
    }

    private function premiums(User $user): array
    {
        return Transaction1::where('user_id', $user->id)->where('description', InsuranceService::LINE)->orderBy('id')
            ->get()->map(fn ($t) => [Carbon::parse($t->transaction_date)->toDateString(), (float) $t->amount])->all();
    }

    private function answers(User $student): array
    {
        return array_column(QuizTasks::questions(InsuranceService::TASK, (int) $student->id), 'answer');
    }

    public function test_the_proposals_cases(): void
    {
        // the table of «El seguro médico»: a year with nothing, a filling, a broken arm, an appendicitis operation
        $this->assertSame([360.0, 960.0], [InsuranceService::yearCost('basic', 0), InsuranceService::yearCost('complete', 0)]);
        $this->assertSame([250.0, 25.0], [InsuranceService::studentPart('basic', 250), InsuranceService::studentPart('complete', 250)]);
        $this->assertSame([950.0, 1040.0], [InsuranceService::yearCost('basic', 800), InsuranceService::yearCost('complete', 800)]);
        $this->assertSame([1910.0, 1360.0], [InsuranceService::yearCost('basic', 4000), InsuranceService::yearCost('complete', 4000)]);
        // without a plan, all of it; Basic waits three months for the dentist, Complete doesn't
        $this->assertSame(800.0, InsuranceService::studentPart(null, 800));
        $this->setDay('2026-06-10');
        $since = Carbon::parse('2026-05-01');
        $this->assertSame(1000.0, InsuranceService::studentPart('basic', 1000, true, $since));
        $this->assertSame(100.0, InsuranceService::studentPart('complete', 1000, true, $since));
        $this->assertSame(650.0, InsuranceService::studentPart('basic', 1000, false, $since));   // an emergency: covered
        $this->setDay('2026-08-02');
        $this->assertSame(650.0, InsuranceService::studentPart('basic', 1000, true, $since));
    }

    public function test_choosing_once_a_year_and_the_premium_with_the_bills(): void
    {
        $this->setDay('2026-05-12');
        $this->assertSame('insurance_not_offered', InsuranceService::choose($this->student('Rookie'), 'basic'));
        $student = $this->student();

        // chosen after the 7th: this month's premium at once
        $this->assertNull(InsuranceService::choose($student, 'basic'));
        $this->assertSame([['2026-05-12', 30.0]], $this->premiums($student));
        $this->assertSame('insurance_same', InsuranceService::choose($student, 'basic'));
        $this->assertSame('insurance_once_a_year', InsuranceService::choose($student, 'complete'));
        InsuranceService::chargePremium($student);
        $this->assertCount(1, $this->premiums($student));

        // next month, with the bills of the 7th - in the price rise's month too: the premium doesn't rise
        $this->setDay('2026-06-07');
        app(BankController::class)->ensureMonthlyBills($student);
        $this->assertSame(['2026-06-07', 30.0], $this->premiums($student)[1]);
        $this->assertTrue(PriceRise::on($student));

        // a year later the student may change; the old plan ends
        $this->setDay('2027-05-12');
        $this->assertNull(InsuranceService::choose($student, 'complete'));
        $this->assertSame('complete', InsuranceService::current($student->id)->plan);
        $this->assertSame(2, InsurancePolicy::where('user_id', $student->id)->count());
        $this->assertNotNull(InsurancePolicy::where('user_id', $student->id)->where('plan', 'basic')->value('ended_on'));
    }

    public function test_the_city_hall_gives_basic_after_the_first_month(): void
    {
        $this->setDay('2026-05-12');
        $student = $this->student();
        $this->assertFalse(InsuranceService::assignDefault($student));   // still their first month

        $this->setDay('2026-06-07');
        app(BankController::class)->ensureMonthlyBills($student);
        $policy = InsuranceService::current($student->id);
        $this->assertSame(['basic', true], [$policy->plan, $policy->auto]);
        $this->assertSame(1, Mailbox::where('student_id', $student->id)->where('template', 'insurance-default')->count());
        $this->assertSame([['2026-06-07', 30.0]], $this->premiums($student));
        $this->assertFalse(InsuranceService::assignDefault($student));

        // they may still choose once, at once: keeping Basic makes it theirs
        $this->assertNull(InsuranceService::choose($student, 'basic'));
        $this->assertFalse(InsuranceService::current($student->id)->auto);
        $this->assertSame(1, InsurancePolicy::where('user_id', $student->id)->count());
        $this->assertSame('insurance_once_a_year', InsuranceService::choose($student, 'complete'));
    }

    public function test_the_task_is_done_once_the_student_chooses(): void
    {
        $this->setDay('2026-05-12');
        $student = $this->student();
        DB::table('finhero_activity_registry')->insert(['sid' => '1', 'cid' => $student->grade, 'type' => 'task', 'activity_key' => 'task_15',
            'activity_name' => 'Choose your health insurance', 'max_points' => 3, 'position' => 'required', 'is_active' => 1,
            'start_date' => '2026-05-01', 'end_date' => '2026-05-31', 'created_at' => now(), 'updated_at' => now()]);

        $questions = QuizTasks::questions(InsuranceService::TASK, (int) $student->id);
        $this->assertCount(5, $questions);
        foreach ($questions as $question) {
            $this->assertSame(count($question['options']), count(array_unique($question['options'])));
            $this->assertNotEmpty($question['why']);
        }
        $right = array_map(fn ($q) => $q['options'][$q['answer']], $questions);
        $this->assertSame([zeds(360), zeds(250), zeds(590), __('insurance.choose.a_complete'), __('insurance.choose.a_nothing')], $right);

        $this->actingAs($student)->get(route('task.page', ['task' => 'task-15']))->assertOk()
            ->assertSee(__('insurance.choose.title'))->assertSee(__('insurance.plan_basic'))->assertSee(__('insurance.row_deductible'));

        // all right, but no plan yet: not done
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-15'), ['answers' => $this->answers($student)])
            ->assertOk()->assertJson(['done' => true, 'choose' => true, 'message' => __('insurance.choose.now_choose')]);
        $this->assertDatabaseMissing('activity_completions', ['student_id' => $student->id]);

        // a plan without the right answers isn't chosen
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-15'), ['answers' => array_fill(0, 5, 99), 'plan' => 'complete'])
            ->assertOk()->assertJson(['done' => false]);
        $this->assertNull(InsuranceService::current($student->id));

        // with the plan: chosen, and the task is done
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-15'), ['answers' => $this->answers($student), 'plan' => 'complete'])
            ->assertOk()->assertJson(['done' => true, 'choose' => false]);
        $this->assertSame('complete', InsuranceService::current($student->id)->plan);
        $this->assertDatabaseHas('activity_completions', ['student_id' => $student->id, 'activity_type' => 'task_15']);
        $this->assertSame([['2026-05-12', 80.0]], $this->premiums($student));

        // trying to change it now: refused, with the date
        $this->actingAs($student)->postJson(route('finhero.quiz.answers', 'task-15'), ['answers' => $this->answers($student), 'plan' => 'basic'])
            ->assertOk()->assertJson(['error' => true, 'message' => __('flash.insurance_once_a_year', ['date' => Carbon::parse('2027-05-12')->locale('en')->isoFormat('LL')])]);
    }

    public function test_the_bank_and_the_admin_show_it(): void
    {
        $this->setDay('2026-05-12');
        $student = $this->student();
        // the Overview, not the sign-up steps
        BankAccount::where('student_id', $student->id)->update(['primary_savings_account_amount' => 3952.40]);
        \App\Models\AutoDebitRequest::create(['user_id' => $student->id, 'sid' => 1, 'type' => 'Rent']);
        $this->actingAs($student)->get(route('bank.index'))->assertOk()->assertSee(__('insurance.card_none'));
        InsuranceService::choose($student, 'basic');
        $this->actingAs($student)->get(route('bank.index'))->assertOk()
            ->assertSee(__('insurance.card_plan', ['plan' => __('insurance.plan_basic'), 'premium' => zeds(30)]))
            ->assertSee(txn_text(InsuranceService::LINE));   // among the month's bills

        $this->actingAs(User::factory()->create(['role' => 1]))->get(route('admin.insurance.index'))->assertOk()
            ->assertSee('Health Insurance')->assertSee('Basic')->assertSee('30.00 Ƶ');
    }

    public function test_the_spanish_page(): void
    {
        $this->setDay('2026-05-12');
        $student = $this->student();
        $student->forceFill(['preferred_language' => 'es'])->save();
        $this->actingAs($student)->get(route('task.page', ['task' => 'task-15']))->assertOk()
            ->assertSee('Elige tu seguro médico')->assertSee('Franquicia')->assertSee('Copago')->assertSee('Periodo de carencia');
    }
}
