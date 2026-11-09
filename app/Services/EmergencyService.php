<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Emergency;
use App\Models\EmergencySetting;
use App\Models\GradeLevel;
use App\Models\Loan;
use App\Models\Mailbox;
use App\Models\User;
use App\Services\MoneyFactory\Activities;
use App\Support\Hybrid;
use App\Support\Level;
use Illuminate\Support\Facades\DB;

/**
 * The emergencies, from the Intermediate (proposal «El imprevisto», step 13;
 * Daniel, 2026-11-08: "de acuerdo"): three in the school year, one a term -
 * the student's months 3, 6 and 9 in the level in scenario A (they bring a
 * fund from the Rookie), 5, 7 and 9 in B (they start one) - an admin may
 * change them, or switch them off, per level. The emergency arrives the first
 * time the student comes in that month after the salary and the bills (from
 * the 7th) - or on its last day if they don't - with a letter (and so the
 * bell), and «Resuelve tu imprevisto» on the department's board, with 30 days
 * to pay it. Each student gets a different one, always the same for the same
 * student, between 5 % and 25 % of the salary; a health one costs what their
 * insurance leaves them (InsuranceService). They pay with their Emergency
 * Fund, part fund and the rest from the current account, the current account,
 * or a loan, seeing what each costs in all; then two short questions. Not
 * paid in 30 days, it is taken from the current account and, without the
 * money, into overdraft (Overdraft) - no fine: the overdraft is the
 * consequence.
 */
class EmergencyService
{
    public const KINDS = [
        'arm' => ['cost' => 800.0, 'health' => true, 'dentist' => false, 'icon' => '🦴'],
        'filling' => ['cost' => 250.0, 'health' => true, 'dentist' => true, 'icon' => '🦷'],
        'washing_machine' => ['cost' => 450.0, 'health' => false, 'dentist' => false, 'icon' => '🧺'],
        'phone' => ['cost' => 300.0, 'health' => false, 'dentist' => false, 'icon' => '📱'],
        'bike' => ['cost' => 200.0, 'health' => false, 'dentist' => false, 'icon' => '🚲'],
        'trip' => ['cost' => 600.0, 'health' => false, 'dentist' => false, 'icon' => '🚆'],
        // not one that arrives: what falling for a fraud cost, paid the same way (FraudService), its cost the loss
        'fraud' => ['cost' => 0.0, 'health' => false, 'dentist' => false, 'icon' => '🎣'],
    ];

    /** The current account's and the fund's lines (lang/<locale>/transactions.php 'lines'). */
    public const LINES = [
        'arm' => 'Unexpected expense: broken arm',
        'filling' => 'Unexpected expense: dental filling',
        'washing_machine' => 'Unexpected expense: washing machine',
        'phone' => 'Unexpected expense: phone screen',
        'bike' => 'Unexpected expense: bike',
        'trip' => 'Unexpected expense: family trip',
        'fraud' => 'Fraud: money stolen',
    ];

    /** The kind that isn't an emergency but a fraud's loss. */
    public const FRAUD = 'fraud';

    /** The student's months in the level, by scenario (IntermediateClassService). */
    public const MONTHS = ['A' => [3, 6, 9], 'B' => [5, 7, 9]];

    public const DAYS = 30;

    /** After the salary (the 6th) and the bills (the 7th). */
    public const DAY = 7;

    /** The loan terms offered to pay one (LoanService::TERMS, the shorter ones). */
    public const LOAN_TERMS = [6, 12];

    /** What the student may answer to "what would you do differently?" (insurance: only for a health one). */
    public const CHANGES = ['save_more', 'insurance', 'compare', 'nothing'];

    /** The same, after a fraud. */
    public const CHANGES_FRAUD = ['hang_up', 'check_sender', 'never_share', 'save_more'];

    /** The frauds' months by default (FraudService): the call in month 4, the fake email in month 7. */
    public const FRAUD_MONTHS = ['call' => 4, 'email' => 7];

