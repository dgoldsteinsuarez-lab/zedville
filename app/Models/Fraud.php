<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A fraud a student got: a call or a fake email (App\Services\FraudService). */
class Fraud extends Model
{
    protected $fillable = ['user_id', 'sid', 'channel', 'script', 'month_in_level', 'token', 'arrived_at', 'status', 'answer', 'emergency_id', 'answered_at'];

    protected $attributes = ['status' => 'open'];

    protected $casts = ['arrived_at' => 'datetime', 'answered_at' => 'datetime'];
}
