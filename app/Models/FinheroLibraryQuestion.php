<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinheroLibraryQuestion extends Model
{
    protected $fillable = ['module_id', 'question_text', 'image', 'sort_order'];

    public function module()
    {
        return $this->belongsTo(FinheroLibraryModule::class, 'module_id');
    }

    public function options()
    {
        return $this->hasMany(FinheroLibraryOption::class, 'question_id')->orderBy('sort_order');
    }
}
