<?php

namespace Tests\Feature;

use App\Models\FinheroBadgeRecord;
use App\Models\StudentBadgeRecord;
use App\Models\User;
use App\Services\BadgeCalculatorService;
use App\Services\FinheroBadgeCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Engagement and FinHero badges are two deliberately distinct systems
 * (confirmed via bugs.md #11 / planning.md Phase 3 #1 - both still
 * required, not a legacy/replacement pair). The only code that was
 * actually duplicated between their two calculator services - academic
 * year, accumulated-points summing, and threshold lookup - was pulled
 * into CalculatesBadgeAccumulation. This verifies both services still
 * behave the same through that shared trait.
 */
class BadgeAccumulationTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_engagement_academic_year_boundary(): void
    {
        $service = new BadgeCalculatorService();

        // config('zedville.academic_year_start_month') defaults to 4
        $this->assertSame('2025-2026', $service->getAcademicYear(4, 2025));
        $this->assertSame('2024-2025', $service->getAcademicYear(3, 2025));
    }

    public function test_finhero_academic_year_boundary(): void
    {
        $service = new FinheroBadgeCalculatorService();

        $this->assertSame('2025-2026', $service->getAcademicYear(4, 2025));
        $this->assertSame('2024-2025', $service->getAcademicYear(3, 2025));
    }

    public function test_engagement_accumulated_points_sum_across_months_and_badge_lookup(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $service = new BadgeCalculatorService();

        StudentBadgeRecord::create([
            'student_id' => $student->id,
            'month' => 4, 'year' => 2025,
            'academic_year' => '2025-2026',
            'required_assigned' => 1, 'required_completed' => 1,
            'optional_completed' => 0, 'participation_points' => 0,
            'monthly_badge' => 'BRONZE', 'monthly_badge_points' => 1,
            'accumulated_points' => 1, 'accumulated_badge' => 'NONE',
        ]);

        // current month (5) not yet written - should be excluded from the sum,
        // only April's 1 point plus the 4 passed in for May should count
        $accumulated = $this->callAccumulatedPoints($service, $student->id, '2025-2026', 5, 2025, 4);

        $this->assertSame(5, $accumulated);
        $this->assertSame('NONE', $service->determineAccumulatedBadge($accumulated));
        $this->assertSame('PLATINUM', $service->determineAccumulatedBadge(32));
    }

    public function test_finhero_accumulated_points_sum_across_months_and_badge_lookup(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $service = new FinheroBadgeCalculatorService();

        FinheroBadgeRecord::create([
            'student_id' => $student->id,
            'month' => 4, 'year' => 2025,
            'academic_year' => '2025-2026',
            'quiz_pts' => 5, 'library_pts' => 0, 'activity_pts' => 0,
            'total_earned' => 5, 'total_available' => 10, 'monthly_pct' => 50,
            'monthly_badge' => 'ROOKIE', 'monthly_badge_points' => 1,
            'accumulated_points' => 1, 'accumulated_badge' => 'NONE',
        ]);

        $accumulated = $this->callAccumulatedPoints($service, $student->id, '2025-2026', 5, 2025, 4);

        $this->assertSame(5, $accumulated);
        $this->assertSame(FinheroBadgeRecord::ACCUMULATED_THRESHOLDS['LEGEND'] ?? null, 32);
    }

    private function callAccumulatedPoints(object $service, int $studentId, string $academicYear, int $month, int $year, int $currentPoints): int
    {
        $method = (new \ReflectionClass($service))->getMethod('sumAccumulatedPoints');
        $method->setAccessible(true);
        $recordClass = $service instanceof BadgeCalculatorService ? StudentBadgeRecord::class : FinheroBadgeRecord::class;

        return $method->invoke($service, $recordClass, $studentId, $academicYear, $month, $year, $currentPoints);
    }
}
