<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A class as admins pick it and see it in the assignment lists: with its
 * grade, "Grade 9 · 9A" (Daniel, 2026-10-28: "put the grade together with
 * the class ... on the data and also when selecting the class"). Grade
 * names are the school's own ("Grade 8", "10 Spanish"), shown as they are.
 */
class ClassLabel
{
    public static function make(?string $grade, ?string $class): string
    {
        if ($class === null || $class === '') {
            return '';
        }

        return $grade ? $grade . ' · ' . $class : $class;
    }

    /**
     * The classes of a school (every school's when $sid is empty), each with
     * grade_name and label, in grade then class order - for the Class pickers.
     * On an activity screen of one level (App\Support\AdminLevel), only that
     * level's classes.
     */
    public static function classes($sid): Collection
    {
        return DB::table('classes as c')
            ->leftJoin('grades as g', 'g.id', '=', 'c.grade_id')
            ->when($sid, fn ($q) => $q->where('c.sid', $sid))
            ->when(AdminLevel::id(), fn ($q, $level) => $q->where('g.grade_level_id', $level))
            ->get(['c.*', 'g.name as grade_name'])
            ->each(function ($class) {
                $class->label = self::make($class->grade_name, $class->name);
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }
}
