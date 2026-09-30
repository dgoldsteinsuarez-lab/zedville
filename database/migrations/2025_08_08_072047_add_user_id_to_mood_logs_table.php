<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Redundant with the original create_mood_logs_table migration, which
     * already defines user_id as a foreign key column. Left as a no-op
     * rather than deleted so any environment where this is already recorded
     * as run doesn't break on rollback.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
