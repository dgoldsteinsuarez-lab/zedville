<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        Schema::create('consumer_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('sid')->nullable();
            $table->string('diet')->nullable();
            $table->json('groceries')->nullable();
            $table->decimal('basket_cost', 10, 2)->nullable();
            $table->decimal('lunch', 10, 2)->nullable();
            $table->decimal('transport', 10, 2)->nullable();
            $table->decimal('activities', 10, 2)->nullable();
            $table->decimal('restaurants', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->string('survey_month')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('consumer_surveys');
    }
};
