<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Who an activity is assigned to, from an admin form's Class (or Grade)
 * select. Daniel, 2026-10-26: "make all the activities available or
 * repeatable for every school and classes made, not just only one school".
 * Besides one class/grade, the select offers:
 *   SCHOOL - every class (grade) of the school being managed;
 *   ALL    - every class (grade) of every school (super admins only).
 * Each class/grade carries its own school (sid), which is what students'
 * pages match an assignment on.
 */
class AssignmentScope
{
    public const SCHOOL = 'school';
    public const ALL = 'all';

    public static function isMany(?string $choice): bool
    {
        return in_array($choice, [self::SCHOOL, self::ALL], true);
    }

    /** May the signed-in admin assign to every school? */
    public static function canUseAll(): bool
    {
        return (bool) Auth::user()?->isSuperAdmin();
    }

    /** The classes ({id, sid, name}) a choice covers; empty when it covers none or isn't allowed. */
    public static function classes(?string $choice, $selectedSchool): Collection
    {
        return self::rows('classes', $choice, $selectedSchool);
    }

    /** The grades ({id, sid, name}) a choice covers. */
    public static function grades(?string $choice, $selectedSchool): Collection
    {
        return self::rows('grades', $choice, $selectedSchool);
    }

    private static function rows(string $table, ?string $choice, $selectedSchool): Collection
    {
        $query = DB::table($table)->orderBy('sid')->orderBy('name')->select('id', 'sid', 'name')
            // on an activity screen of one level (AdminLevel): "every class" is every class of that level
            ->when(AdminLevel::id(), fn ($q, $level) => $table === 'grades'
                ? $q->where('grade_level_id', $level)
                : $q->whereIn('grade_id', DB::table('grades')->where('grade_level_id', $level)->select('id')));

        if ($choice === self::ALL) {
            return self::canUseAll() ? $query->get() : collect();
        }

        if ($choice === self::SCHOOL) {
            return $selectedSchool ? $query->where('sid', $selectedSchool)->get() : collect();
        }

        return $choice ? $query->where('id', (int) $choice)->get() : collect();
    }
}
