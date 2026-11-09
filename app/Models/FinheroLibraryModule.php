<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinheroLibraryModule extends Model
{
    protected $fillable = ['id', 'module_key', 'title', 'icon', 'sort_order', 'lesson', 'levels'];

    // levels: the grade levels it is for, null for every level (App\Support\AdminLevel, 2026-11-02)
    protected $casts = ['lesson' => 'array', 'levels' => 'array'];

    public function questions()
    {
        return $this->hasMany(FinheroLibraryQuestion::class, 'module_id')->orderBy('sort_order');
    }
}
