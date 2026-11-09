<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A line in the history of a student's Emergency Fund or Money Market account
 * (account: emergency | money_market). The account's balance itself stays in
 * bank_accounts; each line carries the balance after it. Written only through
 * App\Services\SavingsLedger.
 */
class SavingsTransaction extends Model
{
    protected $fillable = [
        'user_id', 'bank_account_id', 'account', 'type', 'category',
        'amount', 'balance', 'description', 'meta', 'transaction_date',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'meta' => 'array',
        'amount' => 'float',
        'balance' => 'float',
    ];
}
