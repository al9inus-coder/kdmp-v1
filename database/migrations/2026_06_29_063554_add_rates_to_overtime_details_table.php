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
        Schema::table('overtime_details', function (Blueprint $table) {
            $table->integer('rate_lembur_fix')->nullable()->after('use_uang_makan');
            $table->integer('rate_makan_fix')->nullable()->after('rate_lembur_fix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtime_details', function (Blueprint $table) {
            $table->dropColumn(['rate_lembur_fix', 'rate_makan_fix']);
        });
    }
};
