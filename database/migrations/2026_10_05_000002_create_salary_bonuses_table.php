<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Admin Roles & Permissions doc §2.3/§4.2: Tutor (or Super Admin) can add a
// one-time or recurring salary bonus with a mandatory reason; pausable
// without deletion. Applied by the `bonuses:apply` scheduled command, which
// writes the actual credit into the real ledger (transactions1) - this
// table only holds the bonus *definition*, never a balance itself.
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('salary_bonuses')) {
            Schema::create('salary_bonuses', function (Blueprint $table) {
                $table->id();
                $table->string('sid', 50)->nullable()->index();
                $table->unsignedBigInteger('student_id')->index();
                $table->unsignedBigInteger('granted_by');
                $table->decimal('amount', 10, 2);
                $table->text('reason');
                $table->string('frequency', 20)->nullable(); // null = one-time, 'weekly' | 'monthly'
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->date('last_applied_at')->nullable();
                $table->string('status', 20)->default('active'); // active | paused | cancelled
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('salary_bonuses');
    }
};
