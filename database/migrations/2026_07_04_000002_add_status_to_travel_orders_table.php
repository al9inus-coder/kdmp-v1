<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            // Default 'approved' agar SPPD lama & yang dibuat Kabid (di luar alur pengajuan) dianggap final.
            $table->string('status')->default('approved')->after('tanggal_surat');
            $table->text('catatan_review')->nullable()->after('status');
            $table->timestamp('submitted_at')->nullable()->after('catatan_review');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('reviewed_at');
            $table->unsignedBigInteger('created_by')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('travel_orders', function (Blueprint $table) {
            $table->dropColumn(['status', 'catatan_review', 'submitted_at', 'reviewed_at', 'reviewed_by', 'created_by']);
        });
    }
};
