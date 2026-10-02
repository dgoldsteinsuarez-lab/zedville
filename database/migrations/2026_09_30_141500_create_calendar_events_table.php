<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        if (!Schema::hasTable('calendar_events')) {
            Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sid')->nullable();
            $table->string('title')->nullable();
            $table->string('activitys')->nullable();
            $table->string('activityType')->nullable();
            $table->unsignedBigInteger('classId')->nullable();
            $table->string('position')->nullable();
            $table->string('repeatActivity')->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->text('description')->nullable();
            $table->string('backgroundColor')->nullable();
            $table->string('borderColor')->nullable();
            $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('calendar_events');
    }
};
