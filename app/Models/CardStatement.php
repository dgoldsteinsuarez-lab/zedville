<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A credit card's monthly statement: what was owed when the month closed and what is paid on the 7th (App\Services\CreditCardService). */
class CardStatement extends Model
{
    protected $fillable = ['card_id', 'user_id', 'month', 'balance', 'due', 'paid', 'interest', 'status', 'paid_at'];

    protected $attributes = ['paid' => 0, 'interest' => 0, 'status' => 'pending'];

    protected $casts = ['paid_at' => 'datetime'];
}
