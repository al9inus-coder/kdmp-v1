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
        Schema::table('technical_specifications', function (Blueprint $table) {
            $table->dropColumn([
                'jangka_waktu',
                'jangka_waktu_jenis',
                'garansi_nilai',
                'garansi_satuan',
                'layanan_purna_jual',
                'jenis_kontrak',
                'npwp_instansi',
                'nama_ppk',
                'pangkat_gol_ppk',
                'nip_ppk',
                'no_telp_ppk',
                'email_ppk',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technical_specifications', function (Blueprint $table) {
            $table->integer('jangka_waktu')->nullable();
            $table->string('jangka_waktu_jenis')->nullable();
            $table->integer('garansi_nilai')->nullable();
            $table->string('garansi_satuan')->nullable();
            $table->boolean('layanan_purna_jual')->nullable();
            $table->string('jenis_kontrak')->nullable();
            $table->string('npwp_instansi')->nullable();
            $table->string('nama_ppk')->nullable();
            $table->string('pangkat_gol_ppk')->nullable();
            $table->string('nip_ppk')->nullable();
            $table->string('no_telp_ppk')->nullable();
            $table->string('email_ppk')->nullable();
        });
    }
};
