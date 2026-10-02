<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No tracked migration existed. Built with sid/class_id scoping from the
     * start (petitions had to be retrofitted): class_id null = school-wide.
     */
    public function up()
    {
        Schema::create('referendums', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('sid')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->timestamps();
        });

        Schema::create('referendum_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referendum_id')->constrained('referendums')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('vote');
            $table->timestamp('created_at')->nullable();

            $table->unique(['referendum_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('referendum_votes');
        Schema::dropIfExists('referendums');
    }
};
