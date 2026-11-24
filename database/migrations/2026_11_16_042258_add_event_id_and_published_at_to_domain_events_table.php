<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan identitas global dan status publikasi pada outbox.
     * event_id dipakai sebagai kunci idempotensi saat event dikirim ke broker,
     * sedangkan published_at menandai event yang sudah di-relay.
     */
    public function up(): void
    {
        Schema::table('domain_events', function (Blueprint $table) {
            $table->uuid('event_id')->nullable()->unique()->after('id');
            $table->timestamp('published_at')->nullable()->after('occurred_at');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_events', function (Blueprint $table) {
            $table->dropUnique(['event_id']);
            $table->dropIndex(['published_at']);
            $table->dropColumn(['event_id', 'published_at']);
        });
    }
};
