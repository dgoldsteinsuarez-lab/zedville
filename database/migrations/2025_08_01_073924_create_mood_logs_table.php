<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Duplicate of 2025_08_01_072816_create_mood_logs_table.php — that one has
     * the real columns (user_id, mood, energy, pleasantness); this one was a
     * bare stub that would have wiped those columns if it ever ran first.
     * Left as a no-op rather than deleted, so any environment where this
     * migration is already recorded as run doesn't break on rollback.
     */
    public function up()
    {
        //
    }

    public function down()
    {
        //
    }
};
