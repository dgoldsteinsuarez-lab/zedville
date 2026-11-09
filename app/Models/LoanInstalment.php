<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One instalment of a loan: its due day, what it pays of interest and of capital, and what is left after it. */
class LoanInstalment extends Model
{
    protected $fillable = ['loan_id', 'number', 'due_on', 'amount', 'interest', 'capital', 'remaining', 'status', 'paid_at'];

    protected $casts = ['due_on' => 'date', 'paid_at' => 'datetime'];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
