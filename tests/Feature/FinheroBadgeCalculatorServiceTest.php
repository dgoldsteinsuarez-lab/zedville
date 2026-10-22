<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinheroBadgeCalculatorService;
use App\Services\FinheroPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "does it need to be fixed" (about finhero:calculate-
 * monthly never being scheduled) - investigating turned up a second,
 * deeper bug: the command crashed every time it ran, scheduled or not,
 * because getQuizPoints()/getLibraryPoints() still pointed at
 * 'daily_quiz_answers'/'library_question_answers' tables that were never
 * created (confirmed live: finhero_badge_records had 0 rows for any
 * student, ever, while the Engagement badge table had real rows every
 * month). Both now read finhero_student_points, the real table every
 * FinHero point in this app actually gets logged to.
 */
class FinheroBadgeCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_command_runs_without_crashing(): void
    {
        User::factory()->create(['role' => 4]);

        $this->artisan('finhero:calculate-monthly')->assertExitCode(0);

        $this->assertDatabaseCount('finhero_badge_records', 1);
    }

    public function test_quiz_points_are_read_from_the_real_finhero_student_points_table(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $now = now();

        // Same call LoginQuizController makes on a correct daily-quiz
        // answer - the real, already-working write side.
        for ($i = 0; $i < 3; $i++) {
            DB::table('finhero_student_points')->insert([
                'student_id' => $student->id, 'month' => $now->month, 'year' => $now->year,
                'academic_year' => '2026-2027', 'source_type' => 'quiz', 'source_key' => 'daily_quiz',
                'points_earned' => 1, 'earned_at' => $now->copy()->subDays($i),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $record = app(FinheroBadgeCalculatorService::class)->calculateForStudent($student->id, $now->month, $now->year);

        $this->assertSame(3, $record->quiz_pts);
    }

    public function test_quiz_points_are_capped_at_ten(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $now = now();

        for ($i = 0; $i < 14; $i++) {
            DB::table('finhero_student_points')->insert([
                'student_id' => $student->id, 'month' => $now->month, 'year' => $now->year,
                'academic_year' => '2026-2027', 'source_type' => 'quiz', 'source_key' => 'daily_quiz',
                'points_earned' => 1, 'earned_at' => $now->copy()->subDays($i),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $record = app(FinheroBadgeCalculatorService::class)->calculateForStudent($student->id, $now->month, $now->year);

        $this->assertSame(10, $record->quiz_pts);
    }

    public function test_library_points_dont_crash_and_currently_report_zero(): void
    {
        // No caller anywhere in this app awards 'library' FinHero points
        // yet (grepped every addPoints() call site) - this just confirms
        // the fixed query runs cleanly and honestly reports 0, rather
        // than crashing on a table that was never built.
        $student = User::factory()->create(['role' => 4]);
        DB::table('finhero_monthly_settings')->insert([
            'month' => now()->month, 'year' => now()->year, 'academic_year' => '2026-2027',
            'active_library_module_id' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $record = app(FinheroBadgeCalculatorService::class)->calculateForStudent($student->id, now()->month, now()->year);

        $this->assertSame(0, $record->library_pts);
    }
}
