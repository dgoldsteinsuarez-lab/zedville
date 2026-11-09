<?php

namespace App\Services;

use App\Http\Controllers\BankController;
use App\Models\Activity;
use App\Models\BankAccount;
use App\Models\SchoolClass;
use App\Models\Transaction1;
use App\Models\User;
use App\Models\WantsPlan;
use App\Models\WantsPlanCharge;
use App\Models\WantsPlanChoice;
use App\Support\Hybrid;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Spending 4, the plan of the month (Daniel, 2026-10-31). Once a month, after
 * the question of the day, a student chooses one of four plans - "solo eligen
 * una actividad de Spending 4". A class has one of two kinds:
 *
 *  - Low Spending 4, next to the class's Low Spending activity: together they
 *    are at least 20% of the salary (LOW_TOTAL), so only the plans that reach
 *    it with that activity's budget are offered;
 *  - High Spending 4, on its own: every plan at least 20% of the salary
 *    (HIGH_MIN; "lo puedo usar en Intermediate level para que puedan estar en
 *    menos en su cuenta y pedir crédito").
 * Daniel, 2026-11-08: "entre Spending y Spending 4 Low, deberían sumar esos
 * 91% ... Spending 4 High, que solo esto cubre el gasto necesario para llegar
 * a 91%": with the bills, the 20% to the fund, the month's shopping from the
 * consumer survey and the monthly donation of 4-6% (DonationService), it
 * takes a student to about 91%.
 *
 * The four come one from each price level of those plans, the same for the
 * whole class, the next four every month. A plan is for the whole month,
 * whenever it's chosen: once (paid when chosen) or on the month's Saturdays
 * (those already gone are paid when chosen, the rest on the day by
 * plans:charge). A payment there's no money for waits - no balance goes
 * below zero - and is paid as soon as there is: right after the salary
 * (BankController::creditMonthlySalary, or a new citizen's first one in
 * StatementGenerator), when the student opens the bank, or by plans:charge.
 * The student is told when (Daniel, 2026-11-02: "si el salario no ha llegado
 * aún, se descuenta justo cuando llegue el salario, y se debe notificar en
 * este mensaje").
 */
class WantsPlanService
{
    /**
     * Low Spending + Low Spending 4: at least this share of the salary. It was
     * 0.24 (Daniel, 2026-11-08: they should take a student to 91%); the same
     * day the monthly donation of 4-6% of the salary came in (DonationService:
     * "podríamos reducir los Spending activities y añadir esto"), so 4 points
     * less: with the smallest donation a student still reaches about 91%.
     */
    public const LOW_TOTAL = 0.20;

    /** Every High Spending 4 plan: at least this share (also 4 points less for the donation). */
    public const HIGH_MIN = 0.20;

    /**
     * The Intermediate and after: their own, cheaper plans ("mid"), with the
     * Low Spending activity about this share (Daniel, 2026-11-08: "for
     * intermediate, if we add the donation per month, adjust to 15% Spending 4"):
     * the point there is to save as much as possible.
     */
    public const MID_TOTAL = 0.15;

    /** A payment's bank line (App\Support\TransactionText translates it). */
    public const LINE = 'Monthly plan: ';

    public static function month(?Carbon $date = null): string
    {
        return ($date ?? now())->format('Y-m');
    }

    public static function salary(): float
    {
        return (float) config('zedville.monthly_salary', 3952.40);
    }

    /** Whether the student still has to choose this month - not once fined for not choosing (MonthEndPenaltyService). */
    public static function needsChoice(?User $user): bool
    {
        return $user && (int) $user->role === 4
            && !WantsPlanChoice::where('user_id', $user->id)->where('month', self::month())->exists()
            && BankAccount::where('student_id', $user->id)->exists()
            && WantsPlan::where('is_active', true)->exists()
            && !MonthEndPenaltyService::finedThisMonth($user->id);
    }

    /** low, high or mid: the Spending 4 the student's class has. */
    public static function kindFor(User $user): string
    {
        return self::kindForClass($user->grade ? SchoolClass::with('grade.level')->find($user->grade) : null);
    }

    /** mid from the Intermediate on; before it, the class's choice of low or high. */
    public static function kindForClass(?SchoolClass $class): string
    {
        if ((int) optional(optional(optional($class)->grade)->level)->sort_order >= \App\Support\Level::INTERMEDIATE) {
            return 'mid';
        }

        return optional($class)->spending4 === 'high' ? 'high' : 'low';
    }

    /** The class's current Low Spending activity, if it has one. */
    public static function lowActivity(int $classId): ?Activity
    {
        $id = DB::table('mba_position')->where('activity_key', Activity::SLOTS['low'][0])->where('cid', $classId)->orderByDesc('id')->value('activity_id');

        return $id ? Activity::find($id) : null;
    }

    /** The least a plan of the class's kind must cost this month. */
    public static function minimum(string $kind, ?int $classId): float
    {
        if ($kind === 'high') {
            return round(self::HIGH_MIN * self::salary(), 2);
        }
        $low = $classId ? self::lowActivity($classId) : null;
        $share = $kind === 'mid' ? self::MID_TOTAL : self::LOW_TOTAL;

        return round(max(0, $share * self::salary() - (float) ($low->budget ?? 0)), 2);
    }

    /**
     * The plans that may be offered: of the kind, costing at least the
     * minimum - or, if fewer than four do, the four dearest - in four price
     * levels (cheapest first), each level in the admin's order.
     */
    public static function levels(string $kind = 'low', float $minimum = 0): array
    {
        $plans = WantsPlan::where('is_active', true)->where('kind', $kind)->get();
        $eligible = $plans->filter(fn ($p) => $p->monthlyCost() >= $minimum);
        if ($eligible->count() < 4) {
            $eligible = $plans->sortByDesc(fn ($p) => $p->monthlyCost())->take(4);
        }
        $sorted = $eligible->sortBy([fn ($a, $b) => $a->monthlyCost() <=> $b->monthlyCost(), fn ($a, $b) => $a->sort_order <=> $b->sort_order])->values();
        $count = $sorted->count();
        $levels = [];
        for ($level = 0; $level < 4; $level++) {
            $from = (int) round($level * $count / 4);
            $slice = $sorted->slice($from, (int) round(($level + 1) * $count / 4) - $from);
            if ($slice->isNotEmpty()) {
                $levels[] = $slice->sortBy('sort_order')->values();
            }
        }

        return $levels;
    }

    /** The class's place in a level this month: the next plan of it every month. */
    private static function turn(?int $classId, int $userId, int $size, int $level): int
    {
        $months = (int) now()->format('Y') * 12 + (int) now()->format('n');

        return ($months + crc32('class:' . ($classId ?: 'u' . $userId) . ':' . $level)) % $size;
    }

    /** This month's four plans for the student's class, cheapest first. */
    public static function offers(User $user): Collection
    {
        $kind = self::kindFor($user);
        $levels = self::levels($kind, self::minimum($kind, $user->grade ? (int) $user->grade : null));

        return collect($levels)->map(fn ($level, $i) => $level[self::turn($user->grade ? (int) $user->grade : null, $user->id, $level->count(), $i)])->values();
    }

    /** 1 once, 2 every other Saturday, 4 every Saturday. */
    public static function frequency(WantsPlan $plan): int
    {
        return $plan->times_per_month >= 4 ? 4 : ($plan->times_per_month >= 2 ? 2 : 1);
    }

    /** The days a plan is paid this month: today if once, else the month's Saturdays (those gone: today). */
    public static function schedule(WantsPlan $plan, ?Carbon $today = null): array
    {
        $today = ($today ?? today())->copy()->startOfDay();
        if (self::frequency($plan) === 1) {
            return [$today];
        }

        $saturdays = [];
        for ($day = $today->copy()->startOfMonth(); $day->month === $today->month; $day->addDay()) {
            if ($day->isSaturday()) {
                $saturdays[] = $day->copy();
            }
        }
        $step = self::frequency($plan) === 4 ? 1 : 2;

        return array_slice(array_values(array_filter($saturdays, fn ($d, $i) => $i % $step === 0, ARRAY_FILTER_USE_BOTH)), 0, self::frequency($plan));
    }

    /** What a plan costs the student each time: in the Intermediate, with the new prices (App\Services\PriceRise). */
    public static function cost(WantsPlan $plan, ?User $user = null): float
    {
        return PriceRise::price($user, (float) $plan->cost, PriceRise::PLAN);
    }

    /** A plan's card: what it is, what it costs this month, its days. */
    public static function card(WantsPlan $plan, ?User $user = null): array
    {
        $days = self::schedule($plan);
        $once = self::frequency($plan) === 1;
        $gone = count(array_filter($days, fn ($d) => $d->lt(today())));
        $cost = self::cost($plan, $user);

        return [
            'id' => $plan->id,
            'name' => $plan->text('name'),
            'description' => PriceRise::planText($user, $plan->text('description')),
            'image' => $plan->imageUrl(),
            'cost' => $cost,
            'total' => round($cost * count($days), 2),
            'total_text' => zeds($cost * count($days)),
            'cost_line' => $once
                ? __('plans.cost_once', ['cost' => zeds($cost)])
                : __('plans.cost_line', ['cost' => zeds($cost), 'freq' => __('plans.freq.' . self::frequency($plan))]),
            'total_line' => $once ? null : __('plans.month_total', ['n' => count($days), 'cost' => zeds($cost), 'total' => zeds($cost * count($days))]),
            'dates' => self::dates($days),
            'catch_up' => $gone > 0 ? __('plans.catch_up') : null,
        ];
    }

    /** "7, 14, 21 and 28 November" in the student's language, or in $locale. */
    public static function dates(array $days, ?string $locale = null): string
    {
        if (count($days) === 1 && $days[0]->isToday()) {
            return __('plans.today', [], $locale);
        }
        $numbers = array_map(fn ($d) => __('plans.day', ['d' => $d->day], $locale), $days);
        $last = array_pop($numbers);
        $list = $numbers ? implode(', ', $numbers) . ' ' . __('plans.and', [], $locale) . ' ' . $last : $last;

        return __(count($days) > 1 ? 'plans.on_days' : 'plans.on_day', ['days' => $list, 'month' => $days[0]->copy()->locale($locale ?? app()->getLocale())->isoFormat('MMMM')], $locale);
    }

    /** What the box shows: the class's kind and its four plans. */
    public static function box(User $user): array
    {
        return [
            'kind' => self::kindFor($user),
            'offers' => self::offers($user)->map(fn ($p) => self::card($p, $user))->values(),
        ];
    }

    /** The student's plan for the month, with its payments planned and the ones due today made. */
    public static function choose(User $user, int $planId): array
    {
        $offers = self::offers($user);
        $plan = $offers->firstWhere('id', $planId);
        abort_unless($plan, 422, 'This is not one of this month\'s plans.');

        $choice = DB::transaction(function () use ($user, $plan, $offers) {
            abort_if(WantsPlanChoice::where('user_id', $user->id)->where('month', self::month())->lockForUpdate()->exists(), 409);
            $days = self::schedule($plan);
            $cost = self::cost($plan, $user);
            $choice = WantsPlanChoice::create([
                'user_id' => $user->id, 'month' => self::month(), 'kind' => 'plan', 'wants_plan_id' => $plan->id,
                'plan_ids' => [$plan->id], 'offered' => $offers->pluck('id')->all(), 'total' => round($cost * count($days), 2),
            ]);
            foreach ($days as $day) {
                WantsPlanCharge::create(['choice_id' => $choice->id, 'wants_plan_id' => $plan->id, 'user_id' => $user->id,
                    'due_date' => $day->toDateString(), 'amount' => $cost]);
            }

            return $choice;
        });

        self::chargeDue($user->id);
        $paid = (float) $choice->charges()->where('status', 'charged')->sum('amount');
        $waiting = (float) $choice->charges()->where('status', 'waiting')->sum('amount');
        $card = self::card($plan, $user);

        return $card + [
            // a plan paid once that waits: when, instead of "You pay today"
            'paid_on' => $waiting > 0 && self::frequency($plan) === 1 ? null : __('plans.paid_on', ['dates' => $card['dates']]),
            'paid_today' => $paid > 0 ? __('plans.paid_today_amount', ['amount' => zeds($paid)]) : null,
            'waiting' => $waiting > 0 ? self::waitingText($user->id, $waiting) : null,
        ];
    }

    /**
     * When a payment that waits for money will be taken: with this month's
     * salary if it hasn't come yet, else as soon as there's money - at the
     * latest with the next salary. A new citizen's first salary comes when
     * the bank is set up, not on the 6th: no date then.
     */
    private static function waitingText(int $userId, float $amount): string
    {
        $berlin = Carbon::now('Europe/Berlin');
        if (BankController::salaryCredited($userId, $berlin)) {
            [$key, $day] = ['plans.waiting_money', $berlin->copy()->startOfMonth()->addMonth()->day(BankController::SALARY_DAY)];
        } elseif ($berlin->day < BankController::SALARY_DAY && Transaction1::where('user_id', $userId)->exists()) {
            [$key, $day] = ['plans.waiting_salary', $berlin->copy()->day(BankController::SALARY_DAY)];
        } else {
            [$key, $day] = ['plans.waiting_salary_soon', null];
        }
        // each language with its own date: a hybrid class reads this one in English
        $text = fn (string $locale) => __($key, ['amount' => zeds($amount), 'date' => $day ? self::dates([$day], $locale) : ''], $locale);

        return Hybrid::on() ? Hybrid::mix($text(app()->getLocale()), $text('en')) : $text(app()->getLocale());
    }

    /**
     * Pays the payments that are due (today or before), those waiting for
     * money too, for one student or everyone - dated $when if given (right
     * after the salary). Returns how many were paid.
     */
    public static function chargeDue(?int $userId = null, ?Carbon $when = null): int
    {
        $paid = 0;
        $due = WantsPlanCharge::whereIn('status', ['pending', 'waiting'])->whereDate('due_date', '<=', today())
            ->when($userId, fn ($q) => $q->where('user_id', $userId))->orderBy('due_date')->orderBy('id')->get();

        foreach ($due as $charge) {
            DB::transaction(function () use ($charge, $when, &$paid) {
                $charge = WantsPlanCharge::with('plan', 'choice.plan')->lockForUpdate()->find($charge->id);
                if ($charge->status === 'charged') {
                    return;
                }
                $user = User::find($charge->user_id);
                $account = BankAccount::where('student_id', $charge->user_id)->first();
                $name = $charge->plan->name ?? $charge->choice->plan->name ?? 'Plan';
                $line = $user && $account ? LedgerService::post($charge->user_id, 'debit', 'Wants', (float) $charge->amount, self::LINE . $name, [
                    'sid' => $user->sid, 'bank_account_id' => $account->id, 'transaction_date' => $when ?? now(),
                    'only_if_enough' => true, 'meta' => ['wants_plan_charge' => $charge->id],
                ]) : null;
                $charge->update(['status' => $line ? 'charged' : 'waiting', 'transaction_id' => $line->id ?? null]);
                $paid += $line ? 1 : 0;
            });
        }

        return $paid;
    }
}
