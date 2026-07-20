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

            $table->string('nama_penyedia')
                ->nullable()
                ->after('kepada');

        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {

            $table->dropColumn('nama_penyedia');

        });
    }
};
