<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * email_templates was built expecting admins to manually INSERT rows at 10
 * specific, hardcoded ids (matching a hardcoded sidebar in the old
 * admin/email-template/index.blade.php) - id has no AUTO_INCREMENT, and the
 * table was never actually seeded, so the page always 404'd. Rebuilt as a
 * real, freely-creatable template list: id becomes auto-increment, a real
 * `name` column replaces the sidebar label, and `trigger_event` lets a
 * template opt into driving the existing MailboxScheduler event pipeline
 * (see app/Helpers/MailboxScheduler.php) instead of just being read for a
 * one-off/scheduled campaign. store_name/subTitle were never more than that
 * hardcoded label + a hover blurb - dropped, not load-bearing.
 *
 * No doctrine/dbal installed in this project, so raw SQL rather than the
 * fluent Schema::table()->renameColumn()/dropColumn() builder.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        DB::statement('ALTER TABLE `email_templates` MODIFY `id` INT UNSIGNED NOT NULL AUTO_INCREMENT');

        if (!Schema::hasColumn('email_templates', 'name')) {
            DB::statement("ALTER TABLE `email_templates` ADD COLUMN `name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sid`");
        }

        if (!Schema::hasColumn('email_templates', 'trigger_event')) {
            DB::statement('ALTER TABLE `email_templates` ADD COLUMN `trigger_event` VARCHAR(100) NULL AFTER `name`');
        }

        if (Schema::hasColumn('email_templates', 'store_name')) {
            DB::statement('UPDATE `email_templates` SET `name` = `store_name` WHERE `name` = \'\'');
            DB::statement('ALTER TABLE `email_templates` DROP COLUMN `store_name`');
        }

        if (Schema::hasColumn('email_templates', 'subTitle')) {
            DB::statement('ALTER TABLE `email_templates` DROP COLUMN `subTitle`');
        }
    }

    public function down()
    {
        if (!Schema::hasTable('email_templates')) {
            return;
        }

        if (!Schema::hasColumn('email_templates', 'store_name')) {
            DB::statement("ALTER TABLE `email_templates` ADD COLUMN `store_name` VARCHAR(255) NOT NULL DEFAULT ''");
        }

        if (!Schema::hasColumn('email_templates', 'subTitle')) {
            DB::statement('ALTER TABLE `email_templates` ADD COLUMN `subTitle` VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('email_templates', 'trigger_event')) {
            DB::statement('ALTER TABLE `email_templates` DROP COLUMN `trigger_event`');
        }

        if (Schema::hasColumn('email_templates', 'name')) {
            DB::statement('ALTER TABLE `email_templates` DROP COLUMN `name`');
        }
    }
};
