<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Existing accounts have no nickname yet (column is new). Students only ever see
// each other by nickname, so a blank one would show an empty name. Per the product
// owner: the nickname can simply be the first name (max 15 chars, same limit as the
// registration form). Only fills NULL/empty values; students can change it in Profile.
return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'nickname')) {
            DB::statement("UPDATE users SET nickname = LEFT(SUBSTRING_INDEX(TRIM(name), ' ', 1), 15) WHERE (nickname IS NULL OR nickname = '') AND name IS NOT NULL AND TRIM(name) <> ''");
        }
    }

    public function down()
    {
        // Not reversible: cannot tell generated nicknames from chosen ones.
    }
};
