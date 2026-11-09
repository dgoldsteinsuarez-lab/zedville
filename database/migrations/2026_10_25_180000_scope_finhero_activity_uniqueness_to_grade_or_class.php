<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Daniel, 2026-10-25: adding a FinHero activity for Grade 9 crashed with
// "Duplicate entry 'task_5-library-1' for key 'unique_activity_type'".
// An activity is assigned per grade (or per class), so the same activity
// can be given to several grades of one school - which is what
// FinheroAdminController::addActivity() already checks (one per school +
// grade/class + type). The old key allowed it once per school. It becomes
// a plain index; the controller keeps the per-grade/class rule.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finhero_activity_registry', function (Blueprint $table) {
            $table->index(['activity_key', 'type', 'sid'], 'finhero_registry_key_type_sid_index');
            $table->dropUnique('unique_activity_type');
        });
    }

    public function down(): void
    {
        Schema::table('finhero_activity_registry', function (Blueprint $table) {
            $table->unique(['activity_key', 'type', 'sid'], 'unique_activity_type');
            $table->dropIndex('finhero_registry_key_type_sid_index');
        });
    }
};
