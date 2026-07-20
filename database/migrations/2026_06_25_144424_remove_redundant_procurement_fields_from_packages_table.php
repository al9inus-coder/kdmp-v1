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
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'procurement_status',
                'target_procurement_date',
                'pptk_name',
                'ppk_name',
                'procurement_notes',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('procurement_status')->default('planning');
            $table->date('target_procurement_date')->nullable();
            $table->string('pptk_name')->nullable();
            $table->string('ppk_name')->nullable();
            $table->text('procurement_notes')->nullable();
        });
    }
};
