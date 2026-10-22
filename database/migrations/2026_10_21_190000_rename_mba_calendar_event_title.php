<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-21: "Call It... Needs, Wants and Savings Match" - the
 * title rename in Admin\MbaPositionController (title 'MBA' -> 'Needs,
 * Wants & Savings Match') only takes effect for events it writes going
 * forward. Without this, the one real live row keeps its old title
 * forever (MbaPositionController's own lookups now search for the new
 * title, so the next save would silently create a second, orphaned event
 * instead of updating this one in place).
 */
return new class extends Migration
{
    public function up()
    {
        DB::table('calendar_events')->where('title', 'MBA')->update(['title' => 'Needs, Wants & Savings Match']);
    }

    public function down()
    {
        DB::table('calendar_events')->where('title', 'Needs, Wants & Savings Match')->update(['title' => 'MBA']);
    }
};
