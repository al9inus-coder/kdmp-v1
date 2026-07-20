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
        Schema::table('procurement_requests', function (Blueprint $table) {

            $table->text('alasan_pemilihan_penyedia')
                ->nullable()
                ->after('nama_penyedia');

        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {

            $table->dropColumn('alasan_pemilihan_penyedia');

        });
    }
};
