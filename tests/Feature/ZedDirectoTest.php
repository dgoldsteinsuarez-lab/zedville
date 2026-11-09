<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\Mailbox;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Models\WantsPlanChoice;
use App\Services\Finhero\QuizTasks;
use App\Services\LedgerService;
use App\Services\PriceRise;
use App\Services\SavingsLedger;
use App\Services\WantsPlanService;
use App\Services\ZedDirecto;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Zed Directo, the Intermediate's second bank (Daniel, 2026-11-08: "te
 * confirmo"): in the level's third month the Money Market goes from 4% to 5%
 * and Zed Directo opens - a savings account at 6% and a 6-month deposit at 7%
 * that loses its interest if taken out early. Two letters, its page, and the
 * real interest task with the second bank.
 */
class ZedDirectoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // the school year starts on 1 April: June is the Intermediate's third month
        $this->setDay('2026-06-10');
    }

    private function setDay(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00', 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
        ZedDirecto::flush();
    }

    private function student(string $level = 'Intermediate', ?string $startedOn = null): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => $level . ' grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id, 'intermediate_started_on' => $startedOn]);
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => '2026-03-01']);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        LedgerService::post($user->id, 'credit', 'Income', 3952.40, 'Monthly Salary', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->subDays(4)]);

        return $user;
    }

    private function account(User $user): BankAccount
    {
        return BankAccount::where('student_id', $user->id)->first();
    }

    public function test_it_opens_in_the_third_month_and_the_money_market_rises(): void
    {
        $student = $this->student();
        $this->assertTrue(ZedDirecto::open($student));
        $this->assertSame('2026-06-01', ZedDirecto::since($student)->toDateString());
        // the Intermediate's Money Market: 5 %, and 6 % from its third month (Daniel, 2026-11-08)
        $this->assertSame(0.06, SavingsLedger::rate('money_market', $student));
        $this->assertSame(0.05, SavingsLedger::rate('money_market', $student, Carbon::parse('2026-05-15')));   // its second month
        $this->assertSame([0.07, 0.08], [SavingsLedger::rate(ZedDirecto::SAVINGS), SavingsLedger::rate(ZedDirecto::DEPOSIT)]);   // the deposit: IPC + 3 %

        $second = $this->student('Intermediate', '2026-05-01');
        $this->assertFalse(ZedDirecto::open($second));
        $this->assertSame(0.05, SavingsLedger::rate('money_market', $second));
        $this->assertFalse(ZedDirecto::open($this->student('Rookie')));
        $this->assertSame(0.04, SavingsLedger::rate('money_market', $this->student('Rookie')));
    }

    public function test_the_monthly_interest_follows_the_students_rate(): void
    {
        $student = $this->student();
        $account = $this->account($student);
        $account->forceFill(['is_open_money_market_account' => 1, 'money_market_account_amount' => 2400])->save();

        $this->artisan('savings:credit-interest', ['--write' => true, '--month' => '2026-05'])->assertExitCode(0);
        $this->artisan('savings:credit-interest', ['--write' => true, '--month' => '2026-06'])->assertExitCode(0);
        $paid = SavingsTransaction::where('user_id', $student->id)->where('category', 'Interest')->orderBy('id')->pluck('amount')->map(fn ($a) => (float) $a)->all();
        $this->assertSame([10.0, 12.05], $paid);   // May: 2,400 × 5 % ÷ 12; June: 2,410 × 6 % ÷ 12
    }

    public function test_the_savings_account_takes_money_in_and_out(): void
    {
        $student = $this->student();
        $zd = app(ZedDirecto::class);
        $this->assertSame('opened', $zd->openSavings($student));
        $this->assertSame('open', $zd->openSavings($student));
        $this->assertStringStartsWith('ZD', $this->account($student)->zd_savings_number);

        // like the Money Market: from the 26th, the month's tasks done
        $this->assertSame('zd_locked_day', $zd->move($student, 'in', 500));
        $this->setDay('2026-06-27');
        $this->assertSame('zd_locked_tasks', $zd->move($student, 'in', 500));
        WantsPlanChoice::create(['user_id' => $student->id, 'month' => WantsPlanService::month(), 'kind' => 'plan', 'total' => 0]);
        $this->assertNull($zd->move($student, 'in', 500));
        $this->assertSame('insufficient_balance', $zd->move($student, 'out', 600));
        $this->assertNull($zd->move($student, 'out', 200));

        $this->assertSame(300.0, (float) $this->account($student)->zd_savings_amount);
        $this->assertSame(500.0, (float) Transaction1::where('user_id', $student->id)->where('description', ZedDirecto::TO_ZD)->value('amount'));
        $this->assertSame(200.0, (float) Transaction1::where('user_id', $student->id)->where('description', ZedDirecto::FROM_ZD)->value('amount'));
        $this->assertSame('Own Transfer', Transaction1::where('user_id', $student->id)->where('description', ZedDirecto::TO_ZD)->value('category'));   // not spending

        // 7 % a year, paid each month - taking money out costs nothing here
        $this->artisan('savings:credit-interest', ['--write' => true, '--month' => '2026-06'])->assertExitCode(0);
        $this->assertSame(1.75, (float) SavingsTransaction::where('user_id', $student->id)->where('account', ZedDirecto::SAVINGS)->where('category', 'Interest')->value('amount'));
    }

    public function test_the_deposit_pays_at_its_end_and_nothing_if_taken_out_early(): void
    {
        $student = $this->student();
        $zd = app(ZedDirecto::class);
        $zd->openSavings($student);
        $this->account($student)->forceFill(['zd_savings_amount' => 3000])->save();

        $this->assertSame('zd_deposit_min', $zd->openDeposit($student, 50));
        $this->assertNull($zd->openDeposit($student, 1000));
        $this->assertSame('zd_deposit_exists', $zd->openDeposit($student, 500));
        $account = $this->account($student);
        $this->assertSame([2000.0, 1000.0, '2026-12-10'], [(float) $account->zd_savings_amount, (float) $account->zd_deposit_amount, $account->zd_deposit_matures_on]);

        // taken out early: the money back, no interest
        $this->assertNull($zd->cancelDeposit($student));
        $this->assertSame([3000.0, 0.0, 0], [(float) $this->account($student)->zd_savings_amount, (float) $this->account($student)->zd_deposit_amount, (int) $this->account($student)->is_open_zd_deposit]);
        $this->assertSame('zd_no_deposit', $zd->cancelDeposit($student));

        // kept 6 months: IPC + 3 % = 8 % a year, for half a year
        $zd->openDeposit($student, 1000);
        $this->assertSame(0, ZedDirecto::endDue(null, Carbon::parse('2026-12-09')));
        $this->assertSame(1, ZedDirecto::endDue(null, Carbon::parse('2026-12-10')));
        $this->assertSame(40.0, ZedDirecto::depositInterest(1000));
        $this->assertSame(3040.0, (float) $this->account($student)->zd_savings_amount);
        $this->assertSame(0, (int) $this->account($student)->is_open_zd_deposit);
    }

    public function test_a_month_with_money_taken_out_of_the_money_market_earns_nothing(): void
    {
        // every level, the Rookie at its 4 % too (Daniel, 2026-11-08)
        $student = $this->student('Rookie');
        $account = $this->account($student);
        $account->forceFill(['is_open_money_market_account' => 1, 'money_market_account_amount' => 1300])->save();
        SavingsLedger::post($account, 'money_market', 'debit', 'Own Transfer', 100, 'Transfer to your current account', null, Carbon::parse('2026-05-20'));

        $this->artisan('savings:credit-interest', ['--write' => true, '--month' => '2026-05'])->assertExitCode(0);
        $this->artisan('savings:credit-interest', ['--write' => true, '--month' => '2026-06'])->assertExitCode(0);
        $paid = SavingsTransaction::where('user_id', $student->id)->where('category', 'Interest')->get(['amount', 'meta']);
        $this->assertSame([['2026-06', 4.0]], $paid->map(fn ($t) => [$t->meta['month'], (float) $t->amount])->all());   // 1,200 × 4 % ÷ 12

        // this month too: the bank says so instead of the next interest
        SavingsLedger::post($account->fresh(), 'money_market', 'debit', 'Own Transfer', 50, 'Transfer to your current account');
        $this->assertTrue(app(\App\Http\Controllers\SavingsAccountController::class)->interest($student->id, 'money_market')['withdrawn']);
        $this->assertSame(0.0, app(\App\Http\Controllers\SavingsAccountController::class)->interest($student->id, 'money_market')['estimated_interest']);
    }

    public function test_taking_money_out_of_the_money_market_costs_two_percent_at_most_the_interest_earned(): void
    {
        $student = $this->student('Rookie');
        $account = $this->account($student);
        $account->forceFill(['is_open_money_market_account' => 1, 'money_market_account_number' => '111122223333', 'primary_savings_account_number' => '9988776655443'])->save();
        SavingsLedger::post($account, 'money_market', 'credit', 'Own Transfer', 1000, 'Transfer from your current account', null, Carbon::parse('2026-04-27'));
        SavingsLedger::post($account->fresh(), 'money_market', 'credit', 'Interest', 6, 'Interest', ['month' => '2026-05'], Carbon::parse('2026-05-31'));
        $out = fn (float $amount) => app(\App\Services\BankTransferService::class)->execute(\App\Models\Transfer::create([
            'user_id' => $student->id, 'kind' => 'transfer', 'type' => 'now', 'status' => 'done', 'from_account' => 'money_market',
            'account_number' => '9988776655443', 'beneficiary_name' => $student->name, 'amount' => $amount,
        ]));

        // 2 % of 200 is 4, under the 6 earned: 196 arrive
        $this->assertSame(['ok' => true, 'fee' => 4.0], $out(200));
        $this->assertSame(196.0, (float) Transaction1::where('user_id', $student->id)->where('description', 'Transfer from your Money Market Account')->value('amount'));
        $this->assertSame(806.0, (float) $this->account($student)->money_market_account_amount);   // 1,006 - 200
        // 2 % of 300 would be 6, but only 2 of the interest is left: never the money put in
        $this->assertSame(['ok' => true, 'fee' => 2.0], $out(300));
        $this->assertSame(['ok' => true, 'fee' => 0.0], $out(100));
        $this->assertSame(406.0, (float) $this->account($student)->money_market_account_amount);
    }

    public function test_the_two_letters_come_once(): void
    {
        $student = $this->student();
        ZedDirecto::announce($student);
        ZedDirecto::announce($student);

        $this->assertSame(1, Mailbox::where('student_id', $student->id)->where('template', 'rates-up')->count());
        $this->assertSame(1, Mailbox::where('student_id', $student->id)->where('template', 'zed-directo-open')->count());
        app()->setLocale('es');
        $letter = Mailbox::where('student_id', $student->id)->where('template', 'rates-up')->first();
        $this->assertStringContainsString('ha subido los tipos de interés', $letter->content);
        $this->assertStringContainsString(PriceRise::pct(6, false), $letter->content);   // 6 %, it was 5 %
        $this->assertStringContainsString(route('zeddirecto.index'), Mailbox::where('template', 'zed-directo-open')->first()->content);

        $second = $this->student('Intermediate', '2026-05-01');
        ZedDirecto::announce($second);
        $this->assertSame(0, Mailbox::where('student_id', $second->id)->count());
    }

    public function test_its_page(): void
    {
        $student = $this->student();
        $this->actingAs($student)->get(route('zeddirecto.index'))->assertOk()
            ->assertSee(__('zeddirecto.compare_title'))->assertSee(__('zeddirecto.open'))->assertSee(PriceRise::pct(1, true));   // 6 % - 5 %
        $this->actingAs($student)->post(route('zeddirecto.open'))->assertSessionHas('success');
        $this->actingAs($student)->get(route('zeddirecto.index'))->assertOk()->assertSee(__('zeddirecto.in_title'));
        $this->actingAs($student)->get(route('bank.index'))->assertOk();

        $this->actingAs($this->student('Intermediate', '2026-05-01'))->get(route('zeddirecto.index'))->assertOk()
            ->assertSee(__('zeddirecto.not_yet', ['month' => Carbon::parse('2026-07-01')->locale('en')->isoFormat('MMMM')]));
        $this->actingAs($this->student('Rookie'))->get(route('zeddirecto.index'))->assertNotFound();
    }

    public function test_the_real_interest_task_compares_the_second_bank(): void
    {
        $student = $this->student();
        $questions = QuizTasks::questions('task_13', (int) $student->id);
        $right = array_map(fn ($q) => $q['options'][$q['answer']], $questions);
        $this->assertCount(5, $questions);
        $this->assertSame(PriceRise::pct(1, false), $right[0]);   // the Money Market: 6 % - 5 %
        $this->assertSame(PriceRise::pct(2, false), $right[4]);   // Zed Directo: 7 % - 5 %
        $this->assertCount(4, QuizTasks::questions('task_13', (int) $this->student('Intermediate', '2026-05-01')->id));
    }
}
