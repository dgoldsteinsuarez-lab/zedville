<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A fine charged for a Required activity not done in time
 * (App\Services\ActivityFineService): one per student and calendar event.
 */
class ActivityFine extends Model
{
    protected $fillable = [
        'student_id',
        'calendar_event_id',
        'activity_title',
        'run_start',
        'run_end',
        'amount',
        'transaction_id',
    ];

    protected $casts = [
        'run_start' => 'datetime',
        'run_end' => 'datetime',
        'amount' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction1::class, 'transaction_id');
    }

    /** The activity's last day (run_end is the day after, as on the calendar). */
    public function lastDay()
    {
        return $this->run_end->copy()->subDay();
    }
}
