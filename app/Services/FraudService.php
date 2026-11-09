<?php

namespace App\Services;

use App\Models\Emergency;
use App\Models\Fraud;
use App\Models\Mailbox;
use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Support\Hybrid;
use App\Support\Level;
use Illuminate\Support\Str;

/**
 * The frauds, from the Intermediate (proposal «Fraudes», step 15; Daniel,
 * 2026-11-08: "correo falso que no viene del departamento de finanzas
 * educativas, y «llamada» en pantalla tras la pregunta del día o el medidor de
 * ánimo"), copied from the commonest real ones: a «call» on screen in the
 * student's month 4 of the level - with the bank's own number, which can be
 * faked (spoofing) - and in month 7 a fake email from the Education Finance
 * Department, its address with a 1 for an l, in a hurry, with a link to a fake
 * page that asks for nothing real. Each student gets one of the scripts and
 * chooses what to do: hanging up or reporting it earns a participation point;
 * falling for it costs an amount like an emergency's (5 % to 25 % of the
 * salary), which they cover with their Emergency Fund or a loan in «Resuelve
 * tu imprevisto» (EmergencyService, kind 'fraud'), with Universal Bank's
 * letter explaining what happened. An admin may switch them off or move the
 * months per level (Admin > level > Emergencies).
 */
class FraudService
{
    public const SCRIPTS = [
        'call' => ['bank_code', 'safe_account', 'tech_support', 'family'],
        'email' => ['verify', 'refund', 'prize', 'parcel', 'job'],
    ];

    /** Each call's answers: the safe one first, then the two that fall for it. */
    public const ANSWERS = [
        'bank_code' => ['hang_up_call_bank', 'give_code', 'ask_then_give'],
        'safe_account' => ['hang_up_safe', 'move_all', 'move_half'],
        'tech_support' => ['hang_up_tech', 'install', 'pay_fix'],
        'family' => ['call_usual', 'transfer', 'ask_amount'],
    ];

    /** The amounts the scripts name (FraudService texts): the fake charge, the refund, the fees, the pay. */
    public const AMOUNTS = ['charge' => 480.0, 'refund' => 450.0, 'repair' => 49.99, 'prize_fee' => 5.99, 'parcel_fee' => 1.79, 'pay' => 50.0];

    /** Universal Bank's real number - the one a spoofed call shows, and the one to call back (bank help); it never breaks across lines. */
    public const BANK_PHONE = '900' . "\u{00A0}" . '555' . "\u{00A0}" . '010';

    /** The fake email's address: the department's, with a 1 for an l. */
    public const FAKE_SENDER = 'educacion-financiera@zedvi11e.zv';

    /** The loss, as a share of the salary: like an emergency's (proposal: 5 % to 25 %). */
    public const LOSS_MIN = 5;
    public const LOSS_MAX = 25;

    /** The fraud this month brings the student by $channel, its month in the level, or null. */
    public static function dueMonth(User $user, string $channel): ?int
    {
        if (!Level::saver($user) || !$user->grade) {
            return null;
        }
        $settings = EmergencyService::settings(EmergencyService::levelId($user));
        $month = IntermediateClassService::studentMonth($user);

        return $settings['frauds'] && $month && $month === (int) $settings[$channel] ? $month : null;
    }

    /** Brings this month's call or email, if it is due and hasn't come. */
    public static function arrive(User $user): ?Fraud
    {
        foreach (array_keys(self::SCRIPTS) as $channel) {
            $month = self::dueMonth($user, $channel);
            if (!$month || Fraud::where('user_id', $user->id)->where('channel', $channel)->where('month_in_level', $month)->exists()) {
                continue;
            }
            $scripts = self::SCRIPTS[$channel];
            $fraud = Fraud::create([
                'user_id' => $user->id, 'sid' => $user->sid, 'channel' => $channel,
                'script' => $scripts[Activities::random('fraud:' . $channel, $user->id)->between(0, count($scripts) - 1)],
                'month_in_level' => $month, 'token' => Str::random(40), 'arrived_at' => now(),
            ]);
            if ($channel === 'email') {
                $data = ['fraud' => $fraud->id];
                Mailbox::create([
                    'student_id' => $user->id,
                    'sid' => $user->sid,
                    'subject' => 'Urgent: verify your account',
                    'content' => Hybrid::without(fn () => view('mailbox_templates.fraud-' . $fraud->script, $data + ['user' => $user])->render()),
                    'type' => 'primary',
                    'template' => 'fraud-' . $fraud->script,
                    'template_data' => $data,
                    'read' => 0,
                ]);
            }

            return $fraud;
        }

        return null;
    }

