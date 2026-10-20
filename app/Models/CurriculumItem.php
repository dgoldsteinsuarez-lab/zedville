<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One Activity/Exam/Graphic within a CurriculumCategory. type='quiz' items
 * carry a question bank (see CurriculumQuestion); type='content' items
 * (Graphics) are just title/description, no scoring.
 */
class CurriculumItem extends Model
{
    protected $fillable = [
        'category_id', 'title', 'description', 'type',
        'pisa_proficiency_level_id', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(CurriculumCategory::class, 'category_id');
    }

    public function pisaLevel()
    {
        return $this->belongsTo(PisaProficiencyLevel::class, 'pisa_proficiency_level_id');
    }

    public function questions()
    {
        return $this->hasMany(CurriculumQuestion::class, 'item_id')->orderBy('sort_order');
    }

    public function attempts()
    {
        return $this->hasMany(CurriculumAttempt::class, 'item_id');
    }
}
