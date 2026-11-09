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
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('category');
        });

        Schema::table('dues', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->after('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_transactions', function (Blueprint $table) {
            $table->dropColumn('proof_path');
        });

        Schema::table('dues', function (Blueprint $table) {
            $table->dropColumn('proof_path');
        });
    }
};
