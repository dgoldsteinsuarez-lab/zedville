<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A credit card movement: a purchase, a payment or interest; balance is what is owed after it (App\Services\CreditCardService). */
class CardMovement extends Model
{
    protected $fillable = ['card_id', 'user_id', 'type', 'amount', 'balance', 'description', 'order_id', 'moved_at'];

    protected $casts = ['moved_at' => 'datetime'];
}
