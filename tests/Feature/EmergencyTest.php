<?php

namespace Tests\Feature;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\Emergency;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\Loan;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Services\EmergencyService;
use App\Services\InsuranceService;
use App\Services\LedgerService;
use App\Services\PriceRise;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The unexpected expenses, from the Intermediate (proposal «El imprevisto»,
 * step 13): months 3, 6 and 9 of the level in scenario A, 5, 7 and 9 in B;
 * it comes the first time the student logs in from the 7th, or on the
 * month's last day; 30 days to pay it with the fund, the current account or a
 * loan, then two questions; not paid, it is taken - into overdraft if need be.
 */
class EmergencyTest extends TestCase
{
    use RefreshDatabase;

    private function setDay(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00', 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
        ZedDirecto::flush();
    }

    /** A student whose class started the level on 1 April 2026; with $fund in an Emergency Fund from March, scenario A. */
    private function student(float $fund = 0, string $level = 'Intermediate', float $salary = 3952.40): User
    {
        $grade = Grade::create(['sid' => '1', 'name' => 'Grade ' . uniqid(), 'grade_level_id' => GradeLevel::where('name', $level)->value('id')]);
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid(), 'grade_id' => $grade->id]);
        $class->forceFill(['intermediate_started_on' => '2026-04-01'])->save();
        $user = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id, 'created_at' => Carbon::parse('2026-03-01')]);
        $account = BankAccount::create(['student_id' => $user->id, 'sid' => '1', 'student_name' => $user->name, 'student_email' => $user->email, 'card_pin' => '1234']);
        $account->forceFill(['primary_savings_account_amount' => 100])->save();
        AutoDebitRequest::create(['user_id' => $user->id, 'sid' => 1, 'type' => 'Rent']);
        if ($fund > 0) {
            $account->forceFill(['is_open_emergency_account' => 1])->save();
            SavingsLedger::post($account, 'emergency', 'credit', 'Transfer', $fund, 'Emergency Fund', null, Carbon::parse('2026-03-15'));
        }
        LedgerService::post($user->id, 'credit', 'Income', $salary, 'Monthly Salary Credit', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth()->addDays(5)]);

        return $user;
    }

    private function balance(User $user): float
    {
        return (float) Transaction1::where('user_id', $user->id)->latest('id')->value('balance');
    }

    public function test_the_months_by_scenario_and_level(): void
    {
        $intermediate = GradeLevel::where('name', 'Intermediate')->value('id');
        $this->assertSame(['enabled' => true, 'A' => [3, 6, 9], 'B' => [5, 7, 9], 'frauds' => true, 'call' => 4, 'email' => 7], EmergencyService::settings($intermediate));
        $this->assertFalse(EmergencyService::settings(GradeLevel::where('name', 'Rookie')->value('id'))['enabled']);
        $this->assertSame([2, 4, 10], EmergencyService::months('10, 4,2, 4, 13'));

        // A (a fund from the Rookie): month 3 of the level is June; B: August
        $this->setDay('2026-06-10');
        $a = $this->student(800);
        $b = $this->student();
        $this->assertSame(['number' => 1, 'month' => 3], EmergencyService::dueNow($a));
        $this->assertNull(EmergencyService::dueNow($b));
        $this->assertNull(EmergencyService::dueNow($this->student(800, 'Rookie')));
        $this->setDay('2026-08-10');
        $this->assertSame(['number' => 1, 'month' => 5], EmergencyService::dueNow($b));
    }

    public function test_it_comes_at_the_first_login_from_the_7th_or_on_the_last_day(): void
    {
        $this->setDay('2026-06-03');
        $student = $this->student(800);
        $this->actingAs($student)->get(route('dashboard'))->assertOk();
        $this->assertSame(0, Emergency::count());   // before the salary and the bills

        $this->setDay('2026-06-10');
        $expected = EmergencyService::kindFor($student, 1);   // always the same one for the same student
        $this->actingAs($student)->get(route('dashboard'))->assertOk();
        $emergency = Emergency::where('user_id', $student->id)->firstOrFail();
        $this->assertSame($expected, $emergency->kind);
        $k = EmergencyService::KINDS[$emergency->kind];
        $this->assertSame([1, 3, '2026-07-09'], [$emergency->number, $emergency->month_in_level, $emergency->due_on->toDateString()]);
        $this->assertSame($k['cost'], (float) $emergency->cost);
        $this->assertSame(1, Mailbox::where('student_id', $student->id)->where('template', 'emergency')->count());
        // once a month; the next one is never the same
        $this->actingAs($student)->get(route('dashboard'))->assertOk();
        $this->assertSame(1, Emergency::count());
        $this->assertNotSame($emergency->kind, EmergencyService::kindFor($student, 2));

        // whoever doesn't log in gets it on the month's last day
        $absent = $this->student(800);
        $this->setDay('2026-06-29');
        $this->assertNull(EmergencyService::arrive($absent, true));
        $this->setDay('2026-06-30');
        $this->assertInstanceOf(Emergency::class, EmergencyService::arrive($absent, true));

        // an admin can switch them off for the level
        $off = $this->student(800);
        \App\Models\EmergencySetting::create(['grade_level_id' => GradeLevel::where('name', 'Intermediate')->value('id'), 'enabled' => false, 'months_a' => '3', 'months_b' => '5']);
        $this->assertNull(EmergencyService::arrive($off));
    }

    public function test_a_health_one_costs_what_the_insurance_leaves(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student(800);
        InsuranceService::choose($student, 'basic');
        $arm = Emergency::create(['user_id' => $student->id, 'number' => 1, 'month_in_level' => 3, 'kind' => 'arm', 'cost' => 800,
            'amount' => InsuranceService::studentPart('basic', 800), 'plan' => 'basic', 'arrived_at' => now(), 'due_on' => now()->addDays(29)]);
        $this->assertSame([590.0, 'covered'], [(float) $arm->amount, $arm->cover()]);
        $filling = new Emergency(['kind' => 'filling', 'cost' => 250, 'amount' => 250, 'plan' => 'basic']);
        $this->assertSame('deductible', $filling->cover());
        $this->assertSame('waiting', (new Emergency(['kind' => 'filling', 'cost' => 250, 'amount' => 250, 'plan' => 'basic', 'waiting' => true]))->cover());
        $this->assertSame('none', (new Emergency(['kind' => 'arm', 'cost' => 800, 'amount' => 800]))->cover());
        $this->assertNull((new Emergency(['kind' => 'bike', 'cost' => 200, 'amount' => 200]))->cover());

        // paid with the fund: the question's right answer is what they paid
        $this->assertNull(EmergencyService::pay($student, $arm, 'fund'));
        $this->assertSame(210.0, SavingsLedger::balance(BankAccount::where('student_id', $student->id)->first(), 'emergency'));
        $question = EmergencyService::question($arm->fresh());
        $this->assertSame(zeds(590), $question['options'][$question['answer']]);
        $this->assertSame(__('emergency.why_insured', ['cost' => zeds(800), 'plan' => __('insurance.plan_basic'), 'covered' => zeds(210), 'total' => zeds(590)]), $question['why']);
    }

    public function test_the_ways_to_pay_and_what_each_costs(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student(300, 'Intermediate', 3952.40);
        $emergency = Emergency::create(['user_id' => $student->id, 'number' => 1, 'month_in_level' => 3, 'kind' => 'trip', 'cost' => 600,
            'amount' => 600, 'arrived_at' => now(), 'due_on' => now()->addDays(29)]);
        $options = EmergencyService::options($emergency, $student);
        // 300 in the fund: not enough alone - all of it and 300 from the current account; the current account; loans
        $this->assertSame(['fund_current', 'current', 'loan_6', 'loan_12'], array_keys($options));
        $this->assertSame([300.0, 300.0], [$options['fund_current']['fund'], $options['fund_current']['current']]);
        $this->assertSame(600.0, $options['current']['total']);
        $this->assertGreaterThan($options['loan_6']['total'], $options['loan_12']['total']);   // longer, dearer
        $this->assertSame('emergency_option', EmergencyService::pay($student, $emergency, 'fund'));

        // with a loan: 600 over 6 months, the interest on top
        $before = $this->balance($student);
        $this->assertNull(EmergencyService::pay($student, $emergency, 'loan_6'));
        $emergency->refresh();
        $loan = Loan::findOrFail($emergency->loan_id);
        $this->assertSame([600.0, 6], [(float) $loan->amount, (int) $loan->months]);
        $this->assertSame($before, $this->balance($student));   // the loan in, the trip out
        $this->assertSame(round(600 + (float) $loan->interest_total, 2), $emergency->realCost());
        $this->assertSame('emergency_done', EmergencyService::pay($student, $emergency, 'current'));

        // the questions: the real cost, with the interest, and something to change
        $question = EmergencyService::question($emergency);
        $wrong = ($question['answer'] + 1) % count($question['options']);
        $this->assertSame('cost_wrong', EmergencyService::answer($student, $emergency, $wrong, 'save_more'));
        $this->assertSame('pick_change', EmergencyService::answer($student, $emergency, $question['answer'], 'insurance'));   // not a health one
        $this->assertNull(EmergencyService::answer($student, $emergency, $question['answer'], 'save_more'));
        $this->assertNotNull($emergency->fresh()->completed_at);
        $this->assertNull(EmergencyService::open($student->id));
    }

    public function test_not_paid_in_30_days_it_is_taken_into_overdraft(): void
    {
        $this->setDay('2026-06-10');
        $rich = $this->student();
        $poor = $this->student(0, 'Intermediate', 100);
        foreach ([$rich, $poor] as $student) {
            Emergency::create(['user_id' => $student->id, 'number' => 1, 'month_in_level' => 3, 'kind' => 'phone', 'cost' => 300,
                'amount' => 300, 'arrived_at' => now(), 'due_on' => now()->addDays(29)]);
        }
        $this->setDay('2026-07-09');   // the last of the 30 days
        $this->assertSame(0, EmergencyService::chargeDue($rich));

        $this->setDay('2026-07-10');
        $this->assertSame(1, EmergencyService::chargeDue($rich));
        $this->assertSame(['auto', 'current'], [Emergency::where('user_id', $rich->id)->value('status'), Emergency::where('user_id', $rich->id)->value('method')]);
        $this->assertSame(1, EmergencyService::chargeDue($poor));
        $this->assertSame(-200.0, $this->balance($poor));
        $this->assertSame('overdraft', Emergency::where('user_id', $poor->id)->value('method'));
        $this->assertSame(1, Mailbox::where('student_id', $poor->id)->where('template', 'overdraft')->count());
        $this->assertSame(0, EmergencyService::chargeDue($poor));
    }

    public function test_the_pages(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student(800);
        $student->forceFill(['preferred_language' => 'es'])->save();
        $this->actingAs($student)->get(route('emergency.show'))->assertOk()->assertSee('Resuelve tu imprevisto')->assertSee('¿Cómo lo pagas?');
        $emergency = Emergency::where('user_id', $student->id)->firstOrFail();   // the visit brought it
        $this->actingAs($student)->get(route('bank.index'))->assertOk()->assertSee(__('emergency.bank_go', [], 'es'));
        $this->actingAs($student)->get(route('education.educational_finance_department'))->assertOk()->assertSee('Resuelve tu imprevisto');

        $this->actingAs($student)->post(route('emergency.pay'), ['emergency' => $emergency->id, 'option' => 'fund'])->assertRedirect(route('emergency.show'));
        $this->assertSame('paid', $emergency->fresh()->status);
        $question = EmergencyService::question($emergency->fresh());
        $this->actingAs($student)->get(route('emergency.show'))->assertOk()->assertSee('¿Qué harías distinto la próxima vez?');
        $this->actingAs($student)->post(route('emergency.answers'), ['emergency' => $emergency->id, 'cost' => $question['answer'], 'change' => 'nothing'])
            ->assertRedirect(route('emergency.show'));
        $this->actingAs($student)->get(route('emergency.show'))->assertOk()->assertSee('Hecho: imprevisto resuelto.');
        $this->actingAs($this->student(800, 'Rookie'))->get(route('emergency.show'))->assertNotFound();

        // Admin > Intermediate > Emergencies
        $admin = User::factory()->create(['role' => 1]);
        $intermediate = GradeLevel::where('name', 'Intermediate')->value('id');
        $this->actingAs($admin)->get(route('admin.emergencies.index', ['level' => $intermediate]))->assertOk()
            ->assertSee('Emergencies')->assertSee('3, 6, 9')->assertSee(number_format((float) $emergency->amount, 2) . ' Ƶ');
        $this->actingAs($admin)->post(route('admin.emergencies.update', ['level' => $intermediate]),
            ['enabled' => 1, 'months_a' => '2, 5, 8', 'months_b' => '4,6,9', 'frauds_enabled' => 0, 'call_month' => 4, 'email_month' => 7])
            ->assertSessionHas('success');
        $this->assertSame(['enabled' => true, 'A' => [2, 5, 8], 'B' => [4, 6, 9], 'frauds' => false, 'call' => 4, 'email' => 7], EmergencyService::settings($intermediate));
    }
}
