<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One of the 27 fixed street names in the "City and Map" doc (2026-10-19),
 * each with a short bio shown when a student clicks its plaque on the city
 * map. Global reference data, same category as GradeLevel - not
 * per-school/per-grade content.
 */
class Street extends Model
{
    protected $fillable = ['name', 'tier', 'bio', 'sort_order'];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
