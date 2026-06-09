<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technical_specifications', function (Blueprint $table) {
            if (!Schema::hasColumn('technical_specifications', 'target_sasaran')) {
                $table->longText('target_sasaran')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'jangka_waktu')) {
                $table->integer('jangka_waktu')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'jangka_waktu_jenis')) {
                $table->enum('jangka_waktu_jenis', [
                    'pengiriman_barang',
                    'pekerjaan_jasa',
                ])->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'garansi_nilai')) {
                $table->integer('garansi_nilai')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'garansi_satuan')) {
                $table->enum('garansi_satuan', [
                    'hari',
                    'bulan',
                    'tahun',
                ])->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'layanan_purna_jual')) {
                $table->boolean('layanan_purna_jual')->default(false);
            }

            if (!Schema::hasColumn('technical_specifications', 'jenis_kontrak')) {
                $table->string('jenis_kontrak')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'npwp_instansi')) {
                $table->string('npwp_instansi')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'nama_ppk')) {
                $table->string('nama_ppk')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'pangkat_gol_ppk')) {
                $table->string('pangkat_gol_ppk')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'nip_ppk')) {
                $table->string('nip_ppk')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'no_telp_ppk')) {
                $table->string('no_telp_ppk')->nullable();
            }

            if (!Schema::hasColumn('technical_specifications', 'email_ppk')) {
                $table->string('email_ppk')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('technical_specifications', function (Blueprint $table) {
            $columns = [
                'target_sasaran',
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
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('technical_specifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
