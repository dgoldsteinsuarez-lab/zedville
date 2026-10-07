<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Admin Roles & Permissions doc §2.4/§3: students "Propose a city name and
// city quote"; permission matrix lists approval as Super Admin + Tutor.
// Scoped by school (sid), not class - the city is shared by the whole
// school's simulated city, unlike petitions which can be class-scoped.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('city_proposals')) {
            Schema::create('city_proposals', function (Blueprint $table) {
                $table->id();
                $table->string('sid', 50)->nullable()->index();
                $table->string('type', 10); // 'name' | 'quote'
                $table->string('value', 255);
                $table->unsignedBigInteger('proposed_by');
                $table->string('status', 20)->default('pending'); // pending | approved | rejected
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('city_proposals');
    }
};
