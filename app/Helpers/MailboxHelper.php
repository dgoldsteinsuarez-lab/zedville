<?php

namespace App\Helpers;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\View;

class MailboxHelper
{
    /**
     * Render a mailbox template as an HTML string, for the event-triggered
     * pipeline (MailboxScheduler/SendMailboxEmail) - $templateName is the
     * literal template key config/mailbox_schedule.php's event entries use
     * (e.g. 'welcome', 'bank-account-ready'), which also now doubles as
     * email_templates.trigger_event's join key.
     *
     * Checks the DB first - an admin editing this template's content in
     * the new Email Template admin screen is what takes over for an event,
     * same {{name}}/{{citizenId}}-style placeholders either way (filled in
     * by the caller, SendMailboxEmail::handle()). Falls back to the
     * original static Blade file in resources/views/mailbox_templates/ if
     * no DB row has been created for it yet, so nothing already working
     * breaks before an admin actually edits it (bugs.md, 2026-10-20).
     *
     * @param string $templateName  (e.g., 'welcome')
     * @param array $data           (e.g., ['user' => $user])
     * @return string
     */
    public static function renderMailboxTemplate(string $templateName, array $data = []): string
    {
        $dbTemplate = EmailTemplate::where('trigger_event', $templateName)->first();

        if ($dbTemplate) {
            return self::applyPlaceholders($dbTemplate->content, $data);
        }

        $viewPath = "mailbox_templates.{$templateName}";

        if (!View::exists($viewPath)) {
            return "Template '{$templateName}' not found.";
        }

        return view($viewPath, $data)->render();
    }

    /**
     * {{name}}/{{citizenId}}-style substitution, matching
     * SendMailboxEmail::handle()'s existing subject-line substitution -
     * DB-stored content is plain HTML (CKEditor output), not a Blade view,
     * so it needs the same treatment applied here instead of Blade's own
     * variable interpolation. Public: also used directly by
     * DispatchDueEmailCampaigns, which already has the exact EmailTemplate
     * row in hand and has no need to go through the trigger_event lookup
     * above (that lookup is only for the event pipeline, which only knows
     * a template *key*, not which row to use).
     */
    public static function applyPlaceholders(string $content, array $data): string
    {
        $user = $data['user'] ?? null;

        $replacements = array_merge([
            'citizenId' => $user->citizenId ?? '',
            'name' => $user->nickname ?? '',
        ], array_diff_key($data, ['user' => true]));

        foreach ($replacements as $key => $value) {
            if (is_scalar($value)) {
                $content = str_replace('{{' . $key . '}}', (string) $value, $content);
            }
        }

        return $content;
    }
}
