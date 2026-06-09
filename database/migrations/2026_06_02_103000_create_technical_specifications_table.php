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
        Schema::create('technical_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_package_id')
                ->unique()
                ->constrained('procurement_packages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->longText('latar_belakang')->nullable();
            $table->longText('maksud')->nullable();
            $table->longText('tujuan')->nullable();
            $table->longText('sasaran')->nullable();
            $table->longText('uraian_pekerjaan')->nullable();
            $table->text('tempat_pengiriman')->nullable();
            $table->integer('jangka_waktu_hari')->nullable();
            $table->text('garansi')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technical_specifications');
    }
};
