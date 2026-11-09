<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\InsurancePolicy;
use App\Models\Mailbox;
use App\Models\Transaction1;
use App\Models\User;
use App\Support\Hybrid;
use App\Support\Level;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The health insurance, from the Intermediate (proposal «El seguro médico»,
 * step 12; Daniel, 2026-11-08: "de acuerdo"). Zedville has no public health
 * care: two plans like private insurance. Basic is cheap and the student pays
 * a good part of each health problem; Complete costs more and covers almost
 * everything. The lesson: Basic is better if nothing serious happens,
 * Complete protects from the serious.
 *
 * Compulsory ("Obligatorio elegir uno al empezar el nivel; se puede cambiar
 * una vez al año", agreed): the student compares and chooses in FinHero Task
 * 15 «Elige tu seguro médico» (Finhero\Insurance\ChoosePlan). Whoever has no
 * plan when their first month of the level ends gets Basic from the City Hall,
 * with a letter, and may still choose once (as in Germany, where the employer
 * signs up whoever doesn't choose a health insurer). The premium comes with
 * the bills on the 7th - the first one as soon as the plan starts - and
 * doesn't rise with prices (PriceRise).
 *
 * A health problem of cost C: Basic pays the first 500 Ƶ (the deductible) and
 * 30 % of the rest (the co-payment); Complete, 10 % of all. Basic waits 3
 * months for the dentist; emergencies are always covered.
 */
class InsuranceService
{
    public const PLANS = [
        'basic' => ['premium' => 30.0, 'deductible' => 500.0, 'copay' => 0.30, 'dentist_wait' => 3],
        'complete' => ['premium' => 80.0, 'deductible' => 0.0, 'copay' => 0.10, 'dentist_wait' => 0],
    ];

    /** What the City Hall gives whoever doesn't choose. */
    public const DEFAULT = 'basic';

    /** The cases the student works out before choosing (the proposal's table). */
    public const CASES = ['none' => 0, 'filling' => 250, 'arm' => 800, 'appendicitis' => 4000];

    /** The FinHero Task where the student chooses. */
    public const TASK = 'task_15';

    /** With the bills (BankController::ensureMonthlyBills). */
    public const DAY = 7;

    /** The current account's line (lang/<locale>/transactions.php 'lines'). */
    public const LINE = 'Health insurance premium';

    public static function offered(?User $user): bool
    {
        return Level::saver($user);
    }

    public static function current(int $userId): ?InsurancePolicy
    {
        return InsurancePolicy::where('user_id', $userId)->whereNull('ended_on')->latest('id')->first();
    }

    /** What the student pays for a health problem of $cost with a plan (without one, all of it). */
    public static function studentPart(?string $plan, float $cost, bool $dentist = false, ?Carbon $since = null): float
    {
        $p = self::PLANS[$plan] ?? null;
        if (!$p || ($dentist && $since && $p['dentist_wait'] > 0 && now()->lt($since->copy()->addMonthsNoOverflow($p['dentist_wait'])))) {
            return round($cost, 2);
        }
        $deductible = min($cost, $p['deductible']);

        return round($deductible + ($cost - $deductible) * $p['copay'], 2);
    }

    /** A whole year with a plan and one problem of $cost: twelve premiums and the student's part. */
    public static function yearCost(string $plan, float $cost): float
    {
        return round(self::PLANS[$plan]['premium'] * 12 + self::studentPart($plan, $cost), 2);
    }

    /** From when the student may change their plan: a year after it started; now, if they have none or the City Hall gave it. */
    public static function changeFrom(?InsurancePolicy $policy): ?Carbon
    {
        return $policy && !$policy->auto ? $policy->started_on->copy()->addYear() : null;
    }

    public static function canChoose(int $userId): bool
    {
        $from = self::changeFrom(self::current($userId));

        return !$from || today()->gte($from);
    }

    /** Chooses (or changes) the plan. Null, or why not (a flash.* key). */
    public static function choose(User $user, string $plan): ?string
    {
        if (!self::offered($user) || !isset(self::PLANS[$plan])) {
            return 'insurance_not_offered';
        }
        $current = self::current($user->id);
        if ($current && $current->plan === $plan && !$current->auto) {
            return 'insurance_same';
        }
        if (!self::canChoose($user->id)) {
            return 'insurance_once_a_year';
        }
        DB::transaction(function () use ($user, $plan, $current) {
            if ($current && $current->plan === $plan) {
                // keeping the plan the City Hall gave: now it's the student's own choice
                $current->update(['auto' => false]);

                return;
            }
            $current?->update(['ended_on' => today()]);
            InsurancePolicy::create(['user_id' => $user->id, 'sid' => $user->sid, 'plan' => $plan, 'started_on' => today()]);
        });
        self::chargePremium($user);

        return null;
    }

    /**
     * Basic for whoever has no plan once their first month of the level is
     * over (IntermediateClassService::studentMonth), with the City Hall's
     * letter. True if it was given now.
     */
    public static function assignDefault(User $user): bool
    {
        if (!self::offered($user) || self::current($user->id) || (IntermediateClassService::studentMonth($user) ?? 0) < 2) {
            return false;
        }
        InsurancePolicy::create(['user_id' => $user->id, 'sid' => $user->sid, 'plan' => self::DEFAULT, 'auto' => true, 'started_on' => today()]);
        $data = ['plan' => self::DEFAULT];
        Mailbox::create([
            'student_id' => $user->id,
            'sid' => $user->sid,
            'subject' => 'Your health insurance',
            'content' => Hybrid::without(fn () => view('mailbox_templates.insurance-default', $data + ['user' => $user])->render()),
            'type' => 'primary',
            'template' => 'insurance-default',
            'template_data' => $data,
            'read' => 0,
        ]);
        self::chargePremium($user);

        return true;
    }

    /**
     * The month's premium, with the bills (from the 7th), once a month, when the
     * money is there - tried again every day, like a bill: dated the 7th if paid
     * then, else the day it is paid.
     */
    public static function chargePremium(User $user): void
    {
        $now = now();
        $policy = self::current($user->id);
        $accountId = BankAccount::where('student_id', $user->id)->value('id');
        if (!$policy || !$accountId || $now->day < self::DAY || !self::offered($user)) {
            return;
        }
        DB::transaction(function () use ($user, $policy, $accountId, $now) {
            BankAccount::whereKey($accountId)->lockForUpdate()->first();
            $paid = Transaction1::where('user_id', $user->id)->where('description', self::LINE)
                ->whereYear('transaction_date', $now->year)->whereMonth('transaction_date', $now->month)->exists();
            if ($paid) {
                return;
            }
            $date = $now->day > self::DAY ? $now : Carbon::create($now->year, $now->month, self::DAY, 9, 0, 0);
            LedgerService::post($user->id, 'debit', 'Needs', self::PLANS[$policy->plan]['premium'], self::LINE, [
                'sid' => $user->sid, 'bank_account_id' => $accountId, 'transaction_date' => $date,
                'only_if_enough' => true, 'meta' => ['plan' => $policy->plan],
            ]);
        });
    }
}
