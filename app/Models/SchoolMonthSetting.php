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
     * a school is currently in, given its configured start_month (a
     * calendar month, 1-12) and today's real calendar month. Wraps every
     * 12 months. Returns null if the school has no setting yet.
     */
    public static function currentProgressionMonthNumber(?int $schoolId): ?int
    {
        if ($schoolId === null) {
            return null;
        }

        $start = self::where('school_id', $schoolId)->value('start_month');
        if ($start === null) {
            return null;
        }

        $elapsed = (now()->month - (int) $start + 12) % 12;
        return $elapsed + 1;
    }
}
