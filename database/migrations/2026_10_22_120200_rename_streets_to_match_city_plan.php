<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel's street plan (2026-10-22) names four streets differently from the
 * "City and Map" doc the streets were first seeded from; the plan wins.
 * Addresses point at street ids, so students' addresses follow the rename.
 */
return new class extends Migration
{
    private const RENAMES = [
        'Marshall Circle' => 'Marshall Street',
        'Hayek Highway' => 'Hayek Street',
        'Samuelson Square' => 'Samuelson Street',
        'Lynch Avenue' => 'Lynch Drive',
    ];

    public function up()
    {
        foreach (self::RENAMES as $old => $new) {
            DB::table('streets')->where('name', $old)->update(['name' => $new]);
        }
    }

    public function down()
    {
        foreach (self::RENAMES as $old => $new) {
            DB::table('streets')->where('name', $new)->update(['name' => $old]);
        }
    }
};
