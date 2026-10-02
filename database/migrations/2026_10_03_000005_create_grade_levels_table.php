<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// School -> Grade level -> Class. A grade level ("Grade 8") groups several
// classes ("8A", "8B"). `classes.grade_level_id` is optional, so every existing
// class keeps working untouched until a School Admin files it under a level.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('grade_levels')) {
            Schema::create('grade_levels', function (Blueprint $table) {
                $table->id();
                $table->string('sid', 50)->nullable()->index();   // same type as classes.sid on live
                $table->string('name');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['sid', 'name']);
            });
        }

        if (Schema::hasTable('classes') && !Schema::hasColumn('classes', 'grade_level_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->unsignedBigInteger('grade_level_id')->nullable()->index();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('classes', 'grade_level_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropColumn('grade_level_id');
            });
        }
        Schema::dropIfExists('grade_levels');
    }
};
