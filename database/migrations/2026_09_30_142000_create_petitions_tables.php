<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        if (!Schema::hasTable('petitions')) {
            Schema::create('petitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('tutor_feedback')->nullable();
            $table->timestamps();
            });
        }

        if (!Schema::hasTable('petition_signatures')) {
            Schema::create('petition_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petition_id')->constrained('petitions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('petition_signatures');
        Schema::dropIfExists('petitions');
    }
};
