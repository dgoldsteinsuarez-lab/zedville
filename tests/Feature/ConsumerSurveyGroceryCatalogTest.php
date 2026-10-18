<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * bugs.md #19: the consumer-profile survey's grocery step needs 11 real
 * items per diet (10 selectable + 1 "cooking oil") before its Continue
 * button can ever enable. Confirms the real catalog seeded from Daniel's
 * pricing doc actually satisfies that for all four diets.
 */
class ConsumerSurveyGroceryCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_diet_has_at_least_eleven_grocery_items_including_cooking_oil(): void
    {
        $student = User::factory()->create(['role' => 4]);

        foreach (['vegetarian', 'vegan', 'omnivore', 'pescatarian'] as $diet) {
            $response = $this->actingAs($student)->getJson("/api/groceries/{$diet}");

            $response->assertOk();
            $items = $response->json();

            $this->assertGreaterThanOrEqual(11, count($items), "Diet '{$diet}' has fewer than 11 items");

            $hasCookingOil = collect($items)->contains(
                fn ($item) => str_contains(strtolower($item['product_name']), 'cooking oil')
            );
            $this->assertTrue($hasCookingOil, "Diet '{$diet}' has no Cooking Oil item");
        }
    }

    public function test_fractional_quantities_are_preserved_not_rounded(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->getJson('/api/groceries/vegetarian');

        $mixedNuts = collect($response->json())->firstWhere('product_name', 'Mixed Nuts');

        $this->assertNotNull($mixedNuts);
        $this->assertEquals(0.80, (float) $mixedNuts['quantity']);
    }
}
