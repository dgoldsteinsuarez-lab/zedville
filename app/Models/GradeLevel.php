<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A curriculum stage ("Rookie", "Intermediate", "Advance", "Advance
 * Economist") assigned to a Grade.
 */
class GradeLevel extends Model
{
    protected $fillable = ['sid', 'name', 'sort_order'];

    public function grades()
    {
        return $this->hasMany(Grade::class, 'grade_level_id');
    }
}
