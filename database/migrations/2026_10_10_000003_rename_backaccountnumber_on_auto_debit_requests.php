<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * bugs.md #17: auto_debit_requests.backaccountnumber was a misspelling
 * of "bank account number". Renaming rather than leaving it, now that
 * every PHP/blade call site has been found and updated in the same
 * commit (bugs.md's own instruction: DB column renames need a migration
 * + full call-site audit, never a casual rename).
 *
 * Uses a raw CHANGE COLUMN rather than Schema::renameColumn() - this
 * app's MariaDB (10.4) predates MariaDB's native RENAME COLUMN support
 * (10.5.2+) and doctrine/dbal (Laravel's usual fallback for renames)
 * isn't installed.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('auto_debit_requests', 'backaccountnumber') && !Schema::hasColumn('auto_debit_requests', 'bankaccountnumber')) {
            DB::statement('ALTER TABLE auto_debit_requests CHANGE backaccountnumber bankaccountnumber VARCHAR(100) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('auto_debit_requests', 'bankaccountnumber') && !Schema::hasColumn('auto_debit_requests', 'backaccountnumber')) {
            DB::statement('ALTER TABLE auto_debit_requests CHANGE bankaccountnumber backaccountnumber VARCHAR(100) NULL');
        }
    }
};
