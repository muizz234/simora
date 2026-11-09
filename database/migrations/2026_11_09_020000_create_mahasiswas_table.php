<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data mahasiswa SSO UNUJA per prodi (hak akses: developer saja).
     * BUKAN data anggota organisasi (members) — tabel members tetap
     * untuk keanggotaan organisasi yang dikelola manual oleh role organization.
     */
    public function up(): void
    {
        Schema::create('mahasiswas', function (Blueprint $table) {
            $table->id();
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('faculty')->nullable();
            $table->string('study_program')->nullable();
            $table->string('prodi_id')->nullable();
            $table->string('fakultas_id')->nullable();
            $table->string('gender', 5)->nullable();
            $table->string('enrollment_year')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['study_program', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswas');
    }
};
