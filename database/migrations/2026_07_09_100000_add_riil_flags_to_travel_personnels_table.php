<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda komponen biaya SPJ:
     * - transport_riil / taksi_riil : tanpa bukti pengeluaran, masuk Daftar Pengeluaran Riil.
     * - penginapan_riil             : tidak menginap di hotel, dibayar 30% tarif SBU
     *                                 (TIDAK masuk Daftar Pengeluaran Riil).
     */
    public function up(): void
    {
        Schema::table('travel_personnels', function (Blueprint $table) {
            $table->boolean('transport_riil')->default(false)->after('biaya_transport');
            $table->boolean('taksi_riil')->default(false)->after('biaya_taksi');
            $table->boolean('penginapan_riil')->default(false)->after('biaya_penginapan');
        });
    }

    public function down(): void
    {
        Schema::table('travel_personnels', function (Blueprint $table) {
            $table->dropColumn(['transport_riil', 'taksi_riil', 'penginapan_riil']);
        });
    }
};
