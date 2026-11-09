<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Emergency Fund Library module's icon was the lifebuoy emoji (Unicode
 * 14, 2021), which Windows 10 can't draw - students saw an empty box. The
 * umbrella ("saving for a rainy day") shows everywhere. Only replaces the
 * old icon, so an icon an admin has chosen since is left alone.
 *
 * The comparison is BINARY on purpose: under utf8mb4_unicode_ci every emoji
 * outside the basic plane compares equal, so a plain `icon = ?` matches
 * every module's emoji, not just this one.
 */
return new class extends Migration
{
    public function up()
    {
        $this->swap("\u{1F6DF}", "\u{2602}\u{FE0F}");
    }

    public function down()
    {
        $this->swap("\u{2602}\u{FE0F}", "\u{1F6DF}");
    }

    private function swap(string $from, string $to): void
    {
        DB::table('finhero_library_modules')
            ->whereRaw('BINARY icon = ?', [$from])
            ->update(['icon' => $to]);
    }
};
