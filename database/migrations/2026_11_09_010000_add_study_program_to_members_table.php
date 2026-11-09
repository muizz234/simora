<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Program studi mahasiswa (misal "Teknologi Informasi")
     * sesuai data SSO UNUJA, untuk memfilter anggota per prodi.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('study_program')->nullable()->after('faculty');
            $table->index('study_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['study_program']);
            $table->dropColumn('study_program');
        });
    }
};
