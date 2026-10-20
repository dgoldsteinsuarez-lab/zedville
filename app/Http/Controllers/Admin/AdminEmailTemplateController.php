<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Grade;
use App\Models\SchoolDomain;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Email Template / campaign system - rebuilt 2026-10-20 (bugs.md/plan:
 * "Email Template / campaign system rebuild"). The old index() defaulted
 * to EmailTemplate::find(1) and 404'd - email_templates had zero rows and
 * no "create a new template" path existed at all, only edit-by-id against
 * a hardcoded 10-item sidebar. This replaces that with a real template
 * list/CRUD plus a campaign system that can target a school, a grade, or
 * hand-picked students, sent immediately or scheduled (a fixed date, N
 * days into a school's configured start month, or every month on a given
 * day) - see DispatchDueEmailCampaigns for the scheduled side.
 */
class AdminEmailTemplateController extends Controller
{
    // =========================================================================
    // TEMPLATES
    // =========================================================================

    public function templates()
    {
        $templates = EmailTemplate::orderBy('name')->get();

        return view('admin.email-template.templates', compact('templates'));
    }

    public function createTemplate()
    {
        $schools = SchoolDomain::orderBy('school_name')->get();

        return view('admin.email-template.template-form', [
            'template' => new EmailTemplate(),
            'schools' => $schools,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        $data = $this->validatedTemplate($request);

        EmailTemplate::create($data);

        return redirect()->route('admin.email-templates.index')->with('success', 'Template created successfully!');
    }

    public function editTemplate(int $id)
    {
        $template = EmailTemplate::findOrFail($id);
        $schools = SchoolDomain::orderBy('school_name')->get();

        return view('admin.email-template.template-form', compact('template', 'schools'));
    }

    public function updateTemplate(Request $request, int $id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->update($this->validatedTemplate($request));

        return redirect()->route('admin.email-templates.index')->with('success', 'Template updated successfully!');
    }

    public function destroyTemplate(int $id)
    {
        $template = EmailTemplate::findOrFail($id);

        if ($template->campaigns()->exists()) {
            return back()->with('error', 'This template has campaigns using it - cancel or delete those first.');
        }

        $template->delete();

        return redirect()->route('admin.email-templates.index')->with('success', 'Template deleted.');
    }

    private function validatedTemplate(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'sid' => 'nullable|exists:school_domains,id',
            'trigger_event' => 'nullable|string|max:100',
        ]);

        // Blank select -> "every school" (nullable sid), not the literal
        // empty string the <select> posts. `nullable` validation only
        // keeps a key in $validated when the request actually sent one,
        // so read with ?? first to avoid an undefined-key warning when it
        // was omitted entirely (as every test request here does).
        $validated['sid'] = ($validated['sid'] ?? '') ?: null;
        $validated['trigger_event'] = ($validated['trigger_event'] ?? '') ?: null;

        return $validated;
    }

    // =========================================================================
    // CAMPAIGNS
    // =========================================================================

    public function campaigns()
    {
        $campaigns = EmailCampaign::with('template')->orderByDesc('id')->get();

        return view('admin.email-template.campaigns', compact('campaigns'));
    }

    public function createCampaign()
    {
        $templates = EmailTemplate::orderBy('name')->get();
        $schools = SchoolDomain::orderBy('school_name')->get();
        $grades = Grade::orderBy('name')->get();
        $users = User::where('role', 4)->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.email-template.campaign-form', compact('templates', 'schools', 'grades', 'users'));
    }

    public function storeCampaign(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'name' => 'required|string|max:255',
            'scope_type' => 'required|in:all,school,grade,selected',
            'scope_school_id' => 'required_if:scope_type,school,grade|nullable|exists:school_domains,id',
            'scope_grade_id' => 'required_if:scope_type,grade|nullable|exists:grades,id',
            'user_ids' => 'required_if:scope_type,selected|array',
            'user_ids.*' => 'exists:users,id',
            'trigger_type' => 'required|in:immediate,fixed_date,relative_to_school_start,recurring_monthly_day',
            'fixed_date' => 'required_if:trigger_type,fixed_date|nullable|date',
            'relative_days_offset' => 'required_if:trigger_type,relative_to_school_start|nullable|integer|min:0|max:364',
            'recurring_day_of_month' => 'required_if:trigger_type,recurring_monthly_day|nullable|integer|min:1|max:31',
        ]);

        if ($validated['trigger_type'] === 'relative_to_school_start' && $validated['scope_type'] !== 'school' && $validated['scope_type'] !== 'grade') {
            return back()->withInput()->with('error', 'Scheduling by a school\'s start month needs a School or School + Grade recipient scope, so there is one school to compute the date from.');
        }

        $campaign = EmailCampaign::create([
            'template_id' => $validated['template_id'],
            'name' => $validated['name'],
            'created_by' => auth()->id(),
            'scope_type' => $validated['scope_type'],
            'scope_school_id' => $validated['scope_school_id'] ?? null,
            'scope_grade_id' => $validated['scope_grade_id'] ?? null,
            'selected_user_ids' => $validated['user_ids'] ?? null,
            'trigger_type' => $validated['trigger_type'],
            'fixed_date' => $validated['fixed_date'] ?? null,
            'relative_days_offset' => $validated['relative_days_offset'] ?? null,
            'recurring_day_of_month' => $validated['recurring_day_of_month'] ?? null,
            'status' => 'scheduled',
        ]);

        if ($validated['trigger_type'] === 'immediate') {
            $sent = $campaign->sendToStudents($campaign->resolveRecipientIds());
            $campaign->update(['status' => 'sent', 'last_run_at' => now()]);

            return redirect()->route('admin.email-campaigns.index')->with('success', "Sent to {$sent} student(s).");
        }

        return redirect()->route('admin.email-campaigns.index')->with('success', 'Campaign scheduled.');
    }

    public function cancelCampaign(int $id)
    {
        $campaign = EmailCampaign::findOrFail($id);
        $campaign->update(['status' => 'cancelled']);

        return back()->with('success', 'Campaign cancelled.');
    }
}
