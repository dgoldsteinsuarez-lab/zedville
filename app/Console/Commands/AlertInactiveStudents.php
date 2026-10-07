<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Admin Roles & Permissions doc §2.3: Tutor "Receive automatic alerts for
 * inactive students." Reuses NotificationService entirely - no new table,
 * no new UI. "Inactive" = no login and no activity_completions row within
 * config('zedville.inactive_days_threshold') days. Re-alerts at most once a
 * week per student (checked via existing AppNotification rows), so a Tutor
 * isn't nagged daily about the same student.
 *
 *   php artisan students:alert-inactive
 */
class AlertInactiveStudents extends Command
{
    protected $signature = 'students:alert-inactive';
    protected $description = "Notify each Tutor about their own students who've gone inactive";

    public function handle(NotificationService $notifications)
    {
        $thresholdDays = (int) config('zedville.inactive_days_threshold', 14);
        $cutoff = now()->subDays($thresholdDays);

        $tutorIds = DB::table('class_tutor')->distinct()->pluck('user_id');

        $alerted = 0;

        foreach ($tutorIds as $tutorId) {
            $tutor = User::find($tutorId);
            if (!$tutor || !$tutor->isTutor()) {
                continue;
            }

            $studentIds = $tutor->manageableStudentIds();
            if ($studentIds === null || $studentIds->isEmpty()) {
                continue;
            }

            $activeSinceIds = DB::table('activity_completions')
                ->whereIn('student_id', $studentIds)
                ->where('completed_at', '>=', $cutoff)
                ->distinct()
                ->pluck('student_id');

            $inactiveStudents = User::whereIn('id', $studentIds)
                ->whereNotIn('id', $activeSinceIds)
                ->where(function ($q) use ($cutoff) {
                    $q->whereNull('loginTime')->orWhere('loginTime', '<', $cutoff);
                })
                ->get(['id', 'name', 'nickname']);

            foreach ($inactiveStudents as $student) {
                $alreadyAlerted = AppNotification::where('user_id', $tutorId)
                    ->where('type', 'student_inactive')
                    ->where('source_id', $student->id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->exists();

                if ($alreadyAlerted) {
                    continue;
                }

                $notifications->log(
                    $tutorId,
                    'Students',
                    'student_inactive',
                    $student->nickname . ' has gone quiet',
                    "No login or activity from {$student->nickname} in the last {$thresholdDays} days.",
                    'alert',
                    'amber',
                    $student
                );

                $alerted++;
            }
        }

        $this->comment("Sent {$alerted} inactive-student alert(s).");

        return self::SUCCESS;
    }
}
