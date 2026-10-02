<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Role::$fillable already lists 'sid', but no migration ever added it —
     * same untracked-schema-change pattern as classes.sid/classCode.
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'sid')) { $table->unsignedBigInteger('sid')->nullable()->after('id'); }
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('sid');
        });
    }
};
