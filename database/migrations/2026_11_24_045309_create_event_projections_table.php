<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Read model di sisi konsumen event.
     *
     * Tabel ini bukan milik modul sumber: ia menyimpan salinan ringkas dari
     * payload event agar service konsumen tetap punya data baca saat modul
     * sumber sudah berjalan sebagai proses terpisah.
     */
    public function up(): void
    {
        Schema::create('event_projections', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('label')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('last_event_at');
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id'], 'event_projections_subject_unique');
            $table->index(['organization_id', 'subject_type'], 'event_projections_org_subject_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_projections');
    }
};
