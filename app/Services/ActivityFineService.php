<?php

namespace App\Services;

use App\Models\ActivityFine;
use App\Models\ActivityFineSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The fine for a Required activity not done in time (Daniel, 2026-11-03: "Put
 * the penalty for the students that did not do the activities in the
 * educational finance department" - 50 Ƶ for each one, the day after its
 * deadline).
 *
 * An activity is a Required event on the class calendar with a real deadline
 * (every admin screen saves the last day + 1, FullCalendar's exclusive end).
 * Academy exams aren't Education Finance Department activities: not fined.
 * A student is fined once per event, unless they:
 *  - did it (a completion pointing at the event; the Emergency Fund one is done
 *    once their fund is open - as for the Engagement badge);
 *  - have a tutor's extension still running (Extensions);
 *  - are on an approved Autopilot during the activity;
 *  - joined Zedville after the activity started.
 * Only deadlines from the day fines started (activity_fine_settings) on, and at
 * most two months back: old deadlines are never fined.
 */
class ActivityFineService
{
    public const LOOKBACK_DAYS = 60;

    /** The text on the bank line; App\Support\TransactionText shows it in the student's language. */
    public const DESCRIPTION = 'Fine: activity not done - ';

    /**
     * Fine everyone who missed a deadline. $write false only says who would be
     * fined.
     *
     * @return Collection<int, array{student: User, event: object, amount: float}>
     */
    public function run(?Carbon $now = null, bool $write = true): Collection
    {
        $now ??= now();
        $setting = ActivityFineSetting::current();
        $fines = collect();
        if ($setting->amount <= 0) {
            return $fines;
        }

        foreach ($this->dueEvents($now, $setting) as $event) {
            foreach ($this->studentsToFine($event, $now) as $student) {
                $fines->push(['student' => $student, 'event' => $event, 'amount' => $setting->amount]);
                if ($write) {
                    $this->fine($student, $event, $setting->amount);
                }
            }
        }

        return $fines;
    }

    /**
     * Required activities whose deadline has passed, since fines started. Copies
     * of one activity on a class calendar (same title, deadline the same month -
     * saves before 2026-10-25 made some) are one activity, with the latest
     * deadline: `ids` holds them all.
     */
    private function dueEvents(Carbon $now, ActivityFineSetting $setting): Collection
    {
        $since = $setting->active_since->copy()->startOfDay()->max($now->copy()->subDays(self::LOOKBACK_DAYS));

        return DB::table('calendar_events')
            ->where('position', 'required')
            ->whereNotNull('classId')
            ->where('title', 'not like', 'exam-%')
            ->whereColumn('end', '>', 'start')
            ->where('end', '<=', $now)
            ->where('end', '>', $since)
            ->orderBy('end')
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($event) => $event->classId . '|' . $event->title . '|' . Carbon::parse($event->end)->subDay()->format('Y-m'))
            ->map(function (Collection $copies) {
                $event = $copies->sortBy('end')->last();
                $event->ids = $copies->pluck('id')->all();

                return $event;
            })
            ->values();
    }

    /** The class's students who missed it and aren't excused, not fined for it yet. */
    private function studentsToFine(object $event, Carbon $now): Collection
    {
        $start = Carbon::parse($event->start);
        $end = Carbon::parse($event->end);

        return User::where('role', 4)
            ->where('grade', $event->classId)
            ->where('created_at', '<=', $start->copy()->endOfDay())
            ->get()
            ->reject(fn (User $student) => ActivityFine::where('student_id', $student->id)->whereIn('calendar_event_id', $event->ids)->exists())
            ->reject(fn (User $student) => $this->done($student, $event))
            ->reject(fn (User $student) => $this->extended($student, $event, $now))
            ->reject(fn (User $student) => $this->onAutopilot($student, $start, $end))
            ->values();
    }

    /**
     * The class's Required activities of this month - started, running or over -
     * the student hasn't done, each once however many copies the calendar has.
     * The Money Market waits for them (BankTransferService::moneyMarketLock).
     */
    public function pending(User $student, ?Carbon $now = null): Collection
    {
        $now ??= now();
        if (!$student->grade) {
            return collect();
        }

        return DB::table('calendar_events')
            ->where('position', 'required')
            ->where('classId', $student->grade)
            ->where('title', 'not like', 'exam-%')
            ->where('start', '<=', $now)
            ->where('end', '>', $now->copy()->startOfMonth())
            ->get()
            ->groupBy('title')
            ->map(function (Collection $copies) {
                $event = $copies->sortBy('end')->last();
                $event->ids = $copies->pluck('id')->all();

                return $event;
            })
            ->reject(fn (object $event) => $this->done($student, $event))
            ->values();
    }

    private function done(User $student, object $event): bool
    {
        if ($event->title === 'Emergency Fund'
            && DB::table('bank_accounts')->where('student_id', $student->id)->where('is_open_emergency_account', 1)->exists()) {
            return true;
        }
        // the monthly donation: done once its standing order is set up in the bank
        if ($event->title === DonationService::ACTIVITY && DonationService::active($student->id)) {
            return true;
        }

        return DB::table('activity_completions')
            ->where('student_id', $student->id)
            ->whereIn('activity_id', $event->ids)
            ->exists();
    }

    /** A tutor's extension whose last day hasn't passed. */
    private function extended(User $student, object $event, Carbon $now): bool
    {
        $until = DB::table('assignment_extensions')
            ->whereIn('calendar_event_id', $event->ids)
            ->where('student_id', $student->id)
            ->max('extended_to');

        return $until && $now->lt(Carbon::parse($until)->addDay()->startOfDay());
    }

    /** An approved Autopilot during the activity (its first to its last day). */
    private function onAutopilot(User $student, Carbon $start, Carbon $end): bool
    {
        return DB::table('autopilot_periods')
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $end->copy()->subDay()->toDateString())
            ->whereDate('end_date', '>=', $start->toDateString())
            ->exists();
    }

    private function fine(User $student, object $event, float $amount): void
    {
        DB::transaction(function () use ($student, $event, $amount) {
            $line = LedgerService::post($student->id, 'debit', 'Fine', $amount, self::DESCRIPTION . $event->title, [
                'sid' => $student->sid,
                'bank_account_id' => DB::table('bank_accounts')->where('student_id', $student->id)->value('id'),
                'transaction_date' => now(),
                'is_penalty' => true,
                'floor_at_zero' => true,
                'meta' => ['calendar_event_id' => $event->id],
            ]);

            ActivityFine::create([
                'student_id' => $student->id,
                'calendar_event_id' => $event->id,
                'activity_title' => $event->title,
                'run_start' => $event->start,
                'run_end' => $event->end,
                'amount' => $line ? (float) $line->amount : 0,
                'transaction_id' => $line?->id,
            ]);
        });
    }
}
