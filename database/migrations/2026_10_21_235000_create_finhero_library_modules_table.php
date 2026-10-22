<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per FinHero Library module (Introduction to Budget, Emergency
 * Fund, ...). Its lesson and questions are written once here and shared by
 * every school/grade/class the module is assigned to through
 * finhero_activity_registry (matched on module_key = activity_key).
 *
 * `id` is kept equal to the original hardcoded module numbers (1-7) on
 * purpose: finhero_monthly_settings.active_library_module_id and the
 * 'library' rows in finhero_student_points (source_key) already use them.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('finhero_library_modules', function (Blueprint $table) {
            $table->id();
            $table->string('module_key')->unique();
            $table->string('title');
            $table->string('icon', 16)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->json('lesson')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finhero_library_modules');
    }
};
