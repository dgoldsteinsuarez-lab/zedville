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
 * to the same mba_position/calendar_events rows HighBudgetActivityController
 * (still present and covered by its own test) already uses.
 *
 * Follow-up the same day: "did you find all the activities for Spending
 * activities?" - found 20 real, hardcoded-but-unmanaged activities (10
 * High + 10 Low). Confirmed via AskUserQuestion: make them real (DB,
 * seeded in a migration) and selectable - this controller now picks a
 * specific Activity and derives activity_key from the chosen Activity's
 * own `type`, so every test below posts `activity_id` instead of the old
 * `activity: high|low` shape.
 *
 * Later the same day: "you can remove low spending activities from the
 * system" - Low removed entirely (LowBudgetActivityController and its
 * route/view/test deleted, the 10 'low' Activity rows deleted via
 * migration). This file's tests cover High only now.
 */
class SpendingActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    private function highActivityId(): int
    {
        return Activity::where('type', 'high')->orderBy('number')->first()->id;
    }

    public function test_the_seeded_activities_are_listed_in_the_dropdown(): void
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/education/spending-activity');

        $response->assertOk();
        $response->assertSee('Birthday Party Budget');
        $response->assertDontSee('Accessories for Seniors');
    }

    public function test_index_lists_the_assigned_activity(): void
    {
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->highActivityId(), 'cid' => 1, 'position' => 'required',
        ]);

        $response = $this->actingAs($admin)->get('/admin/education/spending-activity');

        $response->assertOk();
        $response->assertSee('High');
        $response->assertSee('Birthday Party Budget');
    }

    public function test_saving_writes_the_same_activity_key_the_old_screen_used(): void
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
        // same class - the duplicate check is keyed on activity_key, not
        // the specific activity (one Spending Activity per class, max).
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

    public function test_deleting_a_position_removes_its_calendar_event(): void
    {
        // Regression guard for the bug found in the older "position"
        // controllers (bugs.md, 2026-10-21): their shared delete action
        // hardcodes the calendar title to "MBA", orphaning the real event.
        // This delete looks up the right title instead.
        $admin = User::factory()->create(['role' => 1]);
        session(['selected_school' => 5001]);

        $this->actingAs($admin)->post('/admin/education/spending-activity-position', [
            'activity_id' => $this->highActivityId(), 'cid' => 1, 'position' => 'required',
        ]);

        $id = DB::table('mba_position')->where('activity_key', 'hba_position')->where('cid', 1)->value('id');

        $this->actingAs($admin)->get('/admin/education/spending-activity-position/delete/' . $id)->assertRedirect();

        $this->assertDatabaseMissing('mba_position', ['id' => $id]);
        $this->assertDatabaseMissing('calendar_events', ['title' => 'Spending budget activity', 'classId' => 1]);
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
