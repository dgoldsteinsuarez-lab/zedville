<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoDebitRequest extends Model
{
    use HasFactory;

    protected $table = 'auto_debit_requests';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'sid',
        'type',
        'fullname',
        'email',
        'accountno',
        'serviceaddress',
        'bankname',
        'bankaccountnumber',
        'billschedule',
        'fixedpayment',
        'amount',
        'startDate',
        'endDate',
        'tandagree',
        'signature',
        'date',
    ];

    protected $casts = [
        'billschedule' => 'boolean',
        'fixedpayment' => 'boolean',
        'tandagree'    => 'boolean',
        'startDate'    => 'date',
        'endDate'      => 'date',
        'date'         => 'date',
    ];
}
