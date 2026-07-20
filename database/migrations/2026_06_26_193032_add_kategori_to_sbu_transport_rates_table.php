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
        Schema::table('sbu_transport_rates', function (Blueprint $table) {
            $table->string('kategori')->default('dalam_daerah')->after('tempat_tujuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sbu_transport_rates', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
