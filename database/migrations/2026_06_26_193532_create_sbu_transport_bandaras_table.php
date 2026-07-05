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
        Schema::create('sbu_transport_bandaras', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string('satuan')->default('PP');
            $table->decimal('di_tempat_kedudukan', 15, 2)->default(540000);
            $table->decimal('di_tempat_tujuan', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbu_transport_bandaras');
    }
};
