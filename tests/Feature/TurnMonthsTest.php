<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;
use App\Support\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Months per turn (Daniel, 2026-11-09: "calcula cuál es la mejor opción para
 * aprender sobre economía y aplícalo"): a month of play is 3 months for saved
 * money in the Advance and 6 in the Advance Economist; before, 1.
 */
class TurnMonthsTest extends TestCase
{
    use RefreshDatabase;

    private function student(string $level): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);

        return User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
    }

    public function test_months_per_turn_and_the_interest_of_a_turn(): void
    {
        Level::flush();
        $rookie = $this->student('Rookie');
        $intermediate = $this->student('Intermediate');
        $advance = $this->student('Advance');
        $economist = $this->student('Advance Economist');
        $this->assertSame([1, 1, 3, 6], array_map(fn ($u) => Level::turnMonths($u), [$rookie, $intermediate, $advance, $economist]));

        $rate = SavingsLedger::rate('emergency');
        $this->assertSame(round(1000 * $rate / 12, 2), SavingsLedger::monthlyInterest(1000, 'emergency', $intermediate));
        $this->assertSame(round(1000 * $rate * 3 / 12, 2), SavingsLedger::monthlyInterest(1000, 'emergency', $advance));
        $this->assertSame(round(1000 * $rate * 6 / 12, 2), SavingsLedger::monthlyInterest(1000, 'emergency', $economist));
    }

    public function test_the_six_month_deposit_ends_after_two_turns_in_the_advance(): void
    {
        Level::flush();
        ZedDirecto::flush();
        $student = $this->student('Advance');
        $account = BankAccount::create(['student_id' => $student->id, 'sid' => '1', 'student_name' => $student->name, 'student_email' => $student->email, 'card_pin' => '1234']);
        $zd = app(ZedDirecto::class);
        $zd->openSavings($student);
        SavingsLedger::post($account->fresh(), ZedDirecto::SAVINGS, 'credit', 'Own Transfer', 500, 'In');
        $this->assertNull($zd->openDeposit($student, 300));
        $this->assertSame(today()->addMonthsNoOverflow(2)->toDateString(), $account->fresh()->zd_deposit_matures_on);
    }
}
