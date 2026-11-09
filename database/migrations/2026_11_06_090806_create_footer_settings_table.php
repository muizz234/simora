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
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('identity_text')->default('Universitas Nurul Jadid · Bersama Membangun Fakultas Teknik yang Lebih Baik');
            $table->string('tagline')->default('Kolaborasi • Inovasi • Prestasi');
            $table->string('instagram_url', 2048)->default('https://instagram.com');
            $table->string('youtube_url', 2048)->default('https://youtube.com');
            $table->string('website_url', 2048)->default('https://unuja.ac.id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_settings');
    }
};
