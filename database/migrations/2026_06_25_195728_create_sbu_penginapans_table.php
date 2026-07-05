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
        Schema::create('sbu_penginapans', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string('satuan')->default('OH');
            $table->decimal('eselon_ii', 15, 2)->default(0); // Anggota DPRD / Pejabat Eselon II
            $table->decimal('eselon_iii', 15, 2)->default(0); // Pejabat Eselon III/Jafung Madya/Golongan IV
            $table->decimal('eselon_iv', 15, 2)->default(0); // Pejabat Eselon IV/Jafung/Golongan III/II/I/P3K/Non ASN
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbu_penginapans');
    }
};