    /**
     * What happened, in the student's language: ['status', 'title', 'text', 'name',
     * 'sign' (null for hybrid classes: they are only informed), 'good', 'slogan', 'resolve'].
     */
    public static function lesson(Fraud $fraud): array
    {
        $safe = $fraud->status === 'safe';
        $emergency = $fraud->emergency_id ? Emergency::find($fraud->emergency_id) : null;

        return [
            'status' => $fraud->status,
            'title' => __($safe ? 'fraud.safe_title' : 'fraud.fell_title'),
            'text' => $safe ? __('fraud.safe_text')
                : __('fraud.fell_text', ['amount' => zeds((float) optional($emergency)->amount), 'activity' => __('emergency.title')]),
            'name' => __('fraud.name', ['name' => __('fraud.names.' . $fraud->script)]),
            'sign' => Hybrid::on() ? null : __('fraud.signs.' . $fraud->script),
            'good' => __('fraud.good.' . $fraud->script, ['phone' => self::BANK_PHONE]),
            'slogan' => __('fraud.slogan'),
            'resolve' => $safe ? null : route('emergency.show'),
        ];
    }

    /** The texts' amounts, formatted for the reader: :charge, :refund, :repair, :prize_fee, :parcel_fee, :pay. */
    public static function amounts(): array
    {
        return array_map(fn ($amount) => zeds($amount), self::AMOUNTS);
    }

    /** The call still ringing for the student (answered, it stops). */
    public static function ringing(?User $user): ?Fraud
    {
        return $user ? Fraud::where('user_id', $user->id)->where('channel', 'call')->where('status', 'open')->latest('id')->first() : null;
    }

    /** A call's answers in the student's order: [key => falls?]. */
    public static function answers(Fraud $fraud): array
    {
        $keys = Activities::random('fraud_answers:' . $fraud->id, (int) $fraud->user_id)->shuffle(self::ANSWERS[$fraud->script]);

        return array_combine($keys, array_map(fn ($key) => $key !== self::ANSWERS[$fraud->script][0], $keys));
    }

    /** What falling costs: 5 % to 25 % of the salary, in tens, always the same for the same fraud. */
    public static function loss(Fraud $fraud): float
    {
        $pct = Activities::random('fraud_loss:' . $fraud->id, (int) $fraud->user_id)->between(self::LOSS_MIN, self::LOSS_MAX);

        return round(MonthEndPenaltyService::salary() * $pct / 100 / 10) * 10;
    }

    /**
     * The student's answer to a call ('hang_up'...), or to the email: 'report'
     * (safe) or 'verify' (the fake page's button: falls). The fraud, settled.
     */
    public static function answer(User $user, Fraud $fraud, string $answer): Fraud
    {
        if ((int) $fraud->user_id !== (int) $user->id || $fraud->status !== 'open') {
            return $fraud;
        }
        $falls = $fraud->channel === 'call' ? (self::answers($fraud)[$answer] ?? null) : ['report' => false, 'verify' => true][$answer] ?? null;
        if ($falls === null) {
            return $fraud;
        }
        if (!$falls) {
            $fraud->fill(['status' => 'safe', 'answer' => $answer, 'answered_at' => now()])->save();
            app(ParticipationService::class)->award($user->id, 'fraud_spotted', 'fraud:' . $fraud->id);

            return $fraud;
        }

        $loss = self::loss($fraud);
        $emergency = Emergency::create([
            'user_id' => $user->id, 'sid' => $user->sid, 'number' => 0, 'month_in_level' => $fraud->month_in_level,
            'kind' => EmergencyService::FRAUD, 'cost' => $loss, 'amount' => $loss,
            'arrived_at' => now(), 'due_on' => now()->addDays(EmergencyService::DAYS - 1)->toDateString(),
        ]);
        $fraud->fill(['status' => 'fell', 'answer' => $answer, 'emergency_id' => $emergency->id, 'answered_at' => now()])->save();
        $data = ['fraud' => $fraud->id];
        Mailbox::create([
            'student_id' => $user->id,
            'sid' => $user->sid,
            'subject' => 'You have been the victim of a fraud',
            'content' => Hybrid::without(fn () => view('mailbox_templates.fraud-loss', $data + ['user' => $user])->render()),
            'type' => 'primary',
            'template' => 'fraud-loss',
            'template_data' => $data,
            'read' => 0,
        ]);

        return $fraud;
    }
}
