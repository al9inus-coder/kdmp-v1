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
    Schema::create('programs', function (Blueprint $table) {

        $table->id();

        $table->foreignId('skpd_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->string('kode')->unique();

        $table->string('nama');

        $table->year('tahun');

        $table->decimal('pagu',18,2)
              ->default(0);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
