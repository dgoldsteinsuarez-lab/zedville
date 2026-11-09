<?php

namespace App\Services;

use App\Models\ActivityFine;
use App\Models\AutopilotPeriod;
use App\Models\BankAccount;
use App\Models\Transaction1;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * What Autopilot does to a student's account, and Autopilot set later
 * (Daniel, 2026-10-31: "el autopiloto debería poder ponerse
 * retrospectivamente, en el caso de que alguien se olvide ponerlo ... una
 * retrospección máxima de 2 meses").
 *
 * A period may start up to 2 months back. For the months already gone it
 * covers, what Autopilot would have done is done when it's saved, as if it
 * had been set in time:
 *  - the month's activity penalty (charged on its last day) is given back as
 *    a "Reversal - ..." line, like an admin reversal - the ledger only grows;
 *  - a month that has ended gets its "Autopilot - [Month]" history line;
 *  - the fine for a Required activity that ran during the period
 *    (ActivityFineService) is given back the same way.
 * The current month needs nothing more - its month-end skips the penalty and
 * writes the line (penalty:apply, autopilot:apply) - except a penalty already
 * charged this month (at login on the last day), which is given back too.
 * Each step happens once, however many periods cover the month.
 */
class AutopilotService
{
    public const MAX_MONTHS_BACK = 2;

    /** The earliest day a period saved today may start. */
    public static function earliestStart(): Carbon
    {
        return now()->subMonthsNoOverflow(self::MAX_MONTHS_BACK)->startOfDay();
    }

    /**
     * The months of $period up to this one, caught up.
     *
     * @return array{refunds: int, refunded: float, lines: int}
     */
    public static function catchUp(AutopilotPeriod $period, int $adminId): array
    {
        $done = ['refunds' => 0, 'refunded' => 0.0, 'lines' => 0];
        $student = User::find($period->student_id);
        if (!$student) {
            return $done;
        }

        $thisMonth = now()->startOfMonth();
        $lastMonth = $period->end_date->copy()->startOfMonth();
        if ($lastMonth->gt($thisMonth)) {
            $lastMonth = $thisMonth->copy();
        }

        for ($month = $period->start_date->copy()->startOfMonth(); $month->lte($lastMonth); $month->addMonth()) {
            $penalties = Transaction1::where('user_id', $student->id)->where('category', 'Penalty')
                ->whereBetween('transaction_date', [$month->copy(), $month->copy()->endOfMonth()])
                ->pluck('id');
            foreach ($penalties as $penaltyId) {
                $amount = self::giveBack($penaltyId, $period, $adminId);
                if ($amount !== null) {
                    $done['refunds']++;
                    $done['refunded'] += $amount;
                }
            }

            if ($month->lt($thisMonth) && self::recordMonth($student, $month->year, $month->month, now())) {
                $done['lines']++;
            }
        }

        // the fines for Required activities that ran while they were away (ActivityFineService)
        $fines = ActivityFine::where('student_id', $student->id)->whereNotNull('transaction_id')
            ->where('run_start', '<=', $period->end_date->copy()->endOfDay())
            ->where('run_end', '>', $period->start_date->copy()->startOfDay())
            ->pluck('transaction_id');
        foreach ($fines as $fineId) {
            $amount = self::giveBack($fineId, $period, $adminId);
            if ($amount !== null) {
                $done['refunds']++;
                $done['refunded'] += $amount;
            }
        }

        return $done;
    }

    public static function monthRecorded(int $userId, int $year, int $month): bool
    {
        return Transaction1::where('user_id', $userId)->where('category', 'Autopilot')
            ->where('meta', 'like', '%"autopilot_month":"' . sprintf('%04d-%02d', $year, $month) . '"%')->exists();
    }

    /**
     * "Autopilot - [Month]" in the student's history (amount 0, balance
     * unchanged). False when there's no account or the month has its line.
     */
    public static function recordMonth(User $user, int $year, int $month, $date): bool
    {
        $account = BankAccount::where('student_id', $user->id)->first();
        if (!$account || self::monthRecorded($user->id, $year, $month)) {
            return false;
        }

        DB::transaction(function () use ($user, $account, $year, $month, $date) {
            Transaction1::create([
                'user_id' => $user->id,
                'sid' => $user->sid,
                'bank_account_id' => $account->id,
                'transaction_date' => $date,
                'description' => 'Autopilot - ' . Carbon::create($year, $month, 1)->format('F'),
                'type' => 'credit',
                'category' => 'Autopilot',
                'amount' => 0,
                'balance' => LedgerService::lockedBalance($user->id),
                'is_penalty' => 0,
                'meta' => ['autopilot_month' => sprintf('%04d-%02d', $year, $month)],
            ]);
        });

        return true;
    }

    /** A penalty given back once; null if it already was. */
    private static function giveBack(int $penaltyId, AutopilotPeriod $period, int $adminId): ?float
    {
        return DB::transaction(function () use ($penaltyId, $period, $adminId) {
            $penalty = Transaction1::lockForUpdate()->find($penaltyId);
            $meta = $penalty->meta ?? [];
            if (!empty($meta['reversed_by'])) {
                return null;
            }

            $reversal = LedgerService::post($penalty->user_id, 'credit', 'Reversal', (float) $penalty->amount, 'Reversal - ' . $penalty->description, [
                'sid' => $penalty->sid,
                'bank_account_id' => $penalty->bank_account_id,
                'meta' => [
                    'reversal_of' => $penalty->id,
                    'by_admin' => $adminId,
                    'reason' => 'Autopilot set later: ' . $period->reason,
                    'autopilot_period' => $period->id,
                ],
            ]);

            $penalty->meta = array_merge($meta, ['reversed_by' => $reversal->id]);
            $penalty->save();

            return (float) $penalty->amount;
        });
    }
}
