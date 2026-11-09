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

    /**
     * The bio in the student's language (lang/<code>/streets.php). English
     * shows the database text, so edits made in Admin > City Map show
     * straight away; the other languages fall back to it when untranslated.
     */
    public function getLocalizedBioAttribute(): string
    {
        $key = "streets.bios.{$this->id}";

        if (app()->getLocale() !== 'en' && \Illuminate\Support\Facades\Lang::has($key, null, false)) {
            return __($key);
        }

        return $this->bio;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
