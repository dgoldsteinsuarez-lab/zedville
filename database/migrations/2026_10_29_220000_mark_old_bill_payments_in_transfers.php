<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Bill payments made before the bank review (2026-10-29) sit in `transfers`
 * like transfers: the new `kind` column made them 'transfer'. A scheduled one
 * would then be run as a transfer to a student and fail. They are the rows
 * paid to a biller's account number (manage_payees_biller).
 */
return new class extends Migration
{
    public function up(): void
    {
        $billers = DB::table('manage_payees_biller')->pluck('account_number')->filter()->all();

        if ($billers) {
            DB::table('transfers')->whereIn('account_number', $billers)->update(['kind' => 'bill']);
        }
    }

    public function down(): void
    {
        // the kind can't be told apart again; nothing to undo
    }
};
