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
        Schema::create('procurement_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_package_id')->constrained()->cascadeOnDelete();
            
            // BAST
            $table->string('nomor_bast')->nullable();
            $table->date('tanggal_bast')->nullable();
            
            // Invoice
            $table->string('nomor_invoice')->nullable();
            $table->date('tanggal_invoice')->nullable();
            
            // BAP
            $table->string('nomor_bap')->nullable();
            $table->date('tanggal_bap')->nullable();
            
            // Kwitansi
            $table->string('nomor_kwitansi')->nullable();
            $table->date('tanggal_kwitansi')->nullable();
            
            // Non-PKP
            $table->boolean('is_non_pkp')->default(false);
            $table->date('tanggal_non_pkp')->nullable();
            
            // Ringkasan Kontrak
            $table->date('tanggal_ringkasan_kontrak')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_payments');
    }
};
