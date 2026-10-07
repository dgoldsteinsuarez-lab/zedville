<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A Grade (e.g. "Grade 8") — one curriculum/Level, shared by every Class
 * underneath it. Table `grades`. A Grade can contain several Classes (e.g.
 * "8A", "8B"), each with its own Tutor(s) and its own student roster — see
 * `App\Models\SchoolClass`. Sometimes a Grade has only one Class, with more
 * than one Tutor assigned to it.
 */
class Grade extends Model
{
    use HasFactory;

    protected $table = 'grades';

    protected $fillable = [
        'sid',
        'name',
        'grade_level_id',
    ];

    public function level()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'grade_id');
    }
}
