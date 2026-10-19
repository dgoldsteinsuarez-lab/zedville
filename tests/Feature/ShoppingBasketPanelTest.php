<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ConsumerSurvey;
use App\Models\Supermarket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Substitute Goods feature (2026-10-19): the broad Consumer Profile
 * survey breakdown (lunch/transport/activities/restaurants, not just
 * groceries) is gone from the Supermarket; a focused "My Shopping
 * Basket" panel takes its place on both live Supermarket page variants.
 */
class ShoppingBasketPanelTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudentWithSurvey(): User
    {
        $student = User::factory()->create(['role' => 4, 'sid' => 1, 'grade' => null]);
        BankAccount::create([
            'student_id' => $student->id,
            'sid' => 1,
            'student_name' => $student->name,
            'student_email' => $student->email,
        ]);
        $chicken = Supermarket::create(['product_name' => 'Chicken', 'type' => 'omnivore', 'category' => 'Needs', 'price' => 10, 'quantity' => 1]);
        ConsumerSurvey::create([
            'user_id' => $student->id,
            'sid' => 1,
            'diet' => 'omnivore',
            'groceries' => [['id' => $chicken->id, 'name' => 'Chicken', 'unit_price' => 10, 'quantity' => 1, 'total_price' => 10]],
            'basket_cost' => 10,
            'lunch' => 0,
            'transport' => 0,
            'activities' => 0,
            'restaurants' => 0,
            'total_cost' => 10,
        ]);

        return $student;
    }

    public function test_supermarket_page_shows_the_basket_panel_not_the_broad_survey_breakdown(): void
    {
        $student = $this->makeStudentWithSurvey();

        $response = $this->actingAs($student)->get(route('supermarket.supermarket', 'omnivore'));

        $response->assertOk();
        $response->assertSee('My Shopping Basket');
        $response->assertSee('substitute goods');
        $response->assertDontSee('My Consumer Profile');
    }

    public function test_spending_activities_supermarket_page_shows_the_basket_panel_too(): void
    {
        $student = $this->makeStudentWithSurvey();

        $response = $this->actingAs($student)->get(route('spendingactivities.supermarket', 'omnivore') . '?activity_id=1');

        $response->assertOk();
        $response->assertSee('My Shopping Basket');
        $response->assertDontSee('My Consumer Profile');
    }
}
