<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Schema\Builder;
use Illuminate\Support\Facades\Schema;

// Daniel, 2026-10-25: "todo tiene que estar en sus respectivos idiomas". A
// city message now remembers which template it came from, and is shown in
// the reader's language (Mailbox::getSubjectAttribute/getContentAttribute)
// instead of the English text frozen when it was sent.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mailbox', function (Blueprint $table) {
            $table->string('template', 64)->nullable()->after('type');
            $table->json('template_data')->nullable()->after('template');
        });

        // The messages already sent by the city's own templates.
        DB::table('mailbox')->where('subject', 'like', 'Welcome to Zedville%')->update(['template' => 'welcome']);
        DB::table('mailbox')->where('subject', 'Simplify Bills with Auto-Debit')->update(['template' => 'auto-debit']);
        foreach (DB::table('mailbox')->where('subject', 'like', 'Your % Bank Statement')->get(['id', 'subject']) as $mail) {
            $month = \Carbon\Carbon::createFromFormat('F Y', trim(substr($mail->subject, 5, -15)));
            DB::table('mailbox')->where('id', $mail->id)->update([
                'template' => 'monthly-statement',
                'template_data' => json_encode(['month' => $month->format('Y-m')]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('mailbox', function (Blueprint $table) {
            $table->dropColumn(['template', 'template_data']);
        });
    }
};
