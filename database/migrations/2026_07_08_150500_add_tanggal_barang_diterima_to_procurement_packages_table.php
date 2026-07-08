<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('procurement_packages', 'tanggal_barang_diterima')) {
                $table->date('tanggal_barang_diterima')
                    ->nullable()
                    ->after('jangka_waktu_satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_packages', function (Blueprint $table) {
            $table->dropColumn('tanggal_barang_diterima');
        });
    }
};
