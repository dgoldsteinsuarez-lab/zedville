<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Substitute Goods / FinHero Task 6 (2026-10-19). One Supermarket catalog
 * item an admin/tutor has marked "temporarily unavailable" for a Class.
 */
class FinheroTask6BlockedItem extends Model
{
    protected $table = 'finhero_task6_blocked_items';

    protected $fillable = ['sid', 'cid', 'supermarket_id'];

    public function item()
    {
        return $this->belongsTo(Supermarket::class, 'supermarket_id');
    }
}
