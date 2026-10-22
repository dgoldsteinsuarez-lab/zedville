<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginPracticeAttempt extends Model
{
    protected $fillable = ['student_id', 'month_number', 'score', 'total', 'completed_at'];

    protected $casts = ['completed_at' => 'datetime'];
}
