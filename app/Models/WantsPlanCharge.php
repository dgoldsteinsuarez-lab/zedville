<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One payment of a chosen plan, on its day: pending, charged, or waiting (not
 * enough money on its day - paid as soon as there is, App\Services\WantsPlanService).
 */
class WantsPlanCharge extends Model
{
    protected $fillable = ['choice_id', 'wants_plan_id', 'user_id', 'due_date', 'amount', 'status', 'transaction_id'];

    protected $casts = ['due_date' => 'date', 'amount' => 'float'];

    public function plan()
    {
        return $this->belongsTo(WantsPlan::class, 'wants_plan_id');
    }

    public function choice()
    {
        return $this->belongsTo(WantsPlanChoice::class, 'choice_id');
    }
}
