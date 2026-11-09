<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A plan-of-the-month payment there was no money for isn't skipped any more:
 * it waits and is paid as soon as there is, right after the salary at the
 * latest (Daniel, 2026-11-02: "si el salario no ha llegado aún, se descuenta
 * justo cuando llegue el salario"). The ones skipped so far wait too.
 */
return new class extends Migration {
    public function up()
    {
        DB::table('wants_plan_charges')->where('status', 'skipped')->update(['status' => 'waiting', 'updated_at' => now()]);
    }

    public function down()
    {
        DB::table('wants_plan_charges')->where('status', 'waiting')->update(['status' => 'skipped', 'updated_at' => now()]);
    }
};
