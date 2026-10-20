<?php

namespace Tests\Feature;

use App\Models\ConsumerSurvey;
use App\Models\User;
use App\Services\ConsumerSurveySummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel asked early on ("where did you put the aggregate basket of all
 * students to calculate?") for a city-wide, CPI-style average basket cost
 * per month - deferred twice (planning.md Phase 7/8) until now. Same
 * cohort-filter/group-by-month shape as MoodController::city_mood(),
 * built against consumer_surveys instead of mood_logs.
 */
class CityBasketTrendTest extends TestCase
{
    use RefreshDatabase;

    private function surveyAt(User $user, float $basketCost, \DateTimeInterface $createdAt): ConsumerSurvey
    {
        $survey = ConsumerSurvey::create([
            'user_id' => $user->id,
            'basket_cost' => $basketCost,
        ]);
        $survey->created_at = $createdAt;
        $survey->save();

        return $survey;
    }

    public function test_averages_basket_cost_per_month_across_the_same_cohort(): void
    {
        $viewer = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => 5]);
        $classmate = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => 5]);

        $this->surveyAt($viewer, 100, now()->subMonth());
        $this->surveyAt($classmate, 120, now()->subMonth());
        $this->surveyAt($viewer, 150, now());

        $trend = ConsumerSurveySummaryService::cityBasketTrend($viewer);

        $this->assertCount(2, $trend);

        // Most recent month first.
        $this->assertSame(150.0, $trend[0]['avgBasketCost']);
        $this->assertSame(1, $trend[0]['studentCount']);

        $this->assertSame(110.0, $trend[1]['avgBasketCost']);
        $this->assertSame(2, $trend[1]['studentCount']);
    }

    public function test_a_different_grade_is_excluded_from_the_average(): void
    {
        $viewer = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => 5]);
        $otherGrade = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => 6]);

        $this->surveyAt($viewer, 100, now());
        $this->surveyAt($otherGrade, 999, now());

        $trend = ConsumerSurveySummaryService::cityBasketTrend($viewer);

        $this->assertCount(1, $trend);
        $this->assertSame(100.0, $trend[0]['avgBasketCost']);
        $this->assertSame(1, $trend[0]['studentCount']);
    }

    public function test_city_map_page_renders_with_no_survey_data_yet(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/education/city')
            ->assertOk()
            ->assertSee('Not enough data yet');
    }

    public function test_city_map_page_shows_the_current_month_average(): void
    {
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => 5]);
        $this->surveyAt($student, 80, now());

        $this->actingAs($student)->get('/education/city')
            ->assertOk()
            ->assertSee('80.00');
    }
}
