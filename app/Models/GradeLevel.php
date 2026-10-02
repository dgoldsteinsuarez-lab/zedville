<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A grade level ("Grade 8") that groups classes. Note the confusing legacy
 * naming: the `Grade` model is a single CLASS (table `classes`).
 */
class GradeLevel extends Model
{
    protected $fillable = ['sid', 'name', 'sort_order'];

    public function classes()
    {
        return $this->hasMany(Grade::class, 'grade_level_id');
    }
}
