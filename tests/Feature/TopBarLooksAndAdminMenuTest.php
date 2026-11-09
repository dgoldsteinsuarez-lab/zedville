<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-28/29: the student top bar (his skyline as a silhouette on a
 * soft sky - picked from several designs) and the admin menu opening its
 * sections on click only - no submenu popping out on hover.
 */
class TopBarLooksAndAdminMenuTest extends TestCase
{
    use RefreshDatabase;

    /** Daniel, 2026-10-29: his own skyline as a deep forest silhouette - "I want soft sky". */
    public function test_the_top_bar_is_a_soft_sky_with_the_skyline_between_its_buttons(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get(route('dashboard'))->assertOk()
            ->assertSeeInOrder(['<header class="flex-shrink-0 zv-top">', 'class="zv-toggle', '<div class="zv-top-sky" aria-hidden="true"></div>',
                'class="zv-lang', 'class="zv-chip'], false)
            ->assertSee('skyline-wide.png', false)
            ->assertSee('linear-gradient(180deg, #A9DCF5 0%, #DDF2FC 100%)', false)
            ->assertDontSee('class="zv-top-pick"', false);
    }

    /** "Lets take this zedville green" - the skyline in the buttons' green; nothing left to compare. */
    public function test_the_skyline_is_zedville_green(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get(route('dashboard', ['top' => 'b']))->assertOk()
            ->assertSee('--zt-sky: #00A47D;', false)
            ->assertSee('<header class="flex-shrink-0 zv-top">', false)
            ->assertDontSee('zv-top--', false)
            ->assertDontSee('class="zv-top-pick"', false);
    }

    public function test_the_admin_menu_has_no_hover_pop_outs(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $this->actingAs($admin)->get('/admin/finhero/activities')->assertOk()
            ->assertDontSee(':hover > .collapseNavItems', false)
            ->assertSee('data-bs-toggle="collapse" data-bs-target="#usernav"', false); // opens on click
    }
}
