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
            $table->string('golongan_fix')->nullable()->after('rate_makan_fix');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtime_details', function (Blueprint $table) {
            $table->dropColumn('golongan_fix');
        });
    }
};
