<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutopilotPeriod extends Model
{
    protected $fillable = ['sid', 'student_id', 'class_id', 'start_date', 'end_date', 'reason', 'created_by', 'status'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Active periods that overlap the given month (1st..last day). */
    public function scopeOverlappingMonth($q, int $year, int $month)
    {
        $first = sprintf('%04d-%02d-01', $year, $month);
        $last = date('Y-m-t', strtotime($first));
        return $q->where('status', 'active')->where('start_date', '<=', $last)->where('end_date', '>=', $first);
    }
}
