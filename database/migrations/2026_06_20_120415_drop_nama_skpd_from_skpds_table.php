<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('skpds', 'nama_skpd')) {
            Schema::table('skpds', function (Blueprint $table) {
                $table->dropColumn('nama_skpd');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('skpds', 'nama_skpd')) {
            Schema::table('skpds', function (Blueprint $table) {
                $table->string('nama_skpd')->nullable()->after('kode');
            });
        }
    }
};