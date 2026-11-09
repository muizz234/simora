<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pemegang jabatan menunjuk ke data anggota (members),
     * bukan akun login (users).
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn('member_id');
        });
    }
};
