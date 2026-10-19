<?php

namespace Tests\Feature\Engagement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Substitute Goods activity (2026-10-19) - Engagement badge, not FinHero
 * (moved after Daniel caught it was wired into the wrong badge, and
 * renamed away from "Task 6"). The page itself is narrative + a live
 * "needs a substitute" count - the actual swapping happens on the
 * Supermarket's basket panel, not here.
 */
class SubstituteGoodsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_substitute_goods_page_renders(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get(route('engagement.substitute-goods'));

        $response->assertOk();
        $response->assertSee('Shopping Basket Update');
        $response->assertSee('substitute good');
    }
}
