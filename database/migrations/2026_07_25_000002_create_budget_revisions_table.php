<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat perubahan pagu tiap baris DPA: murni -> pergeseran -> perubahan.
 * Kolom `pagu` menyimpan nilai ABSOLUT setelah revisi tersebut, bukan
 * selisihnya — selisih dihitung terhadap revisi sebelumnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_revisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('budget_line_id')->constrained('budget_lines')->cascadeOnDelete();

            $table->string('jenis', 20);            // murni | pergeseran | perubahan
            $table->unsignedTinyInteger('urutan')->default(1); // pergeseran ke-1, ke-2, ...
            $table->date('tanggal')->nullable();
            $table->string('nomor_dasar')->nullable();          // Perda / Perkada
            $table->decimal('pagu', 18, 2)->default(0);
            $table->text('keterangan')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['budget_line_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_revisions');
    }
};
