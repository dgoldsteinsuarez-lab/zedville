<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryBonus extends Model
{
    protected $table = 'salary_bonuses';

    protected $fillable = [
        'sid',
        'student_id',
        'granted_by',
        'amount',
        'reason',
        'frequency',
        'start_date',
        'end_date',
        'last_applied_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'last_applied_at' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
