<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data anggota (mahasiswa) tiap organisasi.
     * Anggota adalah data keanggotaan, BUKAN akun login —
     * akun login tetap di tabel users. user_id opsional untuk
     * anggota yang juga memiliki akun login.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('nim')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('faculty')->nullable();
            $table->string('status')->default('aktif');
            $table->string('avatar_path')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'nim']);
            $table->index(['organization_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
