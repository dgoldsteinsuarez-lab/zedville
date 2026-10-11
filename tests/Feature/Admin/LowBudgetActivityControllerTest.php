<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\LowBudgetActivityController - written
 * while extracting it out of AdminEducationController (planning.md
 * Phase 3 #4). Previously uncovered.
 *
 * Confirms the pre-existing (not fixed in this pass - see the class
 * docblock and bugs.md) behavior survived the move exactly: the
 * hardcoded id=4 "slot" and the session('sid') (not 'selected_school')
 * lookup.
 */
class LowBudgetActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_a_low_budget_position_inserts_at_the_hardcoded_id_slot(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'required',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mba_position', [
            'id' => 4,
            'user_id' => $admin->id,
            'activity_key' => 'lba_position',
            'required' => 1,
        ]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'Spending Low activity']);
    }
}
