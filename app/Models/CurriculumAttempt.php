<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One full sitting of a quiz CurriculumItem (aggregate score). Retakes are
 * unlimited - a student can have many of these per item.
 */
class CurriculumAttempt extends Model
{
    protected $fillable = ['item_id', 'student_id', 'score', 'total_questions', 'completed_at'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(CurriculumItem::class, 'item_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function answers()
    {
        return $this->hasMany(CurriculumAttemptAnswer::class, 'attempt_id');
    }
}
