<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One exchange at the Money Factory: Zeds from the current account into cash of another currency (App\Services\CurrencyExchangeService). */
class CurrencyExchange extends Model
{
    protected $fillable = ['user_id', 'sid', 'currency', 'zeds', 'commission', 'rate', 'foreign_amount', 'transaction_id'];

    protected $casts = ['zeds' => 'float', 'commission' => 'float', 'rate' => 'float', 'foreign_amount' => 'float'];
}
