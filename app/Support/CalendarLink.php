<?php

namespace App\Support;

use App\Models\CurriculumItem;
use App\Models\EasyActivity;
use Illuminate\Support\Facades\DB;

/**
 * Where a calendar activity takes the student (Daniel, 2026-10-25: "en
 * Inicio no puede clicar sobre actividades e ir a la actividad"). Same
 * destinations as the Education department's blackboard; the event's
 * stored title says which activity it is (see CalendarTitle).
 */
class CalendarLink
{
    public static function forStudent(?string $title, $user): ?string
    {
        $title = (string) $title;

        switch ($title) {
            case 'Needs, Wants & Savings Match':
                return route('education.index');
            case 'Emergency Fund':
                return route('education.educational_finance_department', ['open' => 'emergency-fund']);
            case 'Monthly Donation':
                return route('bank.transfer', ['to' => 'donation']);
            case 'Spending budget activity':
            case 'Low spending activity':
                return route('education.high-spending-activities');
            case 'Substitute Goods: My Shopping Basket':
                return route('engagement.substitute-goods');
        }

        if (str_starts_with($title, 'task-') || str_starts_with($title, 'library-')) {
            [$type, $name] = explode('-', $title, 2);
            // The student's own school first, then one saved for every school.
            $registry = DB::table('finhero_activity_registry')->where('type', $type)->where('activity_name', $name);
            $key = (clone $registry)->where('sid', $user->sid)->value('activity_key')
                ?? (clone $registry)->whereNull('sid')->value('activity_key');

            if (!$key) {
                return null;
            }

            $key = str_replace('_', '-', $key);

            return $type === 'task' ? route('task.page', ['task' => $key]) : route('library.page', ['key' => $key]);
        }

        if (str_starts_with($title, EasyActivity::CALENDAR_PREFIX)) {
            return EasyActivity::where('title', substr($title, strlen(EasyActivity::CALENDAR_PREFIX)))->first()?->studentUrl();
        }

        if (str_starts_with($title, 'exam-')) {
            $item = CurriculumItem::where('title', substr($title, 5))->where('is_real_exam', true)->value('id');

            return $item ? route('academy.item', $item) : null;
        }

        return null;
    }
}
