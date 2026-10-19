<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Substitute Goods / FinHero Task 6 (2026-10-19). One item the system
 * auto-picked from a specific student's own basket for them to
 * substitute, for a given month - no admin/tutor involvement.
 */
class FinheroTask6StudentPick extends Model
{
    protected $table = 'finhero_task6_student_picks';

    protected $fillable = ['student_id', 'supermarket_id', 'month', 'year'];
}
