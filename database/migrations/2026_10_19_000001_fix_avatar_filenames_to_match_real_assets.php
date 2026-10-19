<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-19: avatar images were 404ing for every student (seen via
 * a browser console "GET .../avatar6.png 404" while on the Bank Account
 * wizard, but this is global, not wizard-specific - every page that shows
 * a student's avatar hits it). Root cause: the `avatars` table's `name`
 * column says "avatar1.png" .. "avatar9.png", but the actual files
 * committed under public/asset/front/images/ are "avtar1.svg" ..
 * "avtar9.svg" - a typo'd filename AND a wrong extension, for all 9 rows.
 * No code ever hardcodes the "avtar" spelling - every reference reads
 * `$avatar->name` from this table - so this is a data fix, not a code fix.
 *
 * Found along the way: this table was never seeded by any tracked
 * migration at all - the real dev DB's 9 rows were inserted manually,
 * outside version control, at some point. A fresh install (the isolated
 * test DB, or a new Hostinger deploy) would start with zero avatars,
 * breaking the avatar picker entirely. This upserts by id instead of only
 * fixing existing rows, so it both corrects the dev DB's bad names AND
 * seeds a fresh database that has none yet.
 */
return new class extends Migration
{
    public function up()
    {
        for ($i = 1; $i <= 9; $i++) {
            $existing = DB::table('avatars')->where('id', $i)->first();

            if ($existing) {
                DB::table('avatars')->where('id', $i)->update(['name' => "avtar{$i}.svg"]);
            } else {
                DB::table('avatars')->insert([
                    'id' => $i,
                    'name' => "avtar{$i}.svg",
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        for ($i = 1; $i <= 9; $i++) {
            DB::table('avatars')
                ->where('id', $i)
                ->where('name', "avtar{$i}.svg")
                ->update(['name' => "avatar{$i}.png"]);
        }
    }
};
