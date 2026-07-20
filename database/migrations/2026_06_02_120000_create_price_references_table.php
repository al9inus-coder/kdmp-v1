<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_package_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('nama_barang_jasa');
            $table->string('nama_produk_etalase')->nullable();
            $table->decimal('volume', 15, 2)->default(1);
            $table->string('satuan')->nullable();
            $table->string('nama_pelaku_usaha')->nullable();
            $table->decimal('harga_satuan', 18, 2)->default(0);
            $table->decimal('jumlah_harga', 18, 2)->default(0);
            $table->text('link_produk')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_references');
    }
};
