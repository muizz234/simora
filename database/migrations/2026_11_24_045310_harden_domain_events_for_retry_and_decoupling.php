<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan state retry, backoff, dan dead-letter pada outbox.
     *
     * Sebelum migrasi ini, kegagalan relay hanya tercatat di log dan event
     * langsung dicoba lagi tiap menit tanpa batas.
     */
    public function up(): void
    {
        Schema::table('domain_events', function (Blueprint $table) {
            $table->unsignedInteger('attempts')->default(0)->after('published_at');
            $table->text('last_error')->nullable()->after('attempts');
            $table->timestamp('next_attempt_at')->nullable()->after('last_error');
            $table->timestamp('dead_lettered_at')->nullable()->after('next_attempt_at');

            $table->index('next_attempt_at', 'domain_events_next_attempt_at_index');
            $table->index('dead_lettered_at', 'domain_events_dead_lettered_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_events', function (Blueprint $table) {
            $table->dropIndex('domain_events_next_attempt_at_index');
            $table->dropIndex('domain_events_dead_lettered_at_index');
            $table->dropColumn(['attempts', 'last_error', 'next_attempt_at', 'dead_lettered_at']);
        });
    }
};
