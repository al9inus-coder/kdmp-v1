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
        Schema::create('sbu_tiket_pesawats', function (Blueprint $table) {
            $table->id();
            $table->string('tujuan');
            $table->string('satuan')->default('PP');
            $table->decimal('bisnis', 15, 2)->default(0);
            $table->decimal('ekonomi', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbu_tiket_pesawats');
    }
};
