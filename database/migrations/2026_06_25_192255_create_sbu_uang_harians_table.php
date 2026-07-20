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
        Schema::create('sbu_uang_harians', function (Blueprint $table) {
            $table->id();
            $table->string('provinsi');
            $table->string('satuan')->default('OH');
            $table->decimal('luar_kota', 15, 2)->default(0);
            $table->decimal('diklat', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbu_uang_harians');
    }
};
