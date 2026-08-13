<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Satu ruang pengadaan hanya boleh punya satu baris pembayaran dan satu baris
 * proses. Model memperlakukannya begitu lewat HasOne, tapi database tidak
 * pernah menjaminnya — sementara procurement_requests dan
 * technical_specifications sudah punya batasan unik. Skemanya jadi tidak
 * konsisten, dan yang tanpa jaminan memang sudah pernah kebobolan: satu paket
 * sempat punya tiga baris pembayaran dengan nomor invoice berbeda, dan dua di
 * antaranya tidak pernah terlihat di layar mana pun karena HasOne hanya
 * mengambil yang pertama.
 *
 * Penyebabnya sudah ditutup di sisi kode sejak Juni 2026 (updateOrCreate),
 * migrasi ini memindahkan jaminannya ke tempat yang tidak bisa dilewati.
 *
 * Sengaja TIDAK menghapus baris kembar. Kalau migrasi ini gagal di suatu
 * server, itu justru sinyal bahwa ada data pembayaran ganda yang perlu
 * dilihat manusia — jauh lebih baik daripada diam-diam membuang baris uang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->unique('procurement_package_id', 'procurement_payments_package_unique');
        });

        Schema::table('procurement_processes', function (Blueprint $table) {
            $table->unique('procurement_package_id', 'procurement_processes_package_unique');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->dropUnique('procurement_payments_package_unique');
        });

        Schema::table('procurement_processes', function (Blueprint $table) {
            $table->dropUnique('procurement_processes_package_unique');
        });
    }
};
