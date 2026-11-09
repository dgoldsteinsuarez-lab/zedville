<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A level's emergencies - on or off, and the months of each scenario
 * (App\Services\EmergencyService::settings) - and its frauds: on or off, the
 * month of the call and of the fake email (App\Services\FraudService).
 */
class EmergencySetting extends Model
{
    protected $fillable = ['grade_level_id', 'enabled', 'months_a', 'months_b', 'frauds_enabled', 'call_month', 'email_month'];

    protected $casts = ['enabled' => 'boolean', 'frauds_enabled' => 'boolean'];
}
