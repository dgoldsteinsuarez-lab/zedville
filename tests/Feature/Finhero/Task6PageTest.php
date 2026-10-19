<?php

namespace Tests\Feature\Finhero;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Substitute Goods feature, FinHero Task 6 (2026-10-19). The task page
 * itself is narrative + a live "needs a substitute" count - the actual
 * swapping happens on the Supermarket's basket panel, not here.
 */
class Task6PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_6_page_renders(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $response = $this->actingAs($student)->get('/finhero/task-6');

        $response->assertOk();
        $response->assertSee('Shopping Basket Update');
        $response->assertSee('substitute good');
    }
}
