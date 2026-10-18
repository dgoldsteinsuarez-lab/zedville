<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The clickable mall-picture feature (education/city-mall.blade.php ->
 * EFDController::spending_tracker()) was already fully built but the
 * products table was empty, so every shop appeared with nothing to buy.
 * Confirms the real catalog seeded from Daniel's pricing doc actually
 * shows up through the existing hotspot routes.
 */
class CityMallCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_seeded_store_shows_its_products_through_the_existing_route(): void
    {
        $student = User::factory()->create(['role' => 4]);

        BankAccount::create([
            'student_id' => $student->id,
            'sid' => $student->sid,
            'student_name' => $student->name,
            'student_email' => $student->email,
        ]);

        $storesAndASampleItem = [
            'basicco' => 'Shirt',
            'stationery-store' => 'Writing Tools',
            'daily-essentials' => 'Toiletries',
            'tech-hub' => 'Take it Easy Phone',
            'beu-beLuxury' => 'Fashion t-shirt',
            'accessories' => 'Sunglasses',
            'bespirit-sport-shop' => 'Basic Soccer Shoes',
            'beats-music-store' => 'Acoustic Guitar',
        ];

        foreach ($storesAndASampleItem as $slug => $expectedItem) {
            $response = $this->actingAs($student)->get("/education/spending-tracker/{$slug}");
            $response->assertOk();
            $response->assertSee($expectedItem);
        }
    }
}
