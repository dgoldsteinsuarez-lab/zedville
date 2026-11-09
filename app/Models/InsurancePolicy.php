<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A student's health insurance plan (App\Services\InsuranceService); the one without ended_on is the current one. */
class InsurancePolicy extends Model
{
    protected $fillable = ['user_id', 'sid', 'plan', 'auto', 'started_on', 'ended_on'];

    protected $casts = ['auto' => 'boolean', 'started_on' => 'date', 'ended_on' => 'date'];
}
