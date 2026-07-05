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
        Schema::create('overtime_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->json('daily_hours')->nullable();
            $table->boolean('use_uang_makan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_details');
    }
};
