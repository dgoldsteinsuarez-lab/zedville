<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A Spending Activity's bank line says which activity it was for (Daniel,
 * 2026-11-02: "si es para un Spending Activity, puedes poner Compra Fiesta de
 * Cumpleaños, para que lo ponga en deseos"): "Spending Activity (Order #13)"
 * becomes "Spending Activity: Birthday Party Budget (Order #13)", which the
 * student reads as "Compra: fiesta de cumpleaños" (App\Support\OrderLine).
 * Only the text changes - not the amounts or the balances.
 */
return new class extends Migration {
    public function up()
    {
        DB::table('transactions1')->where('description', 'like', 'Spending Activity (Order #%')->orderBy('id')
            ->each(function ($row) {
                if (!preg_match('/^Spending Activity \(Order #(\d+)\)$/', $row->description, $m)) {
                    return;
                }
                $title = DB::table('orders')->join('activities', 'activities.id', '=', 'orders.activity_id')
                    ->where('orders.id', $m[1])->value('activities.title');
                if ($title) {
                    DB::table('transactions1')->where('id', $row->id)->update(['description' => "Spending Activity: {$title} (Order #{$m[1]})"]);
                }
            });
    }

    public function down()
    {
        DB::table('transactions1')->where('description', 'like', 'Spending Activity: %(Order #%')->orderBy('id')
            ->each(function ($row) {
                if (preg_match('/^Spending Activity: .+ \(Order #(\d+)\)$/', $row->description, $m)) {
                    DB::table('transactions1')->where('id', $row->id)->update(['description' => "Spending Activity (Order #{$m[1]})"]);
                }
            });
    }
};
