<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Daniel, 2026-10-30: "Squeeze in that we can remove the scrolling up and
 * down ... remove those that look like a dead space" - the home page has no
 * band that's mostly empty: the mailbox sits beside the greeting, the
 * building links in the "Enter Zedville" box.
 */
class DashboardFitsOneScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_mailbox_and_the_building_links_share_rows_with_the_greeting_and_the_city(): void
    {
        $class = SchoolClass::create(['sid' => '1', 'name' => 'Class ' . uniqid()]);
        $student = User::factory()->create(['role' => 4, 'sid' => '1', 'grade' => $class->id]);

        $this->actingAs($student)->get(route('dashboard'))->assertOk()
            ->assertSeeInOrder([
                '<h1 class="text-xl font-bold whitespace-nowrap">', 'class="zv-mailchip', 'Mailbox · no new messages', 'class="zv-mailchip-open">Open</span>',
                '<section aria-label="Enter the city"', 'Enter Zedville', 'aria-label="Building quick links"',
                'class="zv-quicklink">🏬 City Mall', 'class="zv-quicklink">🛒 Supermarket', 'class="zv-quicklink">🏦 Bank',
                'class="zv-quicklink">🎓 Education Finance Department', 'Go to the city', '</section>',
                'aria-label="Tasks and money"', 'aria-label="Badges and mood"',
            ], false)
            ->assertDontSee('<section aria-label="Mailbox">', false) // no band of its own at the bottom
            ->assertDontSee('whiteBtn zv-quicklink', false);
    }
}
