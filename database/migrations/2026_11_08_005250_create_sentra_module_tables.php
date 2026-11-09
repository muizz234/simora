<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel modul SENTRA (Manajemen Keuangan dan Aset).
     * budgets.work_program_id terhubung ke work_programs (E-PROGRESS)
     * agar anggaran tercatat atas program kerja yang sama.
     */
    public function up(): void
    {
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('category')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'type', 'date']);
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('program_name');
            $table->unsignedSmallInteger('year');
            $table->decimal('allocated', 15, 2);
            $table->decimal('realized', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'year']);
        });

        Schema::create('fund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('menunggu');
            $table->string('applicant')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });

        Schema::create('dues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('belum_bayar');
            $table->timestamp('paid_at')->nullable();
            $table->string('period')->nullable();
            $table->timestamps();
        });

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_loans');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('dues');
        Schema::dropIfExists('fund_requests');
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('cash_transactions');
    }
};
