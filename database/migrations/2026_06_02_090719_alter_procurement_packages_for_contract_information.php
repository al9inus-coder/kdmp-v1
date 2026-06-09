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

        $table->boolean('ada_garansi')
            ->default(false)
            ->after('garansi_nilai');

        $table->integer('jangka_waktu_nilai')
            ->nullable()
            ->after('jenis_kontrak');

        $table->enum('jangka_waktu_satuan', [
            'hari',
            'bulan',
            'tahun'
        ])
        ->nullable()
        ->after('jangka_waktu_nilai');

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
