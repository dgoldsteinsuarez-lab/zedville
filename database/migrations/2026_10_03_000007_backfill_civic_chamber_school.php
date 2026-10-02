<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Petitions/referendums created before school/class scoping have sid = NULL, which
// the student-side scoping would hide from everyone. Give each the school of its
// creator (class stays NULL = whole school). Idempotent: only touches NULL rows.
return new class extends Migration {
    public function up()
    {
        foreach (['petitions', 'referendums'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'sid') && Schema::hasColumn($table, 'created_by')) {
                DB::statement("UPDATE {$table} t JOIN users u ON u.id = t.created_by SET t.sid = u.sid WHERE t.sid IS NULL AND u.sid IS NOT NULL");
            }
        }
    }

    public function down()
    {
        // Not reversible on purpose: the original NULLs carried no information.
    }
};
