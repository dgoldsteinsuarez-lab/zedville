<?php

namespace Tests\Feature;

use App\Models\AutoDebitRequest;
use App\Models\BankAccount;
use App\Models\Emergency;
use App\Models\EmergencySetting;
use App\Models\Fraud;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\Mailbox;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\EmergencyService;
use App\Services\FraudService;
use App\Services\LedgerService;
use App\Services\MonthEndPenaltyService;
use App\Services\PriceRise;
use App\Services\SavingsLedger;
use App\Services\ZedDirecto;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The frauds, from the Intermediate (proposal «Fraudes», step 15): a call on
 * screen in month 4 of the level, a fake email from the Education Finance
 * Department in month 7; spotting it earns a point, falling for it costs 5 %
 * to 25 % of the salary, covered with the fund or a loan.
 */
class FraudTest extends TestCase
{
    use RefreshDatabase;

    private function setDay(string $date): void
    {
        Carbon::setTestNow(Carbon::parse($date . ' 10:00:00', 'Europe/Berlin'));
        Level::flush();
        PriceRise::flush();
        ZedDirecto::flush();
    }

    /** A student whose class started the level on 1 April 2026 (month 4: July; month 7: October). */
    private function student(string $level = 'Intermediate', float $fund = 0): User
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
        LedgerService::post($user->id, 'credit', 'Income', 3952.40, 'Monthly Salary Credit', ['sid' => '1', 'bank_account_id' => $account->id, 'transaction_date' => now()->startOfMonth()->addDays(5)]);

