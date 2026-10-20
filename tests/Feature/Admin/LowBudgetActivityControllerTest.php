<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * LowBudgetActivityController was structurally broken compared to its
 * three siblings (Mba/EmergencyFund/HighBudgetActivityController):
 * lba_position() read session('sid') instead of 'selected_school' (so
 * $sid was always null), had no class (cid) scoping at all, and did
 * updateOrInsert(['user_id' => $userId, 'id' => 4], ...) - treating the
 * mba_position table's auto-increment primary key as a fixed "slot"
 * reserved for this activity rather than matching on activity_key/cid
 * like the siblings do. Fixed to match the per-class pattern exactly
 * (bugs.md, 2026-10-20).
 */
class LowBudgetActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_a_low_budget_activity_position_and_it_creates_a_calendar_event(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'required',
            'cid' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mba_position', [
            'user_id' => $admin->id,
            'activity_key' => 'lba_position',
            'sid' => 5001,
            'cid' => 1,
            'required' => 1,
        ]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'Spending Low activity', 'classId' => 1, 'sid' => 5001]);
    }

    public function test_two_different_classes_can_each_have_their_own_position(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'required',
            'cid' => 1,
        ]);
        $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'optional',
            'cid' => 2,
        ]);

        $this->assertDatabaseHas('mba_position', ['cid' => 1, 'required' => 1, 'optional' => 0]);
        $this->assertDatabaseHas('mba_position', ['cid' => 2, 'required' => 0, 'optional' => 1]);
    }

    public function test_the_same_class_cannot_get_a_second_position_row(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'required',
            'cid' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'optional',
            'cid' => 1,
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(1, \DB::table('mba_position')->where('activity_key', 'lba_position')->where('cid', 1)->count());
    }

    public function test_index_page_scopes_to_the_selected_school(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'required',
            'cid' => 1,
        ]);

        session(['selected_school' => 9999]);
        $response = $this->actingAs($admin)->get('/admin/education/low-budget-activity');

        $response->assertOk();
        $response->assertViewHas('mbaList', fn ($list) => $list->isEmpty());
    }

    public function test_admin_can_set_a_start_and_end_date_and_the_calendar_event_reflects_it(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/lba-position', [
            'position' => 'required',
            'cid' => 1,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);

        $this->assertDatabaseHas('mba_position', [
            'activity_key' => 'lba_position',
            'cid' => 1,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);
        $this->assertDatabaseHas('calendar_events', [
            'title' => 'Spending Low activity',
            'classId' => 1,
            'start' => '2026-11-02 00:00:00',
            'end' => '2026-11-07 00:00:00',
        ]);
    }
}
