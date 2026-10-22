<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinheroLibraryModule extends Model
{
    protected $fillable = ['id', 'module_key', 'title', 'icon', 'sort_order', 'lesson'];

    protected $casts = ['lesson' => 'array'];

    public function questions()
    {
        return $this->hasMany(FinheroLibraryQuestion::class, 'module_id')->orderBy('sort_order');
    }
}
