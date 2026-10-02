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

    ];

    protected $casts = [

        'start_date' => 'date',

        'end_date' => 'date',

    ];

    /**
     * Referendums a given student may see/vote on: same school, and either
     * school-wide (class_id null) or attached to the student's own class.
     */
    public function scopeVisibleToStudent($query, $student)
    {
        return $query->where('sid', $student->sid)
            ->where(function ($q) use ($student) {
                $q->whereNull('class_id')->orWhere('class_id', $student->grade);
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