<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignSend;
use App\Models\SchoolMonthSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Sends any `email_campaigns` row whose trigger has come due. Run daily
 * (see Kernel.php). Safe to run more than once a day - per-student
 * idempotency is checked against email_campaign_sends before every send.
 *
 *   php artisan email-campaigns:dispatch-due              dry run (default)
 *   php artisan email-campaigns:dispatch-due --write       actually sends
 *
 * `immediate` campaigns are not handled here - the admin "Send now" action
 * sends them straight away via EmailCampaign::sendToStudents(), the same
 * method this command's --write path uses.
 */
class DispatchDueEmailCampaigns extends Command
{
    protected $signature = 'email-campaigns:dispatch-due {--write}';
    protected $description = 'Send scheduled email campaigns whose trigger date has arrived';

    public function handle()
    {
        $write = (bool) $this->option('write');
        $now = Carbon::now();
        $sentTotal = 0;

        $campaigns = EmailCampaign::where('status', 'scheduled')
            ->whereIn('trigger_type', ['fixed_date', 'relative_to_school_start', 'recurring_monthly_day'])
            ->get();

        foreach ($campaigns as $campaign) {
            $dueStudentIds = $this->dueRecipientIds($campaign, $now);

            if (empty($dueStudentIds)) {
                continue;
            }

            $this->line("Campaign #{$campaign->id} \"{$campaign->name}\": " . count($dueStudentIds) . ' recipient(s) due');

            if ($write) {
                $sentTotal += $campaign->sendToStudents($dueStudentIds);

                if ($campaign->trigger_type === 'fixed_date') {
                    $campaign->update(['status' => 'sent', 'last_run_at' => $now]);
                } else {
                    $campaign->update(['last_run_at' => $now]);
                }
            }
        }

        $this->comment($write ? "Sent to {$sentTotal} recipient(s) total." : 'Dry run - nothing sent. Add --write to send.');

        return self::SUCCESS;
    }

    /**
     * Student ids this campaign is due to reach right now: in its target
     * scope, and not already sent to for the current cycle.
     */
    private function dueRecipientIds(EmailCampaign $campaign, Carbon $now): array
    {
        $allRecipients = $campaign->resolveRecipientIds();
        if (empty($allRecipients)) {
            return [];
        }

        $cycleStart = $this->cycleStart($campaign, $now);
        if ($cycleStart === null || $cycleStart->greaterThan($now)) {
            return []; // not due yet for anyone
        }

        $alreadySent = EmailCampaignSend::where('campaign_id', $campaign->id)
            ->where('sent_at', '>=', $cycleStart)
            ->pluck('student_id')
            ->all();

        return array_values(array_diff($allRecipients, $alreadySent));
    }

    /**
     * The start of this campaign's current send cycle - a student with no
     * email_campaign_sends row at or after this moment is still due.
     * Returns null if nothing is due yet at all (e.g. a fixed_date still
     * in the future).
     */
    private function cycleStart(EmailCampaign $campaign, Carbon $now): ?Carbon
    {
        if ($campaign->trigger_type === 'fixed_date') {
            return $campaign->fixed_date?->lessThanOrEqualTo($now) ? $campaign->fixed_date : null;
        }

        if ($campaign->trigger_type === 'recurring_monthly_day') {
            $day = min($campaign->recurring_day_of_month ?? 1, $now->daysInMonth);
            $target = $now->copy()->startOfMonth()->addDays($day - 1);
            return $target->lessThanOrEqualTo($now) ? $target->startOfDay() : null;
        }

        if ($campaign->trigger_type === 'relative_to_school_start') {
            // scope_school_id is required for this trigger type (validated
            // on create) - a single school's start_month drives the date,
            // same as the "school" scope itself.
            $startMonth = SchoolMonthSetting::where('school_id', $campaign->scope_school_id)->value('start_month');
            if (!$startMonth) {
                return null; // school hasn't configured a start month yet
            }

            $targetYear = $now->month >= $startMonth ? $now->year : $now->year - 1;
            $target = Carbon::create($targetYear, $startMonth, 1)
                ->addDays($campaign->relative_days_offset ?? 0)
                ->startOfDay();

            return $target->lessThanOrEqualTo($now) ? $target : null;
        }

        return null;
    }
}
