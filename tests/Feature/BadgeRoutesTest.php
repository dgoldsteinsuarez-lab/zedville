<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The old /badges page and its /badges/data are gone (Daniel, 2026-11-02:
 * "bórrala" - no menu linked to it and it still showed the old rule's
 * numbers). A student's badges are on the Badges tab of their settings.
 */
class BadgeRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_old_badges_page_is_gone(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/badges')->assertNotFound();
        $this->actingAs($student)->get('/badges/data')->assertNotFound();
        $this->assertFalse(app('router')->has('badges.index'));
    }

    public function test_the_badges_are_on_the_settings_tab(): void
    {
        $student = User::factory()->create(['role' => 4]);

        $this->actingAs($student)->get('/profile')->assertOk()
            ->assertSee('My Badges')
            ->assertSee('<symbol id="m-platinum"', false);
    }
}
