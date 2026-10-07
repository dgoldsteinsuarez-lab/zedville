<?php

namespace App\Console\Commands;

use App\Models\AutopilotPeriod;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * REPORT-ONLY preview of the month-end Autopilot (see Admin Roles & Permissions
 * doc, section 5). For every class it works out the class averages and lists
 * which accounts would receive them:
 *   - students with an active approved-absence period overlapping the month
 *   - the class Tutor(s), unless they completed activities themselves
 *
 * It deliberately WRITES NOTHING. Applying averages creates money movements, so
 * the exact rules (which averages, how they become transactions) must be signed
 * off by the product owner first. Not scheduled for the same reason.
 *
 *   php artisan autopilot:report            (current month)
 *   php artisan autopilot:report --month=9 --year=2026
 */
class AutopilotReport extends Command
{
    protected $signature = 'autopilot:report {--month=} {--year=}';
    protected $description = 'Preview which accounts month-end Autopilot would apply to (writes nothing)';

    public function handle()
    {
        $month = (int) ($this->option('month') ?: now()->month);
        $year = (int) ($this->option('year') ?: now()->year);
        $from = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $to = date('Y-m-t 23:59:59', strtotime($from));

        $this->info("Autopilot preview for {$year}-" . sprintf('%02d', $month) . ' (nothing is written)');

        foreach (SchoolClass::with('tutors')->orderBy('sid')->orderBy('name')->get() as $class) {
            $students = User::where('role', 4)->where('grade', $class->id)->pluck('id');
            if ($students->isEmpty()) {
                continue;
            }

            $activities = DB::table('activity_completions')->whereIn('student_id', $students)
                ->whereBetween('completed_at', [$from, $to])->count();
            $spend = (float) DB::table('transactions1')->whereIn('user_id', $students)
                ->where('type', 'debit')->where('is_penalty', 0)
                ->whereBetween('transaction_date', [$from, $to])->sum('amount');

            $n = $students->count();
            $this->line('');
            $this->line("<options=bold>{$class->name}</> (school {$class->sid}) - {$n} student(s)");
            $this->line(sprintf('  class average: %.2f activities completed, %.2f spent', $activities / $n, $spend / $n));

            $onAutopilot = AutopilotPeriod::overlappingMonth($year, $month)->whereIn('student_id', $students)->with('student:id,nickname')->get();
            foreach ($onAutopilot as $p) {
                $this->line("  student on Autopilot: {$p->student->nickname} ({$p->start_date->toDateString()} to {$p->end_date->toDateString()})");
            }
            if ($onAutopilot->isEmpty()) {
                $this->line('  no students on Autopilot');
            }

            foreach ($class->tutors as $tutor) {
                $done = DB::table('activity_completions')->where('student_id', $tutor->id)
                    ->whereBetween('completed_at', [$from, $to])->count();
                $this->line('  tutor ' . $tutor->nickname . ': ' . ($done > 0
                    ? "completed {$done} activities - no Autopilot"
                    : 'no activities - Autopilot would apply'));
            }
        }

        $this->line('');
        $this->comment('Preview only. No accounts or transactions were changed.');
        return self::SUCCESS;
    }
}
