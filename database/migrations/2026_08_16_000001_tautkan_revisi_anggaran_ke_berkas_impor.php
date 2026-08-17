<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menautkan revisi anggaran ke berkas DPA sumbernya.
 *
 * Nomor dasar hukum sudah dicatat, tetapi berkas cetakannya yang menjawab
 * pertanyaan pemeriksa "angka ini dari mana": nomor bisa salah ketik, berkasnya
 * tidak. Nullable — revisi yang diketik manual memang tidak punya berkas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_revisions', function (Blueprint $table) {
            $table->foreignId('import_batch_id')->nullable()->after('nomor_dasar')
                ->constrained('import_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('budget_revisions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_batch_id');
        });
    }
};