        return $user;
    }

    public function test_the_call_in_month_4_and_the_email_in_month_7(): void
    {
        $this->setDay('2026-06-10');
        $student = $this->student();
        $this->assertNull(FraudService::arrive($student));   // month 3: nothing

        $this->setDay('2026-07-03');
        $call = FraudService::arrive($student);
        $this->assertSame(['call', 4, 'open'], [$call->channel, $call->month_in_level, $call->status]);
        $this->assertContains($call->script, FraudService::SCRIPTS['call']);
        $this->assertNull(FraudService::arrive($student));   // once
        $this->assertSame($call->id, FraudService::ringing($student)->id);

        $this->setDay('2026-10-02');
        $email = FraudService::arrive($student);
        $this->assertSame('email', $email->channel);
        $mail = Mailbox::where('student_id', $student->id)->where('template', 'fraud-' . $email->script)->firstOrFail();
        $this->assertSame(__('mail.fraud-' . $email->script . '.subject'), $mail->subject);
        $this->assertStringContainsString(FraudService::FAKE_SENDER, $mail->content);

        // not in the Rookie, and an admin can switch them off
        $this->assertNull(FraudService::arrive($this->student('Rookie')));
        $off = $this->student();
        EmergencySetting::create(['grade_level_id' => GradeLevel::where('name', 'Intermediate')->value('id'), 'enabled' => true,
            'months_a' => '3,6,9', 'months_b' => '5,7,9', 'frauds_enabled' => false, 'call_month' => 4, 'email_month' => 7]);
        $this->assertNull(FraudService::arrive($off));
    }

    public function test_hanging_up_earns_a_point_and_falling_costs_like_an_emergency(): void
    {
        $this->setDay('2026-07-10');
        $wise = $this->student();
        $this->actingAs($wise)->get(route('dashboard'))->assertOk()->assertSee(__('fraud.call_incoming'));   // it rings
        $call = Fraud::where('user_id', $wise->id)->firstOrFail();
        $safe = FraudService::ANSWERS[$call->script][0];
        $this->actingAs($wise)->postJson(route('fraud.call', $call), ['answer' => $safe])->assertOk()
            ->assertJson(['status' => 'safe', 'title' => __('fraud.safe_title'), 'resolve' => null]);
        $this->assertSame(1, DB::table('participation_logs')->where('student_id', $wise->id)->where('action_detail', 'fraud_spotted')->count());
        $this->assertNull(FraudService::ringing($wise));
        // answered once: another answer changes nothing
        $this->actingAs($wise)->postJson(route('fraud.call', $call), ['answer' => FraudService::ANSWERS[$call->script][1]])->assertJson(['status' => 'safe']);

        $fooled = $this->student('Intermediate', 300);
        FraudService::arrive($fooled);
        $call = Fraud::where('user_id', $fooled->id)->firstOrFail();
        $this->actingAs($fooled)->postJson(route('fraud.call', $call), ['answer' => FraudService::ANSWERS[$call->script][1]])->assertOk()
            ->assertJson(['status' => 'fell', 'title' => __('fraud.fell_title'), 'resolve' => route('emergency.show')]);
        $loss = Emergency::where('user_id', $fooled->id)->where('kind', 'fraud')->firstOrFail();
        $salary = MonthEndPenaltyService::salary();
        $this->assertGreaterThanOrEqual(round($salary * 0.05 / 10) * 10, (float) $loss->amount);
        $this->assertLessThanOrEqual(round($salary * 0.25 / 10) * 10, (float) $loss->amount);
        $this->assertSame(0.0, fmod((float) $loss->amount, 10));
        $this->assertSame(1, Mailbox::where('student_id', $fooled->id)->where('template', 'fraud-loss')->count());
        // covered with the fund (300 in it) or a loan only - never the current account
        $this->assertSame((float) $loss->amount <= 300 ? ['fund', 'loan_6', 'loan_12'] : ['loan_6', 'loan_12'], array_keys(EmergencyService::options($loss, $fooled)));
        $this->assertSame(EmergencyService::CHANGES_FRAUD, EmergencyService::changes($loss));
    }

    public function test_the_fake_email_its_page_and_the_report(): void
    {
        $this->setDay('2026-10-02');
        $fooled = $this->student();
        $wise = $this->student();
        foreach ([$fooled, $wise] as $student) {
            $this->actingAs($student)->get(route('dashboard'))->assertOk();
        }
        $fake = Fraud::where('user_id', $fooled->id)->where('channel', 'email')->firstOrFail();
        $this->actingAs($fooled)->get(route('fraud.fake', $fake->token))->assertOk()->assertSee('zedvi11e')->assertSee(__('fraud.fake_title'));
        $this->actingAs($wise)->get(route('fraud.fake', $fake->token))->assertNotFound();   // only its own
        $this->actingAs($fooled)->post(route('fraud.verify', $fake->token))->assertRedirect(route('fraud.lesson', $fake->token));
        $this->assertSame('fell', $fake->fresh()->status);
        $this->actingAs($fooled)->get(route('fraud.lesson', $fake->token))->assertOk()->assertSee(__('fraud.fell_title'))->assertSee(__('fraud.go_resolve'));
        $this->actingAs($fooled)->get(route('fraud.fake', $fake->token))->assertRedirect(route('fraud.lesson', $fake->token));

        $mail = Fraud::where('user_id', $wise->id)->where('channel', 'email')->firstOrFail();
        $this->actingAs($wise)->post(route('fraud.report', $mail->token))->assertRedirect(route('fraud.lesson', $mail->token));
        $this->assertSame('safe', $mail->fresh()->status);
        $this->actingAs($wise)->get(route('fraud.lesson', $mail->token))->assertOk()->assertSee(__('fraud.safe_title'));
        $this->assertSame(0, Emergency::where('user_id', $wise->id)->count());

        // month 7 is also B's second emergency: the fraud's loss doesn't stop it
        $this->setDay('2026-10-08');
        $emergency = EmergencyService::arrive($fooled);
        $this->assertInstanceOf(Emergency::class, $emergency);
        $this->assertSame(2, $emergency->number);
        $this->assertNotSame(EmergencyService::FRAUD, $emergency->kind);
    }

    public function test_the_spanish_texts_the_bank_help_and_the_admin(): void
    {
        $this->setDay('2026-07-10');
        $student = $this->student();
        $student->forceFill(['preferred_language' => 'es'])->save();
        $this->actingAs($student)->get(route('dashboard'))->assertOk()->assertSee('Llamada entrante')->assertSee('¿Qué haces?');
        $this->actingAs($student)->get(route('bank.help'))->assertOk()->assertSee(FraudService::BANK_PHONE)->assertSee('El teléfono de Universal Bank');

        $admin = User::factory()->create(['role' => 1]);
        $intermediate = GradeLevel::where('name', 'Intermediate')->value('id');
        $this->actingAs($admin)->get(route('admin.emergencies.index', ['level' => $intermediate]))->assertOk()
            ->assertSee('Frauds')->assertSee('Not answered yet');
        $this->actingAs($admin)->post(route('admin.emergencies.update', ['level' => $intermediate]),
            ['enabled' => 1, 'months_a' => '3,6,9', 'months_b' => '5,7,9', 'frauds_enabled' => 1, 'call_month' => 5, 'email_month' => 8])
            ->assertSessionHas('success');
        $this->assertSame([true, 5, 8], array_values(array_intersect_key(EmergencyService::settings($intermediate), array_flip(['frauds', 'call', 'email']))));
    }
}
