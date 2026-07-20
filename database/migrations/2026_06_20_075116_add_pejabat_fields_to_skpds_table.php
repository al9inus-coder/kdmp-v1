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
        Schema::table('skpds', function (Blueprint $table) {
            $table->string('singkatan')->nullable()->after('nama');
            $table->string('nip_kepala')->nullable()->after('kepala_skpd');
            $table->string('npwp_dinas')->nullable();
            
            $table->string('nama_ppk')->nullable();
            $table->string('nip_ppk')->nullable();
            $table->string('pangkat_ppk')->nullable();
            $table->string('telepon_ppk')->nullable();
            $table->string('email_ppk')->nullable();
            $table->string('username_ppk')->nullable();
            
            $table->string('nama_pptk')->nullable();
            $table->string('nip_pptk')->nullable();
            $table->string('pangkat_pptk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skpds', function (Blueprint $table) {
            $table->dropColumn([
                'singkatan', 'nip_kepala', 'npwp_dinas',
                'nama_ppk', 'nip_ppk', 'pangkat_ppk', 'telepon_ppk', 'email_ppk', 'username_ppk',
                'nama_pptk', 'nip_pptk', 'pangkat_pptk'
            ]);
        });
    }
};
