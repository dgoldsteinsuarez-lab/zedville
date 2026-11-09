<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An emergency a student got (App\Services\EmergencyService). */
class Emergency extends Model
{
    protected $fillable = ['user_id', 'sid', 'number', 'month_in_level', 'kind', 'cost', 'amount', 'plan', 'waiting', 'arrived_at', 'due_on',
        'status', 'method', 'loan_id', 'interest', 'paid_at', 'change', 'completed_at'];

    /** As the table starts them, so a new one reads right before it is loaded again. */
    protected $attributes = ['status' => 'pending', 'interest' => 0, 'waiting' => false];

    protected $casts = ['waiting' => 'boolean','arrived_at' => 'datetime', 'due_on' => 'date', 'paid_at' => 'datetime', 'completed_at' => 'datetime'];

    /**
     * How the insurance went, for a health one: 'none' (no plan), 'waiting' (its
     * dentist's waiting period), 'deductible' (it cost less than the deductible:
     * all the student's), 'covered'; null for the others.
     */
    public function cover(): ?string
    {
        if (!(\App\Services\EmergencyService::KINDS[$this->kind]['health'] ?? false)) {
            return null;
        }

        return match (true) {
            !$this->plan => 'none',
            $this->waiting => 'waiting',
            (float) $this->amount >= (float) $this->cost => 'deductible',
            default => 'covered',
        };
    }

    /** What it really cost the student: their part, and the loan's interest if they took one. */
    public function realCost(): float
    {
        return round((float) $this->amount + (float) $this->interest, 2);
    }
}
