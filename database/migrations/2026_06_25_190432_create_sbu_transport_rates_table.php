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
        Schema::create('sbu_transport_rates', function (Blueprint $table) {
            $table->id();
            $table->string('tempat_kedudukan'); // e.g., "Bengkayang"
            $table->string('tempat_tujuan'); // e.g., "Sungai Raya"
            $table->string('satuan')->default('PP'); // PP
            $table->decimal('biaya_mobil', 15, 2)->default(0);
            $table->decimal('biaya_motor', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbu_transport_rates');
    }
};
