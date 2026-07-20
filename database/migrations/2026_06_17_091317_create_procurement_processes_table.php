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
        Schema::create('procurement_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_package_id')->constrained('procurement_packages')->cascadeOnDelete();
            
            $table->string('nomor_surat_pesanan')->nullable();
            $table->date('tanggal_surat_pesanan')->nullable();
            
            $table->string('nama_penyedia')->nullable();
            $table->text('alamat_penyedia')->nullable();
            $table->string('npwp_penyedia')->nullable();
            
            $table->integer('waktu_pelaksanaan_nilai')->nullable();
            $table->string('waktu_pelaksanaan_satuan')->nullable();
            
            $table->date('tanggal_barang_diterima')->nullable();
            
            $table->text('catatan')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_processes');
    }
};
