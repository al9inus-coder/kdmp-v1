<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Laporan perjalanan dinas (satu per SPPD): input dasar + prompt user
     * + hasil narasi AI per bagian yang bisa diedit sebelum dicetak.
     */
    public function up(): void
    {
        Schema::create('travel_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_order_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('nomor_surat_tugas')->nullable();
            $table->date('tanggal_surat_tugas')->nullable();
            $table->string('nomor_spd')->nullable();
            $table->date('tanggal_spd')->nullable();

            $table->text('prompt_latar_belakang')->nullable();
            $table->text('prompt_kegiatan')->nullable();
            $table->text('prompt_hasil')->nullable();
            $table->text('prompt_kesimpulan')->nullable();
            $table->text('prompt_penutup')->nullable();

            $table->text('hasil_latar_belakang')->nullable();
            $table->text('hasil_kegiatan')->nullable();
            $table->text('hasil_dicapai')->nullable();
            $table->text('hasil_kesimpulan')->nullable();
            $table->text('hasil_penutup')->nullable();

            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_reports');
    }
};
