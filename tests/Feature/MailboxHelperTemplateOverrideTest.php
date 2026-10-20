<?php

namespace Tests\Feature;

use App\Helpers\MailboxHelper;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SendMailboxEmail (the registration/bank_account_opened event pipeline,
 * see MailboxScheduler/config/mailbox_schedule.php) calls
 * MailboxHelper::renderMailboxTemplate() with a template *key* like
 * 'welcome' - it should keep rendering the original static Blade file
 * from resources/views/mailbox_templates/ until a Super Admin actually
 * links an email_templates row to that key via the new admin screen, at
 * which point the DB row takes over. Neither path should break the other
 * (bugs.md, 2026-10-20).
 */
class MailboxHelperTemplateOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_falls_back_to_the_static_blade_file_when_no_db_template_is_linked(): void
    {
        $user = User::factory()->create(['role' => 4, 'nickname' => 'Robin']);

        $content = MailboxHelper::renderMailboxTemplate('welcome', ['user' => $user]);

        $this->assertStringNotContainsString("Template 'welcome' not found.", $content);
    }

    public function test_a_linked_db_template_overrides_the_static_file(): void
    {
        $user = User::factory()->create(['role' => 4, 'nickname' => 'Robin']);
        EmailTemplate::create([
            'name' => 'Custom Welcome',
            'trigger_event' => 'welcome',
            'subject' => 'Hi {{name}}',
            'content' => 'Custom welcome content for {{name}}.',
        ]);

        $content = MailboxHelper::renderMailboxTemplate('welcome', ['user' => $user]);

        $this->assertSame('Custom welcome content for Robin.', $content);
    }

    public function test_an_unknown_template_key_with_no_db_row_and_no_blade_file_reports_not_found(): void
    {
        $content = MailboxHelper::renderMailboxTemplate('does-not-exist');

        $this->assertSame("Template 'does-not-exist' not found.", $content);
    }
}
