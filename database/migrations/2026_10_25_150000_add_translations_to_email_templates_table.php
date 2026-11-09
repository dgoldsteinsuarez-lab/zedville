<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Daniel, 2026-10-25: "para los mensajes, debería haber templates para cada
// idioma". A template keeps its main (English) subject and content, plus the
// versions an admin writes in the other languages:
// {"es": {"subject": "...", "content": "..."}, "fr": {...}, "de": {...}}.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }
};
