<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Substitute Goods activity (Engagement badge, 2026-10-19). One item the
 * system auto-picked from a specific student's own basket for them to
 * substitute, for a given month - fully automatic, no admin/tutor
 * involvement.
 */
class SubstituteGoodsPick extends Model
{
    protected $table = 'substitute_goods_picks';

    protected $fillable = ['student_id', 'supermarket_id', 'month', 'year'];
}
