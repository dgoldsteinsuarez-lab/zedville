<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referendum extends Model
{
    protected $table = 'referendums';

    protected $fillable = [

        'question',

        'description',

        'status',

        'start_date',

        'end_date',

        'created_by',

        'sid',

        'class_id',

        'grade_id',

    ];

    protected $casts = [

        'start_date' => 'date',

        'end_date' => 'date',

    ];

    /**
     * Referendums a given student may see/vote on: same school, and one of —
     * their own Class, their whole Grade, or school-wide (all null).
     */
    public function scopeVisibleToStudent($query, $student)
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

    public function votes()
    {
        return $this->hasMany(ReferendumVote::class);
    }

    public function yesVotes()
    {
        return $this->hasMany(ReferendumVote::class)
                    ->where('vote','yes');
    }

    public function noVotes()
    {
        return $this->hasMany(ReferendumVote::class)
                    ->where('vote','no');
    }

}