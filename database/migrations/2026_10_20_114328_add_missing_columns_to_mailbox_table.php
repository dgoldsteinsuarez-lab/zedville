<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `sid` and `adminemail` exist on the live `mailbox` table but were never
 * captured in a tracked migration (same "discovered by actually running
 * the app" gap as several other tables this session) - both
 * AdminEmailTemplateController's old send() and the new EmailCampaign::
 * sendToStudents()/SendMailboxEmail already write them. Found while
 * testing the new Email Template / campaign system rebuild against
 * laravel_testing, which only had the tracked-migration columns
 * (bugs.md, 2026-10-20).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('mailbox', function (Blueprint $table) {
            if (!Schema::hasColumn('mailbox', 'sid')) {
                $table->string('sid', 50)->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('mailbox', 'adminemail')) {
                $table->boolean('adminemail')->default(0)->after('read');
            }
        });
    }

    public function down()
    {
        Schema::table('mailbox', function (Blueprint $table) {
            if (Schema::hasColumn('mailbox', 'adminemail')) {
                $table->dropColumn('adminemail');
            }
            if (Schema::hasColumn('mailbox', 'sid')) {
                $table->dropColumn('sid');
            }
        });
    }
};
