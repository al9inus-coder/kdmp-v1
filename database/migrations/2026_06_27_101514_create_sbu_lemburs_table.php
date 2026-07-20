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
        Schema::create('sbu_lemburs', function (Blueprint $table) {
            $table->id();
            $table->string('jenis');
            $table->string('golongan');
            $table->string('satuan');
            $table->integer('besaran');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbu_lemburs');
    }
};
