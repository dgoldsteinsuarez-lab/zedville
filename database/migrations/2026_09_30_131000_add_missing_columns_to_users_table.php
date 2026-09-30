<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * User::$fillable and the registration flow (RegisteredUserController)
     * already rely on all of these, but none were ever added in a tracked
     * migration — the live/test database apparently has them from an
     * untracked schema change. Adding them here so a database built from
     * these migrations alone actually matches what the running code expects.
     *
     * Column choices match how each is used elsewhere:
     * - citizenId: generated as "ZV-2026-0001" style strings (RegisteredUserController)
     * - avatar / grade / mascot: foreign-key-style columns, used via
     *   User::avatarRelation()/gradeRelation()/mascotRelation() belongsTo()
     * - loginTime: set on every login (AuthenticatedSessionController)
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('sid')->nullable()->after('id');
            $table->string('citizenId')->nullable()->unique()->after('nickname');
            $table->unsignedBigInteger('avatar')->nullable()->after('role');
            $table->unsignedBigInteger('grade')->nullable()->after('avatar');
            $table->unsignedBigInteger('mascot')->nullable()->after('grade');
            $table->unsignedTinyInteger('age')->nullable()->after('mascot');
            $table->string('address')->nullable()->after('age');
            $table->timestamp('loginTime')->nullable()->after('address');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sid', 'citizenId', 'avatar', 'grade', 'mascot', 'age', 'address', 'loginTime']);
        });
    }
};
