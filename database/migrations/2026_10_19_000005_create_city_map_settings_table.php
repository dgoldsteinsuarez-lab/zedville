<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * City Map, Phase 1 (2026-10-19). A single shared row holding the one
 * city map hero image (global, not per-Grade - Daniel only has one 3D
 * model to export from).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('city_map_settings', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('city_map_settings');
    }
};
