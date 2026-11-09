<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SchoolMonthSetting extends Model
{
    protected $table = 'school_month_settings';

    protected $fillable = [
        'school_id',
        'start_month'
    ];

    /**
     * Which progression month (1-12, matching a `months` row's position)
     * a year that starts in $startMonth (a calendar month, 1-12) is in
     * today. Wraps every 12 months. Null without a start month.
     */
    public static function progressionMonth($startMonth): ?int
    {
        if (!$startMonth) {
            return null;
        }

        $elapsed = (now()->month - (int) $startMonth + 12) % 12;
        return $elapsed + 1;
    }

    /** The school's start month (1-12), or null if the school has no setting yet. */
    public static function schoolStartMonth($schoolId): ?int
    {
        if ($schoolId === null) {
            return null;
        }

        $start = self::where('school_id', $schoolId)->value('start_month');
        return $start === null ? null : (int) $start;
    }

    /**
     * A Grade's start month: its own (Daniel, 2026-10-31: "a grade ... can
     * be independent from the school"), else the school's.
     */
    public static function startMonthForGrade(?Grade $grade, $schoolId = null): ?int
    {
        return $grade?->start_month ?: self::schoolStartMonth($schoolId ?? $grade?->sid);
    }

    /** The progression month a student is in: their Grade's start month, else their school's. */
    public static function progressionMonthForStudent($user): ?int
    {
        return self::progressionMonth(self::startMonthForGrade(optional($user->gradeRelation)->grade, $user->sid));
    }
}
