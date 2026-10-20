<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which of the 20 catalog Activities a class's Spending Activity position
 * actually is - completes the row started yesterday (activity_key,
 * position, start_date/end_date). Nullable, no cascade: if an Activity is
 * ever removed from the catalog, an assigned class just reverts to
 * unassigned rather than pointing at a dead row (same philosophy as
 * curriculum_items.pisa_proficiency_level_id).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('mba_position', 'activity_id')) {
            Schema::table('mba_position', function (Blueprint $table) {
                $table->foreignId('activity_id')->nullable()->after('cid')->constrained('activities')->nullOnDelete();
            });
        }
    }

    public function down()
    {
        Schema::table('mba_position', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropColumn('activity_id');
        });
    }
};
