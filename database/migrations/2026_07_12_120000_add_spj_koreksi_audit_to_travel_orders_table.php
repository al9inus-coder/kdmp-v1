<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jejak audit koreksi biaya rampung oleh Admin (untuk SPPD yang sudah disetujui).
     */
    public function up(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('travel_orders', 'spj_koreksi_by')) {
                $table->unsignedBigInteger('spj_koreksi_by')->nullable()->after('spj_reviewed_by');
            }
            if (!Schema::hasColumn('travel_orders', 'spj_koreksi_at')) {
                $table->timestamp('spj_koreksi_at')->nullable()->after('spj_koreksi_by');
            }
            if (!Schema::hasColumn('travel_orders', 'spj_koreksi_catatan')) {
                $table->string('spj_koreksi_catatan')->nullable()->after('spj_koreksi_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn(['spj_koreksi_by', 'spj_koreksi_at', 'spj_koreksi_catatan']);
        });
    }
};
