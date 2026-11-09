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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('slug')->unique();
            $table->string('full_name');
            $table->char('banner_color', 7);
            $table->char('accent_color', 7);
            $table->string('focus');
            $table->text('description');
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
