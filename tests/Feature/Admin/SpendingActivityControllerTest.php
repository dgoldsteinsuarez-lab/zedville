<?php

namespace Tests\Feature\Admin;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Daniel, 2026-10-21: "for the spending activity, i can not see all the
 * spending activities that are in the system, I can not select any, solve
 * it." Confirmed via AskUserQuestion: one unified page with an Activity
 * dropdown, instead of two separate admin screens/nav links. This writes
 * to the same mba_position/calendar_events rows the two older controllers
 * (HighBudgetActivityController/LowBudgetActivityController, both still
 * present and covered by their own tests) already use.
 *
 * Follow-up the same day: "did you find all the activities for Spending
 * activities?" - found 20 real, hardcoded-but-unmanaged activities (10
 * High + 10 Low). Confirmed via AskUserQuestion: make them real (DB,
 * seeded in a migration) and selectable - this controller now picks a
 * specific Activity (not just High/Low) and derives activity_key from the
 * chosen Activity's own `type`, so every test below posts `activity_id`
 * instead of the old `activity: high|low` shape.
 */
class SpendingActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    private function highActivityId(): int
    {
        return Activity::where('type', 'high')->orderBy('number')->first()->id;
    }

    private function lowActivityId(): int
    {
        return Activity::where('type', 'low')->orderBy('number')->first()->id;
    }

    public function test_the_20_seeded_activities_are_listed_in_the_dropdown(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/education/spending-activity');

        $response->assertOk();
        $response->assertSee('Birthday Party Budget');
        $response->assertSee('Accessories for Seniors');
        $response->assertSee('High Spending', false);
        $response->assertSee('Low Spending', false);
    }

    public function test_index_lists_both_high_and_low_positions_together(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->highActivityId(), 'cid' => 1, 'position' => 'required',
        ]);
        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->lowActivityId(), 'cid' => 2, 'position' => 'optional',
        ]);

        $response = $this->actingAs($admin)->get('/admin/education/spending-activity');

        $response->assertOk();
        $response->assertSee('High');
        $response->assertSee('Low');
        // The list now shows which specific activity, not just the bucket.
        $response->assertSee('Birthday Party Budget');
        $response->assertSee('Accessories for Seniors');
    }

    public function test_saving_high_writes_the_same_activity_key_the_old_screen_used(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);
        $activityId = $this->highActivityId();

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $activityId, 'cid' => 1, 'position' => 'required',
        ]);

        $this->assertDatabaseHas('mba_position', [
            'activity_key' => 'hba_position', 'activity_id' => $activityId, 'cid' => 1, 'required' => 1,
        ]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'Spending budget activity', 'classId' => 1]);
    }

    public function test_saving_low_writes_the_same_activity_key_the_old_screen_used(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);
        $activityId = $this->lowActivityId();

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $activityId, 'cid' => 1, 'position' => 'optional',
        ]);

        $this->assertDatabaseHas('mba_position', [
            'activity_key' => 'lba_position', 'activity_id' => $activityId, 'cid' => 1, 'optional' => 1,
        ]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'Spending Low activity', 'classId' => 1]);
    }

    public function test_a_class_can_have_both_a_high_and_a_low_position_at_once(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->highActivityId(), 'cid' => 1, 'position' => 'required',
        ])->assertRedirect();

        $response = $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->lowActivityId(), 'cid' => 1, 'position' => 'required',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mba_position', ['activity_key' => 'hba_position', 'cid' => 1]);
        $this->assertDatabaseHas('mba_position', ['activity_key' => 'lba_position', 'cid' => 1]);
    }

    public function test_cannot_set_a_duplicate_position_for_the_same_class_and_activity(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);
        $highId = $this->highActivityId();
        $anotherHighId = Activity::where('type', 'high')->orderBy('number')->skip(1)->first()->id;

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $highId, 'cid' => 1, 'position' => 'required',
        ]);

        // Still blocked even picking a *different* High activity for the
        // same class - the duplicate check is keyed on activity_key
        // (High/Low bucket), not the specific activity, matching the old
        // screens' behaviour (one High + one Low per class, max).
        $response = $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $anotherHighId, 'cid' => 1, 'position' => 'optional',
        ]);

        $response->assertRedirect();
        $this->assertSame('This class already has this Spending Activity.', session('error'));
    }

    public function test_editing_swaps_the_assigned_activity_without_creating_a_duplicate_row(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);
        $firstHighId = $this->highActivityId();
        $secondHighId = Activity::where('type', 'high')->orderBy('number')->skip(1)->first()->id;

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $firstHighId, 'cid' => 1, 'position' => 'required',
        ]);
        $rowId = DB::table('mba_position')->where('cid', 1)->where('activity_key', 'hba_position')->value('id');

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'id' => $rowId, 'activity_id' => $secondHighId, 'cid' => 1, 'position' => 'required',
        ])->assertRedirect();

        $this->assertSame(1, DB::table('mba_position')->where('cid', 1)->where('activity_key', 'hba_position')->count());
        $this->assertDatabaseHas('mba_position', ['id' => $rowId, 'activity_id' => $secondHighId]);
    }

    public function test_deleting_a_low_position_removes_the_correct_calendar_event_not_high(): void
    {
        // Regression guard for the bug found in the 3 older "position"
        // controllers (bugs.md, 2026-10-21): their shared delete action
        // hardcodes the calendar title to "MBA", orphaning the real event.
        // This unified delete looks up the right title per activity_key.
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->highActivityId(), 'cid' => 1, 'position' => 'required',
        ]);
        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->lowActivityId(), 'cid' => 1, 'position' => 'required',
        ]);

        $lowId = DB::table('mba_position')->where('activity_key', 'lba_position')->where('cid', 1)->value('id');

        $this->actingAs($admin)->get('/admin/education/spending-activity-position/delete/' . $lowId)->assertRedirect();

        $this->assertDatabaseMissing('mba_position', ['id' => $lowId]);
        $this->assertDatabaseMissing('calendar_events', ['title' => 'Spending Low activity', 'classId' => 1]);
        $this->assertDatabaseHas('calendar_events', ['title' => 'Spending budget activity', 'classId' => 1]);
    }

    public function test_admin_can_set_a_start_and_end_date(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->highActivityId(),
            'cid' => 1,
            'position' => 'required',
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

    public function test_a_nonexistent_activity_id_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $response = $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => 999999, 'cid' => 1, 'position' => 'required',
        ]);

        $response->assertSessionHasErrors('activity_id');
        $this->assertDatabaseMissing('mba_position', ['cid' => 1]);
    }
}