    /**
     * A level's settings: the emergencies on (by default the Intermediate only)
     * and the months of A and B; the frauds on, and the months of the call and
     * of the email.
     */
    public static function settings(?int $levelId): array
    {
        $row = $levelId ? EmergencySetting::where('grade_level_id', $levelId)->first() : null;
        if (!$row) {
            $order = $levelId ? (int) GradeLevel::whereKey($levelId)->value('sort_order') : 0;

            return ['enabled' => $order === Level::INTERMEDIATE] + self::MONTHS
                + ['frauds' => $order === Level::INTERMEDIATE] + self::FRAUD_MONTHS;
        }

        return ['enabled' => $row->enabled, 'A' => self::months($row->months_a), 'B' => self::months($row->months_b),
            'frauds' => (bool) $row->frauds_enabled, 'call' => (int) $row->call_month, 'email' => (int) $row->email_month];
    }

    /** "3, 6,9" → [3, 6, 9]: months from 1 to 12, in order, each once. */
    public static function months(?string $text): array
    {
        $months = array_map('intval', preg_split('/[^0-9]+/', (string) $text, -1, PREG_SPLIT_NO_EMPTY));
        $months = array_values(array_unique(array_filter($months, fn ($m) => $m >= 1 && $m <= 12)));
        sort($months);

        return $months;
    }

    public static function levelId(User $user): ?int
    {
        $id = optional(optional($user->gradeRelation)->grade)->grade_level_id;

        return $id ? (int) $id : null;
    }

    /** The emergency this month would bring the student, ['number', 'month'], or null. */
    public static function dueNow(User $user): ?array
    {
        if (!Level::saver($user) || !$user->grade) {
            return null;
        }
        $settings = self::settings(self::levelId($user));
        $month = IntermediateClassService::studentMonth($user);
        if (!$settings['enabled'] || !$month) {
            return null;
        }
        $i = array_search($month, $settings[IntermediateClassService::scenarioFor($user)] ?? [], true);

        return $i === false ? null : ['number' => $i + 1, 'month' => $month];
    }

    /** Which one: seeded by the student and the number, never one they already had. */
    public static function kindFor(User $user, int $number): string
    {
        $all = array_values(array_diff(array_keys(self::KINDS), [self::FRAUD]));
        $had = Emergency::where('user_id', $user->id)->pluck('kind')->all();
        $kinds = array_values(array_diff($all, $had)) ?: $all;

        return $kinds[Activities::random('emergency:' . $number, $user->id)->between(0, count($kinds) - 1)];
    }

    /**
     * Brings the month's emergency, if it is one and it hasn't come: when the
     * student comes in from the 7th, or ($monthEnd) on the month's last day
     * from the scheduler. The emergency, or null.
     */
    public static function arrive(User $user, bool $monthEnd = false): ?Emergency
    {
        $now = now();
        if ($now->day < self::DAY || ($monthEnd && !$now->isLastOfMonth()) || !($due = self::dueNow($user))
            || Emergency::where('user_id', $user->id)->where('kind', '!=', self::FRAUD)
                ->whereYear('arrived_at', $now->year)->whereMonth('arrived_at', $now->month)->exists()) {
            return null;
        }
        $kind = self::kindFor($user, $due['number']);
        $k = self::KINDS[$kind];
        $policy = $k['health'] ? InsuranceService::current($user->id) : null;
        $amount = $k['health'] ? InsuranceService::studentPart($policy?->plan, $k['cost'], $k['dentist'], $policy?->started_on) : $k['cost'];
        $wait = $policy ? InsuranceService::PLANS[$policy->plan]['dentist_wait'] : 0;
        $waiting = $k['dentist'] && $wait > 0 && $now->lt($policy->started_on->copy()->addMonthsNoOverflow($wait));

        $emergency = Emergency::create([
            'user_id' => $user->id, 'sid' => $user->sid, 'number' => $due['number'], 'month_in_level' => $due['month'],
            'kind' => $kind, 'cost' => $k['cost'], 'amount' => $amount, 'plan' => $policy?->plan, 'waiting' => $waiting,
            'arrived_at' => $now, 'due_on' => $now->copy()->addDays(self::DAYS - 1)->toDateString(),
        ]);
        $data = ['emergency' => $emergency->id];
        Mailbox::create([
            'student_id' => $user->id,
            'sid' => $user->sid,
            'subject' => 'An unexpected expense',
            'content' => Hybrid::without(fn () => view('mailbox_templates.emergency', $data + ['user' => $user])->render()),
            'type' => 'primary',
            'template' => 'emergency',
            'template_data' => $data,
            'read' => 0,
        ]);

        return $emergency;
    }

