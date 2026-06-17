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
        Schema::table('procurement_processes', function (Blueprint $table) {
            $table->unsignedBigInteger('nilai_kontrak')->nullable()->after('npwp_penyedia');
            $table->string('nomor_rekening')->nullable()->after('nilai_kontrak');
            $table->string('nama_bank')->nullable()->after('nomor_rekening');
            $table->string('nama_pic')->nullable()->after('nama_bank');
            $table->string('jabatan_pic')->nullable()->after('nama_pic');
            $table->date('tanggal_mulai_kontrak')->nullable()->after('jabatan_pic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_processes', function (Blueprint $table) {
            $table->dropColumn([
                'nilai_kontrak',
                'nomor_rekening',
                'nama_bank',
                'nama_pic',
                'jabatan_pic',
                'tanggal_mulai_kontrak'
            ]);
        });
    }
};
