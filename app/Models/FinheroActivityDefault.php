<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per activity_key: the global default point value (and, for task
 * activities, default salary) that applies across every school unless a
 * specific finhero_activity_registry row explicitly overrides it.
 */
class FinheroActivityDefault extends Model
{
    protected $table = 'finhero_activity_defaults';

    protected $fillable = [
        'activity_key',
        'activity_name',
        'type',
        'max_points',
        'salary',
    ];
}
