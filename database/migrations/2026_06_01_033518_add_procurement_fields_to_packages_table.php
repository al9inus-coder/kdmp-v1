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

            $table->string('procurement_status')
                ->default('planning')
                ->after('status');

            $table->date('target_procurement_date')
                ->nullable()
                ->after('procurement_status');

            $table->string('pptk_name')
                ->nullable()
                ->after('target_procurement_date');

            $table->string('ppk_name')
                ->nullable()
                ->after('pptk_name');

            $table->text('procurement_notes')
                ->nullable()
                ->after('ppk_name');
        });
    }

    /**
     * Reverse the migrations.
     */
        public function down(): void
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
};
