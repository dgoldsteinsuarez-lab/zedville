<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\IntermediateClassService;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Intermediate's scenario per student, on its own (Daniel, 2026-11-08: "debería
 * hacerse por estudiante ... [una] cuenta nueva, se le daría el B"): A with a fund
 * from the Rookie, B without; and the level's months, from the class's start
 * (the school year's unless an admin sets another).
 */
class IntermediateClassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 12, 10, 10, 0, 0, 'Europe/Berlin'));
        Level::flush();
    }

    private function classAt(string $level = 'Intermediate', ?string $started = '2026-11-01'): SchoolClass
    {
        $grade = Grade::create(['sid' => '1', 'name' => $level . ' ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);

        return SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id, 'intermediate_started_on' => $started]);
    }

    /** A student whose fund had $fund on $when (null: never saved). */
    private function student(SchoolClass $class, ?float $fund = null, string $when = '2026-09-06', string $created = '2026-04-01'): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => $created]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        if ($fund !== null) {
            SavingsTransaction::create(['user_id' => $user->id, 'bank_account_id' => $account->id, 'account' => 'emergency', 'type' => 'credit',
                'category' => 'Monthly transfer', 'amount' => $fund, 'balance' => $fund, 'description' => 'Monthly transfer', 'transaction_date' => $when]);
        }

        return $user;
    }

    public function test_the_levels_month_counts_from_the_class_start_or_the_school_year(): void
    {
        $this->assertSame(2, IntermediateClassService::month($this->classAt('Intermediate', '2026-11-01')));
        $this->assertNull(IntermediateClassService::month($this->classAt('Intermediate', '2026-11-01'), Carbon::create(2026, 10, 30)));
        // no start set: the school year's, which begins in April (config academic_year_start_month)
        $this->assertSame(9, IntermediateClassService::month($this->classAt('Intermediate', null)));
        $this->assertSame('2026-04-01', IntermediateClassService::start($this->classAt('Intermediate', null))->toDateString());
    }

    public function test_a_student_with_a_fund_from_the_rookie_is_a_a_new_account_is_b(): void
    {
        $class = $this->classAt();

        $this->assertSame('A', IntermediateClassService::scenarioFor($this->student($class, 4500)));
        $this->assertSame('B', IntermediateClassService::scenarioFor($this->student($class)));          // never saved
        $this->assertSame('B', IntermediateClassService::scenarioFor($this->student($class, 0)));       // an empty fund
        $this->assertSame('B', IntermediateClassService::scenarioFor($this->student($class, 800, '2026-12-01', '2026-12-01')));   // a new account: its fund came after the start
    }

    public function test_bs_months_count_from_when_they_arrived(): void
    {
        $class = $this->classAt('Intermediate', '2026-11-01');

        $this->assertSame(2, IntermediateClassService::studentMonth($this->student($class, 4500)));
        $this->assertSame(1, IntermediateClassService::studentMonth($this->student($class, null, '2026-09-06', '2026-12-03')));   // arrived in December
        $this->assertSame(2, IntermediateClassService::studentMonth($this->student($class, null, '2026-09-06', '2026-10-01')));   // there before the start
    }

    public function test_admins_see_the_scenarios_and_set_the_start(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        $class = $this->classAt('Intermediate', null);   // from April, the school year's start
        $veteran = $this->student($class, 3000, '2026-03-06', '2025-04-01');   // saved in the Rookie
        $newcomer = $this->student($class, null, '2026-09-06', '2026-12-01');
        $intermediate = GradeLevel::where('name', 'Intermediate')->value('id');

        $page = $this->actingAs($admin)->get(route('admin.intermediate.setup', ['level' => $intermediate]))->assertOk()
            ->assertSee($class->name)->assertSee('The start of the school year');
        $scenarios = $page->viewData('classes')->firstWhere('id', $class->id)->scenarios;
        $this->assertSame([$veteran->id], $scenarios['A']->pluck('id')->all());
        $this->assertSame([$newcomer->id], $scenarios['B']->pluck('id')->all());

        $this->actingAs($admin)->post(route('admin.intermediate.setup.update', $class), ['intermediate_started_on' => 'soon'])
            ->assertSessionHasErrors('intermediate_started_on');
        $this->actingAs($admin)->post(route('admin.intermediate.setup.update', $class), ['intermediate_started_on' => '2026-11'])
            ->assertSessionHas('success');
        $this->assertSame('2026-11-01', $class->fresh()->intermediate_started_on->toDateString());
    }
}
