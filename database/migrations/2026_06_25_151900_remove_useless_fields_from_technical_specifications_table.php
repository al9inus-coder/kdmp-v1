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
        Schema::table('technical_specifications', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_dokumen',
                'tanggal_dokumen',
                'tujuan',
                'sasaran',
                'tempat_pengiriman',
                'jangka_waktu_hari',
                'garansi',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technical_specifications', function (Blueprint $table) {
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('sasaran')->nullable();
            $table->string('tempat_pengiriman')->nullable();
            $table->integer('jangka_waktu_hari')->nullable();
            $table->text('garansi')->nullable();
        });
    }
};
