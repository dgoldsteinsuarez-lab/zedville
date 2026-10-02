<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** No tracked migration existed — discovered by actually running the app. */
    public function up()
    {
        if (!Schema::hasTable('months')) {
            Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('months');
    }
};
