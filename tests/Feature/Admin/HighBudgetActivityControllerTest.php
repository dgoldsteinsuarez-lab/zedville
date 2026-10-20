<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\HighBudgetActivityController - written
 * while extracting it out of AdminEducationController (planning.md
 * Phase 3 #4). Previously uncovered.
 */
class HighBudgetActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_a_high_budget_activity_position_and_it_creates_a_calendar_event(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->post('/admin/education/hba-position', [
            'position' => 'required',
            'cid' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mba_position', [
            'user_id' => $admin->id,
            'activity_key' => 'hba_position',
            'cid' => 1,
            'required' => 1,
        ]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'Spending budget activity', 'classId' => 1]);
    }

    public function test_admin_can_set_a_start_and_end_date_and_the_calendar_event_reflects_it(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/hba-position', [
            'position' => 'required',
            'cid' => 1,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);

        $this->assertDatabaseHas('mba_position', [
            'activity_key' => 'hba_position',
            'cid' => 1,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);
        $this->assertDatabaseHas('calendar_events', [
            'title' => 'Spending budget activity',
            'classId' => 1,
            'start' => '2026-11-02 00:00:00',
            'end' => '2026-11-07 00:00:00',
        ]);
    }
}
