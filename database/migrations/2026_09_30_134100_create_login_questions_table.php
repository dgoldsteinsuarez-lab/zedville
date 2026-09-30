<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        Schema::create('login_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sid')->nullable();
            $table->string('month_name')->nullable();
            $table->unsignedBigInteger('month_id')->nullable();
            $table->text('question');
            $table->string('type')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_questions');
    }
};
