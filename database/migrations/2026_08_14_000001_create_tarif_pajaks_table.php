<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Seluruh tarif pajak dipindahkan dari kode ke data.
 *
 * Sebelumnya tersebar di tiga tempat dengan tiga cara: PPh 21 lembur sebagai
 * str_contains di Overtime::rekap(), PPN 11% dan PPh 22/23 sebagai angka lepas
 * di cetakan BAP, sedangkan pajak restoran dan PPh Final Pasal 4(2) belum ada
 * sama sekali. Tarif berubah mengikuti regulasi, dan yang tahu perubahannya
 * bendahara — bukan pengembang.
 *
 * Kolom `kunci` menampung pembeda di dalam satu jenis: golongan untuk PPh 21,
 * kualifikasi penyedia untuk PPh Final 4(2). Kosong berarti tarif berlaku
 * menyeluruh, seperti PPN.
 *
 * Kolom `dasar` menentukan dari nilai mana tarif dihitung — `dpp` untuk pajak
 * pengadaan, `bruto` untuk PPh 21 lembur dan pajak restoran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif_pajaks', function (Blueprint $table) {
            $table->id();
            $table->string('jenis');
            $table->string('kunci')->nullable();
            $table->decimal('persen', 5, 2);
            $table->string('dasar')->default('dpp');
            $table->string('keterangan')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index(['jenis', 'aktif']);
        });

        // Skema pajak bawaan per rekening belanja. Rekening adalah klasifikasi
        // resmi anggaran; jenis_pengadaan diisi manual per paket dan terbukti
        // tidak konsisten — belanja makanan yang sama tercatat "Barang" pada
        // satu paket dan "Jasa Lainnya" pada paket lain.
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('skema_pajak')->nullable()->after('nama');
        });

        Schema::table('procurement_payments', function (Blueprint $table) {
            // Pilihan manual, mengalahkan bawaan rekening.
            $table->string('skema_pajak')->nullable()->after('is_non_pkp');
            $table->string('kualifikasi_pajak')->nullable()->after('skema_pajak');

            // Dibekukan saat data penagihan disimpan. Tanpa ini, menyesuaikan
            // tarif akan menulis ulang BAP yang sudah dicetak — nominalnya
            // dihitung hidup dari nilai kontrak, tidak pernah disimpan.
            $table->decimal('persen_ppn_fix', 5, 2)->nullable()->after('kualifikasi_pajak');
            $table->decimal('persen_pph_fix', 5, 2)->nullable()->after('persen_ppn_fix');
            $table->decimal('persen_restoran_fix', 5, 2)->nullable()->after('persen_pph_fix');
        });

        Schema::table('overtime_details', function (Blueprint $table) {
            // Sejajar rate_lembur_fix dan rate_makan_fix yang sudah ada.
            $table->decimal('persen_pajak_fix', 5, 2)->nullable()->after('rate_makan_fix');
        });
    }

    public function down(): void
    {
        Schema::table('overtime_details', function (Blueprint $table) {
            $table->dropColumn('persen_pajak_fix');
        });

        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->dropColumn([
                'skema_pajak',
                'kualifikasi_pajak',
                'persen_ppn_fix',
                'persen_pph_fix',
                'persen_restoran_fix',
            ]);
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('skema_pajak');
        });

        Schema::dropIfExists('tarif_pajaks');
    }
};
