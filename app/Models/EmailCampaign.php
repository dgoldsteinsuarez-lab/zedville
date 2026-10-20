<?php

namespace App\Models;

use App\Helpers\MailboxHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class EmailCampaign extends Model
{
    protected $table = 'email_campaigns';

    protected $fillable = [
        'template_id',
        'name',
        'created_by',
        'scope_type',
        'scope_school_id',
        'scope_grade_id',
        'selected_user_ids',
        'trigger_type',
        'fixed_date',
        'relative_days_offset',
        'recurring_day_of_month',
        'status',
        'last_run_at',
    ];

    protected $casts = [
        'selected_user_ids' => 'array',
        'fixed_date' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function sends()
    {
        return $this->hasMany(EmailCampaignSend::class, 'campaign_id');
    }

    /**
     * Resolve this campaign's scope into the student user ids it targets.
     * Reuses the same role=4 + sid/grade filtering already used
     * throughout the admin side (StaffController, ConsumerSurveySummaryService
     * ::cityBasketTrend, etc.) rather than a new targeting primitive.
     */
    public function resolveRecipientIds(): array
    {
        if ($this->scope_type === 'selected') {
            return array_map('intval', $this->selected_user_ids ?? []);
        }

        $query = User::where('role', 4);

        if ($this->scope_type === 'school' || $this->scope_type === 'grade') {
            $query->where('sid', $this->scope_school_id);
        }

        if ($this->scope_type === 'grade') {
            $query->where('grade', $this->scope_grade_id);
        }

        return $query->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Render this campaign's template and deliver it to exactly the given
     * student ids - bulk-inserts into `mailbox` (same shape
     * AdminEmailTemplateController::send() already used) and logs each
     * delivery to email_campaign_sends for DispatchDueEmailCampaigns'
     * idempotency checks. Shared by that command (for due recipients of a
     * scheduled campaign) and the controller (for an `immediate` campaign's
     * full recipient list) so the actual send path only exists once.
     */
    public function sendToStudents(array $studentIds): int
    {
        if (empty($studentIds)) {
            return 0;
        }

        $now = Carbon::now();
        $template = $this->template ?? $this->template()->first();
        $users = User::whereIn('id', $studentIds)->get()->keyBy('id');

        $mailboxRows = [];
        $sendLogRows = [];

        foreach ($studentIds as $studentId) {
            $user = $users->get($studentId);
            if (!$user) {
                continue;
            }

            $mailboxRows[] = [
                'student_id' => $studentId,
                'sid' => $user->sid,
                'subject' => $template->subject,
                'content' => MailboxHelper::applyPlaceholders($template->content, ['user' => $user]),
                'type' => 'primary',
                'read' => 0,
                'adminemail' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $sendLogRows[] = [
                'campaign_id' => $this->id,
                'student_id' => $studentId,
                'sent_at' => $now,
            ];
        }

        if (!empty($mailboxRows)) {
            Mailbox::insert($mailboxRows);
            EmailCampaignSend::insert($sendLogRows);
        }

        return count($mailboxRows);
    }
}
