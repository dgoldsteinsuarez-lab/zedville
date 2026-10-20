<?php

namespace Tests\Feature;

use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\SchoolDomain;
use App\Models\SchoolMonthSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The scheduled side of the Email Template / campaign system rebuild
 * (bugs.md, 2026-10-20) - fixed_date, relative_to_school_start and
 * recurring_monthly_day triggers, each checked for both "fires once due"
 * and "doesn't double-send on a second run the same cycle."
 */
class DispatchDueEmailCampaignsTest extends TestCase
{
    use RefreshDatabase;

    private function template(): EmailTemplate
    {
        return EmailTemplate::create(['name' => 'Test', 'subject' => 'Subject', 'content' => 'Hello {{name}}!']);
    }

    public function test_fixed_date_in_the_future_is_not_sent(): void
    {
        $student = User::factory()->create(['role' => 4]);
        EmailCampaign::create([
            'template_id' => $this->template()->id,
            'name' => 'Future',
            'scope_type' => 'selected',
            'selected_user_ids' => [$student->id],
            'trigger_type' => 'fixed_date',
            'fixed_date' => now()->addDays(5),
            'status' => 'scheduled',
        ]);

        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);

        $this->assertDatabaseMissing('mailbox', ['student_id' => $student->id]);
    }

    public function test_fixed_date_in_the_past_is_sent_once_and_marked_sent(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $campaign = EmailCampaign::create([
            'template_id' => $this->template()->id,
            'name' => 'Due',
            'scope_type' => 'selected',
            'selected_user_ids' => [$student->id],
            'trigger_type' => 'fixed_date',
            'fixed_date' => now()->subDay(),
            'status' => 'scheduled',
        ]);

        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertDatabaseHas('mailbox', ['student_id' => $student->id]);
        $this->assertSame('sent', $campaign->fresh()->status);

        // Running again must not double-send.
        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertSame(1, \DB::table('mailbox')->where('student_id', $student->id)->count());
    }

    public function test_recurring_monthly_day_fires_once_this_month_and_again_next_month(): void
    {
        $student = User::factory()->create(['role' => 4]);
        $campaign = EmailCampaign::create([
            'template_id' => $this->template()->id,
            'name' => 'Monthly',
            'scope_type' => 'selected',
            'selected_user_ids' => [$student->id],
            'trigger_type' => 'recurring_monthly_day',
            'recurring_day_of_month' => now()->day,
            'status' => 'scheduled',
        ]);

        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertSame(1, \DB::table('mailbox')->where('student_id', $student->id)->count());

        // Same month, running again - still just 1.
        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertSame(1, \DB::table('mailbox')->where('student_id', $student->id)->count());

        // Still "scheduled" (recurring campaigns never flip to "sent").
        $this->assertSame('scheduled', $campaign->fresh()->status);

        // Backdate the one send log row to "last month" and re-run - fires again.
        \DB::table('email_campaign_sends')
            ->where('campaign_id', $campaign->id)
            ->update(['sent_at' => now()->subMonthNoOverflow()]);

        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertSame(2, \DB::table('mailbox')->where('student_id', $student->id)->count());
    }

    public function test_relative_to_school_start_uses_the_schools_configured_month(): void
    {
        $school = SchoolDomain::create(['school_name' => 'Zedville Academy', 'school_code' => 'ZEDX', 'school_domain' => 'zedx.test']);
        SchoolMonthSetting::create(['school_id' => $school->id, 'start_month' => now()->month]);

        $student = User::factory()->create(['role' => 4, 'sid' => $school->id]);
        EmailCampaign::create([
            'template_id' => $this->template()->id,
            'name' => 'Start of year',
            'scope_type' => 'school',
            'scope_school_id' => $school->id,
            'trigger_type' => 'relative_to_school_start',
            'relative_days_offset' => 0,
            'status' => 'scheduled',
        ]);

        // start_month is the current month, offset 0 -> due from day 1, so
        // always due "today" for this test regardless of what day it runs.
        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);

        $this->assertDatabaseHas('mailbox', ['student_id' => $student->id]);
        $this->assertSame(1, \DB::table('mailbox')->where('student_id', $student->id)->count());

        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);
        $this->assertSame(1, \DB::table('mailbox')->where('student_id', $student->id)->count());
    }

    public function test_relative_to_school_start_does_nothing_when_the_school_has_no_setting(): void
    {
        $school = SchoolDomain::create(['school_name' => 'No Setting School', 'school_code' => 'NOSET', 'school_domain' => 'noset.test']);
        $student = User::factory()->create(['role' => 4, 'sid' => $school->id]);

        EmailCampaign::create([
            'template_id' => $this->template()->id,
            'name' => 'Start of year',
            'scope_type' => 'school',
            'scope_school_id' => $school->id,
            'trigger_type' => 'relative_to_school_start',
            'relative_days_offset' => 0,
            'status' => 'scheduled',
        ]);

        $this->artisan('email-campaigns:dispatch-due --write')->assertExitCode(0);

        $this->assertDatabaseMissing('mailbox', ['student_id' => $student->id]);
    }

    public function test_dry_run_sends_nothing(): void
    {
        $student = User::factory()->create(['role' => 4]);
        EmailCampaign::create([
            'template_id' => $this->template()->id,
            'name' => 'Due',
            'scope_type' => 'selected',
            'selected_user_ids' => [$student->id],
            'trigger_type' => 'fixed_date',
            'fixed_date' => now()->subDay(),
            'status' => 'scheduled',
        ]);

        $this->artisan('email-campaigns:dispatch-due')->assertExitCode(0);

        $this->assertDatabaseMissing('mailbox', ['student_id' => $student->id]);
    }
}
