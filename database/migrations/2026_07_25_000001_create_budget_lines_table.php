<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baris anggaran DPA: satu rekening belanja di dalam satu sub kegiatan,
 * pada satu tahun anggaran. Inilah pemegang pagu resmi — bukan lagi
 * hasil penjumlahan pagu paket RUP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('sub_activity_id')->constrained('sub_activities')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();

            // Nilai dari revisi terakhir. Disimpan agar monev tidak perlu
            // menelusuri riwayat tiap kali menghitung.
            $table->decimal('pagu_efektif', 18, 2)->default(0);

            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(
                ['fiscal_year_id', 'sub_activity_id', 'account_id'],
                'budget_lines_unik'
            );
            $table->index(['fiscal_year_id', 'sub_activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
