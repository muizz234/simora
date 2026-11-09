<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus tabel aset/inventaris yang tidak dipakai (menu dihapus).
     */
    public function up(): void
    {
        Schema::dropIfExists('asset_loans');
        Schema::dropIfExists('inventories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('category')->default('lainnya');
            $table->string('condition')->nullable();
            $table->string('status')->default('tersedia');
            $table->timestamps();
        });

        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->string('borrower_name');
            $table->date('loan_date');
            $table->date('return_date')->nullable();
            $table->string('status')->default('dipinjam');
            $table->timestamps();
        });
    }
};
