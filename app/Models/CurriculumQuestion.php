<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumQuestion extends Model
{
    protected $fillable = ['item_id', 'question_text', 'sort_order'];

    public function item()
    {
        return $this->belongsTo(CurriculumItem::class, 'item_id');
    }

    public function options()
    {
        return $this->hasMany(CurriculumQuestionOption::class, 'question_id')->orderBy('sort_order');
    }
}
