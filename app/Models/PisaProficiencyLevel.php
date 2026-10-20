<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The OECD's real PISA proficiency/difficulty levels (e.g. Financial
 * Literacy's Below Level 1 through Level 5) - a global reference catalog,
 * independent of Zedville's own Grade Levels (Rookie/Intermediate/Advance/
 * Advance Economist). See the 2026_10_20_...create_pisa_proficiency_levels
 * _table migration for the full context and the official seed data.
 */
class PisaProficiencyLevel extends Model
{
    protected $table = 'pisa_proficiency_levels';

    protected $fillable = [
        'domain',
        'name',
        'sort_order',
        'min_score',
        'max_score',
        'description',
    ];
}
