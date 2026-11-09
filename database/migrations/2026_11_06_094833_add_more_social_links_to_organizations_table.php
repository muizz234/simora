<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'facebook_url')) {
                $table->text('facebook_url')->nullable()->after('whatsapp_url');
            }
            if (!Schema::hasColumn('organizations', 'x_url')) {
                $table->text('x_url')->nullable()->after('facebook_url');
            }
            if (!Schema::hasColumn('organizations', 'telegram_url')) {
                $table->text('telegram_url')->nullable()->after('x_url');
            }
            if (!Schema::hasColumn('organizations', 'email')) {
                $table->string('email')->nullable()->after('telegram_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['facebook_url', 'x_url', 'telegram_url', 'email']);
        });
    }
};
