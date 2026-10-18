<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel's real grocery pricing doc (Salary and Savings Computation.xlsx)
 * uses fractional "typical monthly quantity" values (0.8kg, 1.2kg, 3.5kg,
 * etc.) - supermarkets.quantity was int(11), which would silently round
 * these and throw off the price x quantity totals the survey computes
 * client-side. Widened to match price's own decimal(10,2) precision.
 */
return new class extends Migration {
    public function up()
    {
        // doctrine/dbal isn't installed in this project (see other
        // raw-SQL column-change migrations) - MariaDB CHANGE COLUMN direct.
        DB::statement('ALTER TABLE supermarkets CHANGE quantity quantity DECIMAL(10,2) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE supermarkets CHANGE quantity quantity INT NULL');
    }
};
