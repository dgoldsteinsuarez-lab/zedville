<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A student's credit card (App\Services\CreditCardService). */
class CreditCard extends Model
{
    protected $fillable = ['user_id', 'sid', 'number', 'limit', 'mode', 'fixed_amount', 'status'];

    protected $attributes = ['mode' => 'full', 'status' => 'active'];

    public function movements()
    {
        return $this->hasMany(CardMovement::class, 'card_id');
    }

    public function statements()
    {
        return $this->hasMany(CardStatement::class, 'card_id');
    }
}
