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
        Schema::create('sbu_penginapan_dalam_daerahs', function (Blueprint $table) {
            $table->id();
            $table->string('tempat_tujuan');
            $table->string('satuan')->default('OH');
            $table->decimal('eselon_ii', 15, 2)->default(0);
            $table->decimal('eselon_iii', 15, 2)->default(0);
            $table->decimal('eselon_iv', 15, 2)->default(0);
            $table->decimal('golongan_i_ii', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbu_penginapan_dalam_daerahs');
    }
};
