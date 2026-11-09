<?php

namespace Tests\Feature;

use App\Models\ConsumerSurvey;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19 (bugs.md), repeated 2026-10-23: no "Monthly Groceries /
 * 11 Products Compulsory / View Survey Result" card at the bottom of the
 * Supermarket - it had been removed from the shop pages but not from the two
 * market lists.
 */
class MarketListNoGroceryCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_neither_market_list_shows_the_monthly_groceries_card(): void
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);
        ConsumerSurvey::create([
            'user_id' => $student->id, 'sid' => '1', 'diet' => 'omnivore', 'groceries' => [], 'basket_cost' => 10,
            'lunch' => 0, 'transport' => 0, 'activities' => 0, 'restaurants' => 0, 'total_cost' => 10,
            'survey_month' => now()->format('M-Y'),
        ]);

        // The Spending Activity list is always opened for an activity.
        foreach ([route('supermarket.market-list'), route('spendingactivities.market-list', ['activity_id' => 1])] as $url) {
            $this->actingAs($student)->get($url)
                ->assertOk()
                ->assertDontSee('Monthly Groceries')
                ->assertDontSee('Products Compulsory')
                ->assertDontSee('View Survey Result')
                ->assertDontSee('id="resultModal"', false);
        }
    }
}
