<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petition extends Model
{
    protected $table = 'petitions';

    protected $fillable = [

        'title',

        'description',

        'created_by',

        'sid',

        'class_id',

        'grade_id',

        'status',

        'tutor_feedback',

    ];

    /**
     * Petitions a student may see: same school, and one of —
     * their own Class (class_id match), their whole Grade (class_id null,
     * grade_id matches their Class's Grade), or school-wide/legacy (both null).
     */
    public function scopeInStudentScope($query, $student)
    {
        $gradeId = optional($student->gradeRelation)->grade_id;

        return $query->where('sid', $student->sid)
            ->where(function ($q) use ($student, $gradeId) {
                $q->where('class_id', $student->grade)
                    ->orWhere(function ($q2) use ($gradeId) {
                        $q2->whereNull('class_id')->where('grade_id', $gradeId);
                    })
                    ->orWhere(function ($q2) {
                        $q2->whereNull('class_id')->whereNull('grade_id');
                    });
            });
    }

    public function signatures()
    {
        return $this->hasMany(PetitionSignature::class);
    }

    /**
     * The student who submitted this petition. The Tutor-facing views use
     * this; the public/student-facing civic-chamber view never does — only
     * the signature count is shown there.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signatureCount()
    {
        return $this->signatures()->count();
    }

}