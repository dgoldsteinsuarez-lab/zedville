<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('finhero_library_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('question_id')
                ->references('id')->on('finhero_library_questions')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('finhero_library_options');
    }
};
