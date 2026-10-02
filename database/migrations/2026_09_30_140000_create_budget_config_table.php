<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        if (!Schema::hasTable('budget_config')) {
            Schema::create('budget_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sid')->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
            $table->decimal('needs_percentage', 5, 2)->nullable();
            $table->decimal('wants_percentage', 5, 2)->nullable();
            $table->decimal('savings_percentage', 5, 2)->nullable();
            $table->date('effective_date')->nullable();
            $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('budget_config');
    }
};
