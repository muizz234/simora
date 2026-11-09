<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('instagram_url', 2048)->nullable()->after('registration_url');
            $table->string('tiktok_url', 2048)->nullable()->after('instagram_url');
            $table->string('youtube_url', 2048)->nullable()->after('tiktok_url');
            $table->string('whatsapp_url', 2048)->nullable()->after('youtube_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'tiktok_url', 'youtube_url', 'whatsapp_url']);
        });
    }
};
