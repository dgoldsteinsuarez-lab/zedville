<?php

namespace Tests\Feature\Console;

use App\Models\CurriculumAttempt;
use App\Models\CurriculumCategory;
use App\Models\CurriculumItem;
use App\Models\Grade;
use App\Models\GradeLevel;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "What about the game, and if he scores between the
 * best?" -> "you can build that." Top 3 of each PISA leaderboard cohort
 * get a FinHero bonus at month end (pisa:award-leaderboard-bonus,
 * scheduled alongside the existing badges:calculate-monthly job).
 */
class AwardPisaLeaderboardBonusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * $levelName is which Grade Level the student themselves belongs to -
     * deliberately irrelevant to which PISA item they're completing now
     * that PISA Practice is one global category ("independent from if a
     * student is Junior, Rookie or else, they will have the same
     * activities"), kept as a parameter so a test can prove that directly.
     */
    private function studentWithScore(int $sid, string $levelName, CurriculumItem $item, int $score): User
    {
        $level = GradeLevel::where('name', $levelName)->first();
        $grade = Grade::create(['sid' => $sid, 'name' => 'Grade ' . uniqid(), 'grade_level_id' => $level->id]);
        $class = SchoolClass::create(['sid' => $sid, 'grade_id' => $grade->id, 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => $sid, 'grade' => $class->id]);

        CurriculumAttempt::create([
            'item_id' => $item->id, 'student_id' => $student->id,
            'score' => $score, 'total_questions' => 10, 'completed_at' => now(),
        ]);

        return $student;
    }

    private function pisaItem(): CurriculumItem
    {
        $category = CurriculumCategory::where('is_pisa_practice', true)->first();

        return CurriculumItem::create(['category_id' => $category->id, 'title' => 'Item ' . uniqid(), 'type' => 'quiz', 'sort_order' => 1]);
    }

    public function test_the_top_three_in_a_cohort_earn_two_one_one_and_a_fourth_place_earns_nothing(): void
    {
        $item = $this->pisaItem();
        $first = $this->studentWithScore(1, 'Rookie', $item, 10);
        $second = $this->studentWithScore(1, 'Rookie', $item, 8);
        $third = $this->studentWithScore(1, 'Rookie', $item, 6);
        $fourth = $this->studentWithScore(1, 'Rookie', $item, 4);

        $this->artisan('pisa:award-leaderboard-bonus')->assertExitCode(0);

        $this->assertDatabaseHas('finhero_student_points', ['student_id' => $first->id, 'points_earned' => 2]);
        $this->assertDatabaseHas('finhero_student_points', ['student_id' => $second->id, 'points_earned' => 1]);
        $this->assertDatabaseHas('finhero_student_points', ['student_id' => $third->id, 'points_earned' => 1]);
        $this->assertDatabaseMissing('finhero_student_points', ['student_id' => $fourth->id]);
    }

    public function test_running_it_twice_for_the_same_month_does_not_double_award(): void
    {
        $item = $this->pisaItem();
        $student = $this->studentWithScore(1, 'Rookie', $item, 10);

        $this->artisan('pisa:award-leaderboard-bonus');
        $this->artisan('pisa:award-leaderboard-bonus');

        $this->assertSame(1, DB::table('finhero_student_points')->where('student_id', $student->id)->count());
    }

    public function test_cohorts_at_different_schools_are_scored_independently(): void
    {
        $item = $this->pisaItem();
        $schoolAWinner = $this->studentWithScore(1, 'Rookie', $item, 5);
        $schoolBWinner = $this->studentWithScore(2, 'Rookie', $item, 1);

        $this->artisan('pisa:award-leaderboard-bonus');

        // A low score at school B still wins its own cohort - school A's
        // higher scores never enter school B's ranking.
        $this->assertDatabaseHas('finhero_student_points', ['student_id' => $schoolAWinner->id, 'points_earned' => 2]);
        $this->assertDatabaseHas('finhero_student_points', ['student_id' => $schoolBWinner->id, 'points_earned' => 2]);
    }

    public function test_students_at_different_grade_levels_in_the_same_school_share_one_cohort(): void
    {
        $item = $this->pisaItem();
        $rookieWinner = $this->studentWithScore(1, 'Rookie', $item, 10);
        $intermediateRunnerUp = $this->studentWithScore(1, 'Intermediate', $item, 8);

        $this->artisan('pisa:award-leaderboard-bonus');

        $this->assertDatabaseHas('finhero_student_points', ['student_id' => $rookieWinner->id, 'points_earned' => 2]);
        $this->assertDatabaseHas('finhero_student_points', ['student_id' => $intermediateRunnerUp->id, 'points_earned' => 1]);
    }

    public function test_a_month_option_attributes_the_bonus_to_that_month_not_the_real_current_one(): void
    {
        $item = $this->pisaItem();
        $student = $this->studentWithScore(1, 'Rookie', $item, 10);
        // Back-date the attempt into the target month being awarded.
        CurriculumAttempt::where('student_id', $student->id)->update(['completed_at' => '2026-09-15 10:00:00']);

        $this->artisan('pisa:award-leaderboard-bonus --month=9 --year=2026')->assertExitCode(0);

        $this->assertDatabaseHas('finhero_student_points', [
            'student_id' => $student->id, 'month' => 9, 'year' => 2026,
        ]);
    }
}
