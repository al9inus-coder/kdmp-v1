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
        Schema::create('procurement_external_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_package_id')->constrained()->onDelete('cascade');
            
            // Surat Pesanan
            $table->string('surat_pesanan_no')->nullable();
            $table->date('surat_pesanan_tgl')->nullable();
            
            // Surat Tagihan
            $table->string('surat_tagihan_no')->nullable();
            $table->date('surat_tagihan_tgl')->nullable();
            
            // BAST
            $table->string('bast_no')->nullable();
            $table->date('bast_tgl')->nullable();
            
            // BAP
            $table->string('bap_no')->nullable();
            $table->date('bap_tgl')->nullable();
            
            // Kwitansi
            $table->string('kwitansi_no')->nullable();
            $table->date('kwitansi_tgl')->nullable();
            
            // Nilai Kontrak / Nilai Tagihan
            $table->decimal('nilai_kontrak', 18, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_external_records');
    }
};
