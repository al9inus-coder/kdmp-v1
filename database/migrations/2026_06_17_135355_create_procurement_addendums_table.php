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
        Schema::create('procurement_addendums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_package_id')->constrained()->cascadeOnDelete();
            $table->string('nomor')->nullable();
            $table->date('tanggal_akhir_baru')->nullable();
            $table->text('alasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_addendums');
    }
};
