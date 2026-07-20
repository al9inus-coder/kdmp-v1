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
        Schema::table('procurement_packages', function (Blueprint $table) {

    // PPK

    $table->string('nama_ppk')->nullable();

    $table->string('pangkat_gol_ppk')->nullable();

    $table->string('nip_ppk')->nullable();

    $table->string('no_telp_ppk')->nullable();

    $table->string('email_ppk')->nullable();

    $table->string('npwp_instansi')->nullable();

    // Kontrak

    $table->string('jenis_kontrak')->nullable();

    $table->integer('jangka_waktu')->nullable();

    $table->enum('jangka_waktu_jenis', [
        'pengiriman_barang',
        'pekerjaan_jasa'
    ])->nullable();

    $table->integer('garansi_nilai')->nullable();

    $table->enum('garansi_satuan', [
        'hari',
        'bulan',
        'tahun'
    ])->nullable();

    $table->boolean('layanan_purna_jual')
        ->default(false);

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_packages', function (Blueprint $table) {
            //
        });
    }
};
