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
        Schema::create('travel_personnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->constrained('travel_orders')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('nomor_sppd')->nullable();
            $table->decimal('uang_harian', 15, 2)->default(0);
            $table->decimal('biaya_transport', 15, 2)->default(0);
            $table->decimal('biaya_penginapan', 15, 2)->default(0);
            $table->decimal('biaya_representasi', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_personnels');
    }
};
