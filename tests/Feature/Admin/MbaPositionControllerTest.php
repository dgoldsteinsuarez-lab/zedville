<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Baseline coverage for Admin\MbaPositionController - written while
 * extracting it out of AdminEducationController (planning.md Phase 3
 * #4). Previously uncovered.
 */
class MbaPositionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_an_mba_position_and_it_creates_a_calendar_event(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->post('/admin/education/mba-position', [
            'position' => 'required',
            'cid' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mba_position', [
            'user_id' => $admin->id,
            'cid' => 1,
            'required' => 1,
            'optional' => 0,
        ]);
        $this->assertDatabaseHas('calendar_events', [
            'title' => 'Needs, Wants & Savings Match',
            'classId' => 1,
            'position' => 'required',
        ]);
    }

    public function test_cannot_set_a_duplicate_mba_position_for_the_same_class(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        DB::table('mba_position')->insert([
            'user_id' => $admin->id,
            'activity_key' => 'mba',
            'sid' => 5001,
            'cid' => 1,
            'required' => 1,
            'optional' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post('/admin/education/mba-position', [
            'position' => 'optional',
            'cid' => 1,
        ]);

        $response->assertRedirect();
        $this->assertSame('This class already has an MBA Position.', session('error'));
    }

    public function test_admin_can_set_a_start_and_end_date_and_the_calendar_event_reflects_it(): void
    {
        // Daniel, 2026-10-21: "i need to be able to add a start and end
        // day of each activity... That should be added in calendar."
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->post('/admin/education/mba-position', [
            'position' => 'required',
            'cid' => 1,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mba_position', [
            'cid' => 1,
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-06',
        ]);
        // calendar_events.end is +1 day of the picked end_date - FullCalendar
        // treats `end` as exclusive, so this is what makes Nov 6 actually
        // show as part of the event instead of being cut off.
        $this->assertDatabaseHas('calendar_events', [
            'title' => 'Needs, Wants & Savings Match',
            'classId' => 1,
            'start' => '2026-11-02 00:00:00',
            'end' => '2026-11-07 00:00:00',
        ]);
    }

    public function test_an_end_date_before_the_start_date_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->post('/admin/education/mba-position', [
            'position' => 'required',
            'cid' => 1,
            'start_date' => '2026-11-06',
            'end_date' => '2026-11-02',
        ]);

        $response->assertSessionHasErrors('end_date');
        $this->assertDatabaseMissing('mba_position', ['cid' => 1]);
    }

    public function test_admin_can_delete_an_mba_position(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $id = DB::table('mba_position')->insertGetId([
            'user_id' => $admin->id,
            'activity_key' => 'mba',
            'sid' => 5001,
            'cid' => 1,
            'required' => 1,
            'optional' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get("/admin/education/mba-position/delete/{$id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('mba_position', ['id' => $id]);
    }
}
