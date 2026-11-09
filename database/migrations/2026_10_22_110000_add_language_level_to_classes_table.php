<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-22: every language gets a beginner version (A2/B1) for
 * students learning that language; the class's teacher decides which one
 * the class sees - students can't change it.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->string('language_level', 10)->default('standard')->after('default_language');
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('language_level');
        });
    }
};
