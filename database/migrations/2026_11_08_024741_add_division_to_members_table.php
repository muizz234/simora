<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Divisi anggota, untuk menampilkan anggota di bawah
     * koordinator divisinya pada bagan struktural.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('division')->nullable()->after('faculty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('division');
        });
    }
};
