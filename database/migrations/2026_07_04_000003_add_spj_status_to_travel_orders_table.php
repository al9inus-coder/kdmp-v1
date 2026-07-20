<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            // Status SPJ (pertanggungjawaban biaya rampung). null = belum ada SPJ / draf.
            $table->string('spj_status')->nullable()->after('created_by');
            $table->text('spj_catatan')->nullable()->after('spj_status');
            $table->timestamp('spj_submitted_at')->nullable()->after('spj_catatan');
            $table->timestamp('spj_reviewed_at')->nullable()->after('spj_submitted_at');
            $table->unsignedBigInteger('spj_reviewed_by')->nullable()->after('spj_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn(['spj_status', 'spj_catatan', 'spj_submitted_at', 'spj_reviewed_at', 'spj_reviewed_by']);
        });
    }
};
