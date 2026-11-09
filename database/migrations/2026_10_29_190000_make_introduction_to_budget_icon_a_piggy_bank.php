<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Introduction to Budget's icon was the pig's face emoji; Daniel, 2026-10-29:
 * "change the icon of a head of the pig to a piggy bank instead". There is no
 * piggy bank emoji, so the icon becomes the name of the drawn one in
 * App\Support\ModuleIcon. Only replaces the old icon, so an icon an admin has
 * chosen since is left alone.
 *
 * The comparison is BINARY on purpose: under utf8mb4_unicode_ci every emoji
 * outside the basic plane compares equal (see replace_emergency_fund_library_icon).
 */
return new class extends Migration
{
    public function up()
    {
        DB::table('finhero_library_modules')
            ->where('module_key', 'task_1')
            ->whereRaw('BINARY icon = ?', ["\u{1F437}"])
            ->update(['icon' => 'piggy-bank']);
    }

    public function down()
    {
        DB::table('finhero_library_modules')
            ->where('module_key', 'task_1')
            ->where('icon', 'piggy-bank')
            ->update(['icon' => "\u{1F437}"]);
    }
};
