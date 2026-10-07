<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityProposal extends Model
{
    protected $table = 'city_proposals';

    protected $fillable = [
        'sid',
        'grade_id',
        'type',
        'value',
        'proposed_by',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function proposer()
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    /**
     * The current live value for a Grade: most recently approved row of
     * that type. Changing the city name/quote is always Grade-wide, never
     * per-class or per-school (a school can have several Grades, each with
     * its own simulated city).
     */
    public static function current(string $type, $gradeId): ?string
    {
        return static::where('type', $type)
            ->where('grade_id', $gradeId)
            ->where('status', 'approved')
            ->latest('reviewed_at')
            ->value('value');
    }
}
