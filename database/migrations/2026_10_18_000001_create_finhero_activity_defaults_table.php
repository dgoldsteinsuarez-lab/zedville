<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel's requirement (2026-10-18): a single global default point value per
 * FinHero activity, editable in Admin, applying to every school — not the
 * per-school-row-only setup finhero_activity_registry had before. A registry
 * row's own `max_points`/`salary` stays nullable and now means "no override,
 * use the global default"; admin can still set an explicit value per row to
 * override just that one instance.
 *
 * Seeded from whatever distinct activity_keys already exist live, using
 * their current max_points/salary as the starting default - this migration
 * does not change any existing registry row's effective value, it only adds
 * the new default table underneath it.
 */
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('finhero_activity_defaults')) {
            Schema::create('finhero_activity_defaults', function (Blueprint $table) {
                $table->id();
                $table->string('activity_key')->unique();
                $table->string('activity_name')->nullable();
                $table->string('type', 50)->nullable(); // task | library
                $table->unsignedTinyInteger('max_points')->nullable();
                $table->string('salary')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('finhero_activity_registry')) {
            $existing = DB::table('finhero_activity_registry')
                ->select('activity_key', 'activity_name', 'type', 'max_points', 'salary')
                ->whereNotNull('activity_key')
                ->orderBy('id')
                ->get()
                ->unique('activity_key');

            foreach ($existing as $row) {
                DB::table('finhero_activity_defaults')->updateOrInsert(
                    ['activity_key' => $row->activity_key],
                    [
                        'activity_name' => $row->activity_name,
                        'type' => $row->type,
                        'max_points' => $row->max_points,
                        'salary' => $row->salary,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('finhero_activity_defaults');
    }
};
