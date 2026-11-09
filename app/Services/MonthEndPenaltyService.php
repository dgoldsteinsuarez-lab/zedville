<?php

namespace App\Services;

use App\Models\ActivityFine;
use App\Models\ActivityFineSetting;
use App\Models\AutopilotPeriod;
use App\Models\BankAccount;
use App\Models\Mailbox;
use App\Models\User;
use App\Support\Hybrid;
use Illuminate\Support\Facades\DB;

/**
 * The month-end fine for not doing the plan of the month (Daniel, 2026-11-07).
 * The month's spending activities take a student to 90-94% of the salary; one
 * who never chooses the plan of the month (Spending 4) would keep that money.
 * So on the last day the City Hall fines them: the fine of any Required
 * activity not done (Admin > Bank > Activity Fines), raised until the month's
 * spending is 99% of the salary - "la multa asciende hasta llegar en total a
 * 99% del salario, incluyendo los 50Z". A letter from the City Hall says why:
 * the salary is to prepare for the future and, like any adult citizen, they
 * have obligations. The first month too. Whoever chose a plan pays nothing,
 * however much is left ("no quiero que haya castigo si no mueve el dinero").
 *
 * Before (2026-11-03) the bank chose a plan for them and added a penalty up to
 * 95% - "en vez de wants ... no se ajustaría a la realidad".
 */
class MonthEndPenaltyService
{
    /** With the fine, the month's spending is this share of the salary. */
    public const SPEND_TO = 0.99;

    /** The plan of the month as an activity: on its fine and the fines list (App\Support\CalendarTitle). */
    public const ACTIVITY = 'Monthly plan';

    /**
     * Fines one student, at most once a month. Returns what was charged, or
     * null when the student chose a plan, is on Autopilot or has no bank
     * account.
     *
     * @return array{fine: float}|null
     */
    public function apply(User $user): ?array
    {
        if (!$this->due($user)) {
            return null;
        }

        $start = now()->startOfMonth();
        $missing = round(self::SPEND_TO * self::salary() - BankTransferService::spentThisMonth($user->id), 2);
        $amount = max(ActivityFineSetting::current()->amount, $missing);

        $line = DB::transaction(function () use ($user, $start, $amount) {
            $line = $amount > 0 ? LedgerService::post($user->id, 'debit', 'Fine', $amount, ActivityFineService::DESCRIPTION . self::ACTIVITY, [
                'sid' => $user->sid,
                'bank_account_id' => BankAccount::where('student_id', $user->id)->value('id'),
                'transaction_date' => now(),
                'is_penalty' => true,
                'floor_at_zero' => true,
                'meta' => ['monthly_plan' => $start->format('Y-m')],
            ]) : null;

            // also what keeps it to once a month, and what an Autopilot set later gives back
            ActivityFine::create([
                'student_id' => $user->id,
                'calendar_event_id' => null,
                'activity_title' => self::ACTIVITY,
                'run_start' => $start,
                'run_end' => $start->copy()->addMonthNoOverflow(),
                'amount' => $line ? (float) $line->amount : 0,
                'transaction_id' => $line?->id,
            ]);

            return $line;
        });

        $fine = $line ? (float) $line->amount : 0.0;
        if ($fine > 0) {
            $this->letter($user, $fine, $start->format('Y-m'));
        }

        return ['fine' => $fine];
    }

    /** A student who still has to choose this month's plan (so not fined for it yet), not on Autopilot. */
    public function due(User $user): bool
    {
        return (int) $user->role === 4
            && WantsPlanService::needsChoice($user)
            && !AutopilotPeriod::overlappingMonth(now()->year, now()->month)->where('student_id', $user->id)->exists();
    }

    /** Whether the student was fined this month for not choosing the plan. */
    public static function finedThisMonth(int $userId): bool
    {
        return ActivityFine::where('student_id', $userId)->where('activity_title', self::ACTIVITY)
            ->where('run_start', now()->startOfMonth())->exists();
    }

    /** The salary the month's spending is measured against. */
    public static function salary(): float
    {
        return (float) (app(StatementGenerator::class)->getActiveBudgetConfig()->monthly_salary ?? config('zedville.monthly_salary', 3952.40));
    }

    /**
     * The City Hall's letter in the City Mailbox: the fine and why - read in
     * the student's language (App\Models\Mailbox); the bell says it came.
     */
    private function letter(User $user, float $fine, string $month): void
    {
        $data = ['amount' => $fine, 'month' => $month];

        Mailbox::create([
            'student_id' => $user->id,
            'sid' => $user->sid,
            'subject' => 'City Hall fine: your plan of the month',
            'content' => Hybrid::without(fn () => view('mailbox_templates.city-hall-fine', $data + ['user' => $user])->render()),
            'type' => 'primary',
            'template' => 'city-hall-fine',
            'template_data' => $data,
            'read' => 0,
        ]);
    }
}
