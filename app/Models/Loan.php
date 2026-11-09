<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A student's personal loan (App\Services\LoanService), with its instalments. */
class Loan extends Model
{
    protected $fillable = ['user_id', 'sid', 'amount', 'months', 'tae', 'instalment', 'interest_total', 'status', 'cancel_until', 'closed_at'];

    protected $casts = ['cancel_until' => 'date', 'closed_at' => 'datetime'];

    public function instalments()
    {
        return $this->hasMany(LoanInstalment::class)->orderBy('number');
    }
}
