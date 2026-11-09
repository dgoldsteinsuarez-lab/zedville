<?php

namespace App\Support;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * What the student calendar shows (option A, Daniel 2026-11-04: "Make option A
 * for the calendar" - a month, a week and a day without scrolling and without
 * hours). Each activity of the class once - even if it was put on the
 * calendar twice - with its first and last day (the calendar stores the day
 * after the last one), whether it is Required, whether the student did it in
 * this run (a completion from its first day on, as the Dashboard counts it;
 * the Emergency Fund once the fund is open) and where it takes them.
 */
class StudentCalendar
{
    /**
     * @return array<int, array{title: string, required: bool, start: string, last: string, done: bool, done_on: ?string, url: ?string}>
     */
    public static function activities(User $user): array
    {
        if (!$user->grade) {
            return [];
        }

        $events = DB::table('calendar_events')->where('classId', $user->grade)->whereNotNull('start')
            ->orderBy('start')->orderBy('id')->get();
        $completions = DB::table('activity_completions')->where('student_id', $user->id)
            ->whereIn('activity_id', $events->pluck('id'))->get(['activity_id', 'completed_at']);
        $fundOpen = DB::table('bank_accounts')->where('student_id', $user->id)->where('is_open_emergency_account', 1)->exists();
        $donating = \App\Services\DonationService::active($user->id) !== null;

        return $events
            ->map(function ($event) {
                $start = Carbon::parse($event->start)->startOfDay();
                $end = $event->end ? Carbon::parse($event->end)->startOfDay() : null;

                return (object) ['event' => $event, 'start' => $start, 'last' => $end && $end->gt($start) ? $end->subDay() : $start->copy()];
            })
            ->groupBy(fn ($e) => $e->event->title . '|' . $e->last->toDateString())
            ->map(function ($copies) use ($completions, $fundOpen, $donating, $user) {
                $first = $copies->first();
                $ids = $copies->map(fn ($e) => (int) $e->event->id)->all();
                $doneAt = $completions
                    ->filter(fn ($c) => in_array((int) $c->activity_id, $ids, true) && Carbon::parse($c->completed_at)->gte($first->start))
                    ->min('completed_at');

                return [
                    'title' => CalendarTitle::forStudent($first->event->title),
                    // "Require" too: an old Calendar > Add Activity saved that
                    'required' => str_starts_with(strtolower((string) $first->event->position), 'requir'),
                    'start' => $first->start->toDateString(),
                    'last' => $first->last->toDateString(),
                    'done' => $doneAt !== null || ($first->event->title === 'Emergency Fund' && $fundOpen)
                        || ($first->event->title === \App\Services\DonationService::ACTIVITY && $donating),
                    'done_on' => $doneAt ? Carbon::parse($doneAt)->toDateString() : null,
                    'url' => CalendarLink::forStudent($first->event->title, $user),
                ];
            })
            ->values()
            ->all();
    }
}
