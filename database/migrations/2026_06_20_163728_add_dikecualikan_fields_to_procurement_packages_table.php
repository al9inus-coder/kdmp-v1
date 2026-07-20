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
        Schema::table('procurement_packages', function (Blueprint $table) {
            $table->string('dikecualikan_type')->nullable()->comment('di_luar_sistem, di_dalam_sistem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procurement_packages', function (Blueprint $table) {
            $table->dropColumn([
                'dikecualikan_type'
            ]);
        });
    }
};
