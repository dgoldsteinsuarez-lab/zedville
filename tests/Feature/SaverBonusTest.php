<?php

namespace Tests\Feature;

use App\Http\Controllers\BankController;
use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\PriceRise;
use App\Services\SaverBonus;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Intermediate's saver bonus (proposal «Premio al ahorro», Daniel
 * 2026-11-08): with the first salary of the level, 5 % of the Emergency Fund
 * brought from the Rookie (at most 400 Ƶ); with the salary of months 4, 7 and
 * 10, 5 % of what was saved in the quarter (at most 150 Ƶ). Once each, with a
 * letter, and never as 'Income' (that is the salary).
 */
class SaverBonusTest extends TestCase
{
    use RefreshDatabase;

    private function setDay(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00', 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
        ZedDirecto::flush();
    }

    private function student(float $fundBefore): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', 'Intermediate')->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => '2026-01-10']);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        $account->forceFill(['is_open_emergency_account' => 1])->save();
        if ($fundBefore > 0) {
            // the Rookie's fund, before the school year started (1 April)
            SavingsLedger::post($account, 'emergency', 'credit', 'Monthly transfer', $fundBefore, 'Monthly transfer from your current account', null, Carbon::parse('2026-03-06'));
        }

        return $user;
    }

    private function bonus(User $user, string $line): float
    {
        return (float) Transaction1::where('user_id', $user->id)->where('description', $line)->sum('amount');
    }

    public function test_the_welcome_bonus_with_the_first_salary_of_the_level(): void
    {
        $this->setDay('2026-04-06');
        $rookie = $this->student(7904.80);   // a year of 20 %: 395,24
        $rich = $this->student(12000);       // 600, but at most 400
        $new = $this->student(0);            // scenario B: no welcome

        foreach ([$rookie, $rich, $new] as $student) {
            app(BankController::class)->creditMonthlySalary($student->id);
        }
        $this->assertSame([395.24, 400.0, 0.0], [$this->bonus($rookie, SaverBonus::WELCOME), $this->bonus($rich, SaverBonus::WELCOME), $this->bonus($new, SaverBonus::WELCOME)]);
        $this->assertSame('Bonus', Transaction1::where('user_id', $rookie->id)->where('description', SaverBonus::WELCOME)->value('category'));
        $this->assertSame(1, Mailbox::where('student_id', $rookie->id)->where('template', 'saver-bonus')->count());
        app()->setLocale('es');
        $this->assertStringContainsString('Bienvenido/a al Intermedio', Mailbox::where('student_id', $rookie->id)->where('template', 'saver-bonus')->first()->content);

        // once: not again the same month, nor the next
        SaverBonus::payDue($rookie);
        $this->setDay('2026-05-06');
        SaverBonus::payDue($rookie);
        $this->assertSame(395.24, $this->bonus($rookie, SaverBonus::WELCOME));
    }

    public function test_the_quarter_bonus_with_the_salary_of_month_4(): void
    {
        $this->setDay('2026-07-06');   // the level's month 4
        $student = $this->student(0);
        $account = BankAccount::where('student_id', $student->id)->first();
        // saved in April, May and June: 800 a month into the fund, 300 out of it once
        foreach (['2026-04-06', '2026-05-06', '2026-06-06'] as $day) {
            SavingsLedger::post($account->fresh(), 'emergency', 'credit', 'Monthly transfer', 800, 'Monthly transfer from your current account', null, Carbon::parse($day));
        }
        SavingsLedger::post($account->fresh(), 'emergency', 'debit', 'Own Transfer', 300, 'Transfer to your current account', null, Carbon::parse('2026-06-20'));

        $this->assertSame(105.0, SaverBonus::quarterAmount($student, now()));   // 5 % of 2,100
        $this->assertSame([SaverBonus::QUARTER => 105.0], SaverBonus::payDue($student));
        $this->assertSame([], SaverBonus::payDue($student));   // once in the month

        $this->setDay('2026-08-06');   // month 5: nothing
        $this->assertSame([], SaverBonus::payDue($student));
    }
}
