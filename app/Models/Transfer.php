<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sid',
        'from_account',
        'account_number',
        'sort_code',
        'beneficiary_name',
        'amount',
        'memo',
        'type',
        'scheduled_at',
        'start_date',
        'end_date',
        'frequency',
        // 2026-10-29 bank review: a scheduled or recurring one runs when it's
        // due, once per date (App\Services\BankTransferService::runDue)
        'kind',        // transfer | bill
        'status',      // scheduled | done | failed | cancelled
        'next_run_at',
        'last_run_at',
        'runs',
        'failure',     // the flash.* key of why the last run failed
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_run_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Who or where it goes, for the student: a bill's payee, a charity, one of their own accounts, or the friend's name. */
    public function targetLabel(?BankAccount $own): string
    {
        if ($this->kind === \App\Services\DonationService::KIND) {
            return (string) ct('activities', $this->beneficiary_name);
        }
        if ($this->kind !== 'bill' && $own) {
            foreach (['current' => 'primary_savings_account_number', 'emergency' => 'emergency_fund_account_number', 'money_market' => 'money_market_account_number'] as $key => $column) {
                if ((string) $own->{$column} === (string) $this->account_number) {
                    return __('bank_ui.' . $key);
                }
            }
        }

        return (string) txn_text($this->beneficiary_name);
    }
}
