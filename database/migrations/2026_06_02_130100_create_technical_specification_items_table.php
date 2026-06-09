<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_specification_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technical_specification_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('nama_barang_jasa');
            $table->longText('spesifikasi')->nullable();
            $table->decimal('volume', 18, 2)->default(0);
            $table->string('satuan')->nullable();
            $table->boolean('pdn')->default(false);
            $table->decimal('tkdn', 5, 2)->nullable();
            $table->string('kode_mak')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_specification_items');
    }
};
