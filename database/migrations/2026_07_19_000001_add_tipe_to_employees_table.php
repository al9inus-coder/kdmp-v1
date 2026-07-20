<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // 'dinas' = pegawai bidang/dinas (roster lembur reguler),
            // 'kebersihan' = petugas kebersihan (roster lembur via upload kehadiran).
            $table->string('tipe')->default('dinas')->after('kategori_biaya');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};
