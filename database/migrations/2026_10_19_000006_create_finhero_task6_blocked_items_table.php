<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Substitute Goods feature, FinHero Task 6 (2026-10-19). An admin/tutor
 * marks which Supermarket catalog items are temporarily "unavailable"
 * for a Class while Task 6 is active for it - a student with one of
 * these in their basket must swap it for a substitute to complete the
 * task. Diet is implicit via `supermarkets.type` on the blocked row
 * itself, no separate field needed.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('finhero_task6_blocked_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sid');
            $table->unsignedBigInteger('cid');
            $table->foreignId('supermarket_id')->constrained('supermarkets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sid', 'cid', 'supermarket_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('finhero_task6_blocked_items');
    }
};
