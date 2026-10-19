<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row settings table holding the one shared city map hero image.
 */
class CityMapSetting extends Model
{
    protected $table = 'city_map_settings';

    protected $fillable = ['image'];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
