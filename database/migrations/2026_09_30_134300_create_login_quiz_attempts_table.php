<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        Schema::create('login_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('login_questions')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('login_options')->cascadeOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_quiz_attempts');
    }
};
