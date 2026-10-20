<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumAttemptAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = ['attempt_id', 'question_id', 'option_id', 'is_correct'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt()
    {
        return $this->belongsTo(CurriculumAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(CurriculumQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(CurriculumQuestionOption::class, 'option_id');
    }
}
