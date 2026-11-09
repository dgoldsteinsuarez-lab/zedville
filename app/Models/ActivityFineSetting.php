<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * How much a Required activity not done in time costs (0 = no fines), and
 * the day fines started: deadlines before it are never fined.
 */
class ActivityFineSetting extends Model
{
    public const DEFAULT_AMOUNT = 50;

    protected $fillable = ['amount', 'active_since', 'updated_by'];

    protected $casts = [
        'amount' => 'float',
        'active_since' => 'date',
    ];

    public static function current(): self
    {
        return self::query()->orderBy('id')->first()
            ?? new self(['amount' => self::DEFAULT_AMOUNT, 'active_since' => now()->toDateString()]);
    }
}
