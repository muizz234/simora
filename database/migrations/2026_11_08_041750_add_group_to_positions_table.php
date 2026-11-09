<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kelompok jabatan untuk penentuan tingkat bagan struktural:
     * pimpinan, wakil_pimpinan, bpi (Badan Pengurus Inti), divisi.
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('group_type')->nullable()->after('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('group_type');
        });
    }
};
