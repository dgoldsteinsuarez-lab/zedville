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

        'status',

        'tutor_feedback',

    ];

    /**
     * Petitions a student's own school/class may see: same school, and either
     * school-wide/legacy (class_id null) or the student's own class.
     */
    public function scopeInStudentScope($query, $student)
    {
        return $query->where('sid', $student->sid)
            ->where(function ($q) use ($student) {
                $q->whereNull('class_id')->orWhere('class_id', $student->grade);
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