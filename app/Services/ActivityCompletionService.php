<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Records that a student finished one of the activities admins put on the
 * calendar: Needs, Wants & Savings Match, Emergency Fund, Spending
 * Activity, FinHero Tasks and Library modules, and Academy real exams.
 *
 * The completion points at the student's own class's calendar event for
 * that activity - that's how the Engagement badge knows whether it was
 * required or optional (BadgeCalculatorService). activity_type holds the
 * activity's key, which the EFD blackboard uses to hide finished ones.
 * Recorded once per activity per month.
 */
class ActivityCompletionService
{
    /**
     * @return bool true if this is the first completion this month
     */
    public function complete(int $studentId, string $activityKey, string $calendarTitle): bool
    {
        $alreadyCompleted = DB::table('activity_completions')
            ->where('student_id', $studentId)
            ->where('activity_type', $activityKey)
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->exists();

        if ($alreadyCompleted) {
            return false;
        }

        DB::table('activity_completions')->insert([
            'student_id' => $studentId,
            // No calendar event for the class (e.g. deleted): still recorded,
            // so the blackboard marks it done, but no badge rule matches id 0.
            'activity_id' => $this->calendarEventId($studentId, $calendarTitle) ?? 0,
            'activity_type' => $activityKey,
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * The student's class's event with this title - the one already
     * running if there are several (admins re-save activities monthly).
     */
    private function calendarEventId(int $studentId, string $calendarTitle): ?int
    {
        $classId = DB::table('users')->where('id', $studentId)->value('grade');
        if (!$classId) {
            return null;
        }

        $events = DB::table('calendar_events')->where('classId', $classId)->where('title', $calendarTitle);

        return (clone $events)->where('start', '<=', now())->orderByDesc('start')->value('id')
            ?? $events->orderByDesc('id')->value('id');
    }
}