    /** The one the student is working on: not paid, or paid without the questions, within its days. */
    public static function open(int $userId): ?Emergency
    {
        return Emergency::where('user_id', $userId)
            ->where(fn ($q) => $q->where('status', 'pending')
                ->orWhere(fn ($q) => $q->where('status', 'paid')->whereNull('completed_at')->whereDate('due_on', '>=', today())))
            ->latest('id')->first();
    }

    /** The latest one, for the student's page. */
    public static function latest(int $userId): ?Emergency
    {
        return Emergency::where('user_id', $userId)->latest('id')->first();
    }

    /**
     * How the student can pay it now and what each costs in all: 'fund',
     * 'fund_current' (all the fund, the rest from the current account),
     * 'current', and 'loan_6' / 'loan_12' (at least LoanService::MIN, the
     * interest on top), each ['total', 'fund', 'current', 'loan', 'months', 'instalment', 'interest'].
     */
    public static function options(Emergency $emergency, User $user): array
    {
        $account = BankAccount::where('student_id', $user->id)->first();
        if (!$account || $emergency->status !== 'pending') {
            return [];
        }
        $amount = (float) $emergency->amount;
        $fund = SavingsLedger::isOpen($account, BankTransferService::EMERGENCY) ? SavingsLedger::balance($account, BankTransferService::EMERGENCY) : 0.0;
        $current = BankTransferService::balance($account, BankTransferService::CURRENT);
        $none = ['fund' => 0.0, 'current' => 0.0, 'loan' => 0.0, 'months' => null, 'instalment' => 0.0, 'interest' => 0.0];
        $options = [];
        // a fraud's loss: only the fund or a loan (Daniel, 2026-11-08: "lo paga con su fondo o con un préstamo")
        $fraud = $emergency->kind === self::FRAUD;
        if ($fund >= $amount) {
            $options['fund'] = ['total' => $amount, 'fund' => $amount] + $none;
        } elseif (!$fraud && $fund > 0 && $fund + $current >= $amount) {
            $options['fund_current'] = ['total' => $amount, 'fund' => $fund, 'current' => round($amount - $fund, 2)] + $none;
        }
        if (!$fraud && $current >= $amount) {
            $options['current'] = ['total' => $amount, 'current' => $amount] + $none;
        }
        $loan = max((float) LoanService::MIN, ceil($amount));
        foreach (self::LOAN_TERMS as $months) {
            if (LoanService::refusal($user, $loan, $months) === null) {
                $quote = LoanService::quote($loan, $months);
                $options['loan_' . $months] = ['total' => round($amount + $quote['interest'], 2), 'loan' => $loan, 'months' => $months,
                    'instalment' => $quote['instalment'], 'interest' => $quote['interest']] + $none;
            }
        }

        return $options;
    }

    /** Pays it the way the student chose. Null, or why not (a flash.* key). */
    public static function pay(User $user, Emergency $emergency, string $option): ?string
    {
        if ((int) $emergency->user_id !== (int) $user->id || $emergency->status !== 'pending') {
            return 'emergency_done';
        }
        $o = self::options($emergency, $user)[$option] ?? null;
        if (!$o) {
            return 'emergency_option';
        }
        $line = self::LINES[$emergency->kind];
        $meta = ['emergency' => $emergency->id];

        try {
            DB::transaction(function () use ($user, $emergency, $option, $o, $line, $meta) {
                if ($o['loan'] > 0) {
                    $loan = LoanService::grant($user, $o['loan'], $o['months']);
                    if (!$loan instanceof Loan) {
                        throw new \DomainException($loan);   // the bank's refusal
                    }
                    $o['current'] = (float) $emergency->amount;
                    $emergency->loan_id = $loan->id;
                    $emergency->interest = $o['interest'];
                }
                $account = BankAccount::where('student_id', $user->id)->lockForUpdate()->first();
                // the money is still there (nothing was spent since the page showed the options)
                if ($o['fund'] > SavingsLedger::balance($account, BankTransferService::EMERGENCY) + 0.001
                    || $o['current'] > BankTransferService::balance($account, BankTransferService::CURRENT) + 0.001) {
                    throw new \DomainException('emergency_option');
                }
                if ($o['fund'] > 0) {
                    SavingsLedger::post($account, BankTransferService::EMERGENCY, 'debit', 'Emergency', $o['fund'], $line, $meta);
                }
                if ($o['current'] > 0) {
                    LedgerService::post($user->id, 'debit', 'Needs', $o['current'], $line,
                        ['sid' => $user->sid, 'bank_account_id' => $account->id, 'meta' => $meta]);
                }
                $emergency->fill(['status' => 'paid', 'method' => $o['loan'] > 0 ? 'loan' : $option, 'paid_at' => now()])->save();
            });
        } catch (\DomainException $refused) {
            $emergency->refresh();

            return $refused->getMessage();
        }

        return null;
    }

