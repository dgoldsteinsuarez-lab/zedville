<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentExtension extends Model
{
    protected $table = 'assignment_extensions';

    protected $fillable = [
        'sid',
        'calendar_event_id',
        'student_id',
        'original_due',
        'extended_to',
        'reason',
        'granted_by',
    ];

    protected $casts = [
        'original_due' => 'datetime',
        'extended_to' => 'datetime',
    ];

    public function calendarEvent()
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
