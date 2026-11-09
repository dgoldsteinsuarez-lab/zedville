<?php

namespace App\Services;

use App\Support\ClassColor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Assigns one of the class activities kept in mba_position (Emergency
 * Fund, Needs/Wants/Savings Match, Spending Activity) to a class: its
 * position row - one per class and activity, updated if the class has it
 * already - and the class's calendar event for this month. Used when an
 * admin assigns an activity to many classes at once (AssignmentScope);
 * same rows the one-class forms write.
 */
class ClassActivityAssigner
{
    /**
     * @param object $class    {id, sid}
     * @param array  $activity key, title, description, position (required|optional),
     *                         start_date, end_date, and optionally activity_id, activitys
     */
    public static function assign(object $class, array $activity): void
    {
        $position = $activity['position'];
        $values = [
            'user_id' => Auth::id(),
            'sid' => $class->sid,
            'required' => $position === 'required' ? 1 : 0,
            'optional' => $position === 'optional' ? 1 : 0,
            'start_date' => $activity['start_date'] ?? null,
            'end_date' => $activity['end_date'] ?? null,
            'updated_at' => now(),
        ];
        if (isset($activity['activity_id'])) {
            $values['activity_id'] = $activity['activity_id'];
        }

        $existing = DB::table('mba_position')->where('activity_key', $activity['key'])->where('cid', $class->id)->value('id');
        if ($existing) {
            DB::table('mba_position')->where('id', $existing)->update($values);
        } else {
            DB::table('mba_position')->insert($values + ['activity_key' => $activity['key'], 'cid' => $class->id, 'created_at' => now()]);
        }

        // FullCalendar's `end` is exclusive: the last day is stored one day
        // later (see MbaPositionController).
        $event = [
            'position' => $position,
            'backgroundColor' => ClassColor::hex((int) $class->id),
            'borderColor' => ClassColor::hex((int) $class->id),
            'start' => !empty($activity['start_date']) ? Carbon::parse($activity['start_date'])->startOfDay() : now(),
            'end' => !empty($activity['end_date']) ? Carbon::parse($activity['end_date'])->addDay()->startOfDay() : now(),
            'updated_at' => now(),
        ];
        if (!empty($activity['activitys'])) {
            $event['activitys'] = 1;
        }

        $eventId = DB::table('calendar_events')
            ->where('sid', $class->sid)
            ->where('classId', $class->id)
            ->where('title', $activity['title'])
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->value('id');

        if ($eventId) {
            DB::table('calendar_events')->where('id', $eventId)->update($event);
        } else {
            DB::table('calendar_events')->insert($event + [
                'sid' => $class->sid,
                'classId' => $class->id,
                'title' => $activity['title'],
                'description' => $activity['description'],
                'repeatActivity' => '0',
                'created_at' => now(),
            ]);
        }
    }
}