    /**
     * After the 30 days, the ones not paid: from the current account and,
     * without the money, into overdraft. How many were taken.
     */
    public static function chargeDue(User $user): int
    {
        $taken = 0;
        foreach (Emergency::where('user_id', $user->id)->where('status', 'pending')->whereDate('due_on', '<', today())->get() as $emergency) {
            $account = BankAccount::where('student_id', $user->id)->first();
            $current = $account ? BankTransferService::balance($account, BankTransferService::CURRENT) : 0.0;
            $line = self::LINES[$emergency->kind];
            if ($current >= (float) $emergency->amount) {
                LedgerService::post($user->id, 'debit', 'Needs', (float) $emergency->amount, $line,
                    ['sid' => $user->sid, 'bank_account_id' => $account?->id, 'meta' => ['emergency' => $emergency->id]]);
                $method = 'current';
            } else {
                Overdraft::charge($user, (float) $emergency->amount, $line, ['emergency' => $emergency->id]);
                $method = 'overdraft';
            }
            $emergency->fill(['status' => 'auto', 'method' => $method, 'paid_at' => now()])->save();
            $taken++;
        }

        return $taken;
    }

    /** The question after paying: what did it really cost? With its right answer and why. */
    public static function question(Emergency $emergency): array
    {
        $real = $emergency->realCost();
        $others = array_values(array_unique(array_filter([
            (float) $emergency->cost !== $real ? zeds((float) $emergency->cost) : null,
            (float) $emergency->interest > 0 ? zeds((float) $emergency->amount) : null,
            zeds(round($real * 1.5, 2)),
            zeds(round($real / 2, 2)),
        ])));
        $options = [zeds($real)];
        foreach ($others as $other) {
            if (!in_array($other, $options, true) && count($options) < 3) {
                $options[] = $other;
            }
        }
        $k = self::KINDS[$emergency->kind];
        $why = match (true) {
            (float) $emergency->interest > 0 => __('emergency.why_loan', ['amount' => zeds((float) $emergency->amount),
                'interest' => zeds((float) $emergency->interest), 'total' => zeds($real)]),
            $k['health'] && (float) $emergency->amount < (float) $emergency->cost => __('emergency.why_insured', ['cost' => zeds((float) $emergency->cost),
                'plan' => __('insurance.plan_' . $emergency->plan), 'covered' => zeds((float) $emergency->cost - (float) $emergency->amount), 'total' => zeds($real)]),
            default => __('emergency.why_paid', ['total' => zeds($real)]),
        };

        return Activities::choice(Activities::random('emergency_q:' . $emergency->id, (int) $emergency->user_id), __('emergency.q_cost'), $options, ['why' => $why]);
    }

    /** The answers after paying: the cost right and something to change. Null, or why not (an emergency.* key). */
    public static function answer(User $user, Emergency $emergency, ?int $cost, ?string $change): ?string
    {
        if ((int) $emergency->user_id !== (int) $user->id || $emergency->status === 'pending') {
            return 'not_paid';
        }
        if (!in_array($change, self::changes($emergency), true)) {
            return 'pick_change';
        }
        if ($cost === null || !Activities::grade([self::question($emergency)], [$cost])[0]) {
            return 'cost_wrong';
        }
        $emergency->fill(['change' => $change, 'completed_at' => $emergency->completed_at ?? now()])->save();

        return null;
    }

    /** The answers to "what would you do differently?" for this one. */
    public static function changes(Emergency $emergency): array
    {
        if ($emergency->kind === self::FRAUD) {
            return self::CHANGES_FRAUD;
        }

        return array_values(array_filter(self::CHANGES, fn ($c) => $c !== 'insurance' || self::KINDS[$emergency->kind]['health']));
    }
}
