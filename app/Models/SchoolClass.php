<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A single Class (e.g. "8A") — its own Tutor(s) and its own student roster.
 * Table `classes`. Was formerly (confusingly) the `Grade` model, before the
 * real Grade/Class split: a Grade (e.g. "Grade 8") can contain several of
 * these Classes, each with a different Tutor, sharing the Grade's
 * curriculum/Level. See `App\Models\Grade` for the parent entity.
 */
class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'spending4', // low | high: which Spending 4 the class gets (App\Services\WantsPlanService)
        // the month it started the Intermediate (App\Services\IntermediateClassService)
        'intermediate_started_on',
        'sid',
        'name',
        'classCode',
        'section',
        'description',
        'grade_id',
        'status',
        'language_mode',
        'default_language',
        'language_level',
        'created_at',
        'updated_at',
    ];

    protected $casts = ['intermediate_started_on' => 'date'];

    /** Students of this class (they point at it through users.grade). */
    public function students()
    {
        return $this->hasMany(User::class, 'grade')->where('role', 4);
    }

    /** The Grade this Class belongs to — carries the shared curriculum/Level. */
    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function tutors()
    {
        return $this->belongsToMany(User::class, 'class_tutor', 'class_id', 'user_id');
    }
}
