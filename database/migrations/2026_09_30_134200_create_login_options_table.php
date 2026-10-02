<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        if (!Schema::hasTable('login_options')) {
            Schema::create('login_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('login_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('login_options');
    }
};
