<?php

namespace Tests\Feature\Admin;

use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Grade;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The old Email Template admin page 404'd on every visit -
 * EmailTemplate::find(1) with zero rows in the table - and had no way to
 * create a new template at all. Rebuilt as a real template list/CRUD plus
 * a campaign system (school/grade/selected recipients, immediate or
 * scheduled). See the "Email Template / campaign system rebuild" plan
 * (bugs.md, 2026-10-20).
 */
class AdminEmailTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => 1]);
    }

    public function test_templates_index_no_longer_404s_with_an_empty_table(): void
    {
        $response = $this->actingAs($this->superAdmin())->get('/admin/email-templates');

        $response->assertOk();
        $response->assertSee('No templates yet');
    }

    public function test_a_super_admin_can_create_a_template(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post('/admin/email-templates', [
            'name' => 'Welcome Email',
            'subject' => 'Welcome to Zedville!',
            'content' => 'Hi {{name}}, welcome!',
        ]);

        $response->assertRedirect('/admin/email-templates');
        $this->assertDatabaseHas('email_templates', [
            'name' => 'Welcome Email',
            'subject' => 'Welcome to Zedville!',
        ]);
        $this->assertNull(EmailTemplate::where('name', 'Welcome Email')->value('sid'));
    }

    public function test_a_non_super_admin_cannot_reach_the_template_screens(): void
    {
        $schoolAdmin = User::factory()->create(['role' => 2]);

        $this->actingAs($schoolAdmin)->get('/admin/email-templates')->assertForbidden();
    }

    public function test_the_new_template_form_renders(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/email-templates/create')->assertOk();
    }

    public function test_the_edit_template_form_renders(): void
    {
        $template = EmailTemplate::create(['name' => 'Test', 'subject' => 'Subject', 'content' => 'Content']);

        $this->actingAs($this->superAdmin())
            ->get('/admin/email-templates/' . $template->id . '/edit')
            ->assertOk();
    }

    public function test_the_new_campaign_form_renders(): void
    {
        EmailTemplate::create(['name' => 'Test', 'subject' => 'Subject', 'content' => 'Content']);

        $this->actingAs($this->superAdmin())->get('/admin/email-campaigns/create')->assertOk();
    }

    public function test_the_campaigns_list_renders(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/email-campaigns')->assertOk();
    }

    public function test_updating_a_template_persists_changes(): void
    {
        $admin = $this->superAdmin();
        $template = EmailTemplate::create(['name' => 'Old name', 'subject' => 'Old subject', 'content' => 'Old content']);

        $response = $this->actingAs($admin)->put('/admin/email-templates/' . $template->id, [
            'name' => 'New name',
            'subject' => 'New subject',
            'content' => 'New content',
        ]);

        $response->assertRedirect('/admin/email-templates');
        $this->assertDatabaseHas('email_templates', ['id' => $template->id, 'name' => 'New name', 'subject' => 'New subject']);
    }

    public function test_an_immediate_campaign_sends_right_away_to_the_resolved_scope(): void
    {
        $admin = $this->superAdmin();
        $school = SchoolDomain::create(['school_name' => 'Zedville Academy', 'school_code' => 'ZED1', 'school_domain' => 'zed1.test']);
        $grade = Grade::create(['sid' => $school->id, 'name' => 'Grade 5']);

        $inGrade = User::factory()->create(['role' => 4, 'sid' => $school->id, 'grade' => $grade->id]);
        $otherGrade = User::factory()->create(['role' => 4, 'sid' => $school->id, 'grade' => null]);

        $template = EmailTemplate::create(['name' => 'Test', 'subject' => 'Hi {{name}}', 'content' => 'Hello {{name}}!']);

        $response = $this->actingAs($admin)->post('/admin/email-campaigns', [
            'template_id' => $template->id,
            'name' => 'Grade 5 blast',
            'scope_type' => 'grade',
            'scope_school_id' => $school->id,
            'scope_grade_id' => $grade->id,
            'trigger_type' => 'immediate',
        ]);

        $response->assertRedirect('/admin/email-campaigns');
        $this->assertDatabaseHas('email_campaigns', ['name' => 'Grade 5 blast', 'status' => 'sent']);
        $this->assertDatabaseHas('mailbox', ['student_id' => $inGrade->id, 'subject' => 'Hi {{name}}']);
        $this->assertDatabaseMissing('mailbox', ['student_id' => $otherGrade->id]);
        $this->assertDatabaseHas('email_campaign_sends', ['student_id' => $inGrade->id]);
    }

    public function test_an_immediate_campaign_substitutes_the_name_placeholder(): void
    {
        $admin = $this->superAdmin();
        $student = User::factory()->create(['role' => 4, 'nickname' => 'Robin']);
        $template = EmailTemplate::create(['name' => 'Test', 'subject' => 'Subject', 'content' => 'Hello {{name}}!']);

        $this->actingAs($admin)->post('/admin/email-campaigns', [
            'template_id' => $template->id,
            'name' => 'Selected blast',
            'scope_type' => 'selected',
            'user_ids' => [$student->id],
            'trigger_type' => 'immediate',
        ]);

        $this->assertDatabaseHas('mailbox', ['student_id' => $student->id, 'content' => 'Hello Robin!']);
    }

    public function test_a_scheduled_campaign_is_not_sent_immediately(): void
    {
        $admin = $this->superAdmin();
        $student = User::factory()->create(['role' => 4]);
        $template = EmailTemplate::create(['name' => 'Test', 'subject' => 'Subject', 'content' => 'Content']);

        $this->actingAs($admin)->post('/admin/email-campaigns', [
            'template_id' => $template->id,
            'name' => 'Future blast',
            'scope_type' => 'selected',
            'user_ids' => [$student->id],
            'trigger_type' => 'fixed_date',
            'fixed_date' => now()->addMonth()->toDateString(),
        ]);

        $this->assertDatabaseHas('email_campaigns', ['name' => 'Future blast', 'status' => 'scheduled']);
        $this->assertDatabaseMissing('mailbox', ['student_id' => $student->id]);
    }

    public function test_cancelling_a_campaign_stops_it_from_being_scheduled(): void
    {
        $admin = $this->superAdmin();
        $template = EmailTemplate::create(['name' => 'Test', 'subject' => 'Subject', 'content' => 'Content']);
        $campaign = EmailCampaign::create([
            'template_id' => $template->id,
            'name' => 'To cancel',
            'scope_type' => 'all',
            'trigger_type' => 'fixed_date',
            'fixed_date' => now()->addDay(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($admin)->post("/admin/email-campaigns/{$campaign->id}/cancel")->assertRedirect();

        $this->assertDatabaseHas('email_campaigns', ['id' => $campaign->id, 'status' => 'cancelled']);
    }
}
