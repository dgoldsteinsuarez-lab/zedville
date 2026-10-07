<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Real Grade/Class split: a Grade ("Grade 8") carries one curriculum Level
// and can contain several Classes ("8A", "8B"), each with its own Tutor(s)
// and roster. This table is the new parent entity; `classes` (model
// SchoolClass) becomes the child. See the backfill migration that follows.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table) {
                $table->id();
                $table->string('sid', 50)->nullable()->index();
                $table->string('name');
                $table->unsignedBigInteger('grade_level_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('grades');
    }
};
