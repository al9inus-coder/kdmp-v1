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
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->string('nama_pptk')->nullable();
            $table->string('nip_pptk')->nullable();
            $table->string('pangkat_golongan_pptk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->dropColumn(['nama_pptk', 'nip_pptk', 'pangkat_golongan_pptk']);
        });
    }
};
