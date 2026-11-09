<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** What a student chose for a month ("2026-11"): kind plan (wants_plan_id), bundle (three low-budget plans, plan_ids) or save; and the four they were offered. */
class WantsPlanChoice extends Model
{
    protected $fillable = ['user_id', 'wants_plan_id', 'kind', 'plan_ids', 'month', 'offered', 'total'];

    protected $casts = ['offered' => 'array', 'plan_ids' => 'array', 'total' => 'float'];

    public function plan()
    {
        return $this->belongsTo(WantsPlan::class, 'wants_plan_id');
    }

    public function charges()
    {
        return $this->hasMany(WantsPlanCharge::class, 'choice_id')->orderBy('due_date');
    }
}
