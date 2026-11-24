<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Putus constraint DB yang melintasi batas calon service.
     *
     * Kolomnya tetap dipertahankan sebagai ID referensi. Validasi keberadaan
     * data dilakukan lewat kontrak/directory, bukan lagi oleh foreign key,
     * supaya SENTRA dan outbox bisa dipindah ke database lain.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropForeign(['work_program_id']);
        });

        Schema::table('domain_events', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_events', function (Blueprint $table) {
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->nullOnDelete();
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->foreign('work_program_id')
                ->references('id')
                ->on('work_programs')
                ->nullOnDelete();
        });
    }
};
