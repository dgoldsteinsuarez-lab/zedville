<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A content category within a Grade Level (Activities, Exams, Graphics,
 * PISA Practice, and level-specific extras like Insurance/Credit/Voting).
 * grade_level_id is a plain attribute, not an Eloquent relation - see the
 * migration's docblock for why it isn't a real foreign key.
 */
class CurriculumCategory extends Model
{
    protected $fillable = ['grade_level_id', 'name', 'is_pisa_practice', 'sort_order'];

    protected $casts = [
        'is_pisa_practice' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(CurriculumItem::class, 'category_id');
    }
}
