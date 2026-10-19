<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-19: clicking a diet option (Omnivore/Pescatarian/
 * Vegetarian/Vegan) in the Supermarket sent him to "the IT guys' website -
 * the original place they were using to build the website." Root cause:
 * the four diet tiles were hardcoded to absolute URLs on
 * "https://zedville.nexovah.in/..." - the developers' own working domain -
 * instead of a relative/APP_URL-based link, so they always took the
 * visitor there regardless of which domain the app is actually deployed
 * on. Same hardcoded-domain bug found and fixed in 5 other admin/student
 * pages (bugs.md, 2026-10-19).
 */
class SupermarketDietLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_diet_links_use_the_apps_own_domain_not_a_hardcoded_one(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('supermarket.market-list'));

        $response->assertOk();
        $response->assertDontSee('zedville.nexovah.in', false);
        $response->assertSee(route('supermarket.supermarket', 'omnivore'), false);
    }
}
