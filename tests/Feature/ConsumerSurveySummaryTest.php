<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Supermarket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SupermarketController and SpendingActivitiesController each had their
 * own byte-for-byte copy of "load the student's latest consumer survey,
 * compute basket/lunch/transport/activities/restaurants/total" (planning.md
 * Phase 3 #2). Extracted into ConsumerSurveySummaryService; these tests
 * confirm all four call sites still expose the exact same view variables
 * after the move.
 */
class ConsumerSurveySummaryTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithSurvey(int $sid): User
    {
        $user = User::factory()->create(['role' => 4, 'sid' => $sid, 'grade' => 1]);
        BankAccount::create([
            'student_id' => $user->id,
            'sid' => $sid,
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);
        ConsumerSurvey::create([
            'user_id' => $user->id,
            'sid' => $sid,
            'diet' => 'omnivore',
            'groceries' => json_encode([['name' => 'Milk', 'total_price' => 5]]),
            'basket_cost' => 50,
            'lunch' => 10,
            'transport' => 5,
            'activities' => 2,
            'restaurants' => 3,
            'total_cost' => 70,
            'survey_month' => now()->format('M-Y'),
        ]);

        return $user;
    }

    public function test_supermarket_market_list_exposes_survey_derived_variables(): void
    {
        $user = $this->makeStudentWithSurvey(2101);

        $response = $this->actingAs($user)->get('/supermarket/market-list');

        $response->assertOk();
        $response->assertViewHas('diet', 'omnivore');
        $response->assertViewHas('itemNames', 'Milk');
        $response->assertViewHas('total', 70.0);
        $response->assertViewHas('ConsumersurveyExists', true);
    }

    public function test_supermarket_type_page_exposes_survey_derived_variables(): void
    {
        $user = $this->makeStudentWithSurvey(2102);
        Supermarket::create(['type' => 'omnivore', 'sid' => 2102, 'product_name' => 'Test Product', 'price' => 5]);

        $response = $this->actingAs($user)->get('/supermarket/omnivore');

        $response->assertOk();
        $response->assertViewHas('itemNames', 'Milk');
        $response->assertViewHas('total', 70.0);
        $response->assertViewHas('groceries', [['name' => 'Milk', 'total_price' => 5]]);
    }

    public function test_spending_activities_market_list_exposes_survey_derived_variables(): void
    {
        $user = $this->makeStudentWithSurvey(2103);

        $response = $this->actingAs($user)->get('/spending-activities/market-list?activity_id=1');

        $response->assertOk();
        $response->assertViewHas('diet', 'omnivore');
        $response->assertViewHas('itemNames', 'Milk');
        $response->assertViewHas('total', 70.0);
    }

    public function test_spending_activities_type_page_exposes_survey_derived_variables(): void
    {
        $user = $this->makeStudentWithSurvey(2104);

        $response = $this->actingAs($user)->get('/spending-activities/omnivore?activity_id=1');

        $response->assertOk();
        $response->assertViewHas('itemNames', 'Milk');
        $response->assertViewHas('total', 70.0);
        $response->assertViewHas('groceries', [['name' => 'Milk', 'total_price' => 5]]);
    }
}
