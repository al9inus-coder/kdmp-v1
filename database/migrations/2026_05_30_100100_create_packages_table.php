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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')
                ->nullable()
                ->constrained('import_batches')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('fiscal_year_id')
                ->constrained('fiscal_years')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
           $table->foreignId('program_id')
                ->nullable()
                ->constrained('programs')
                ->nullOnDelete();
            $table->foreignId('activity_id')
                ->nullable()
                ->constrained('activities')
                ->nullOnDelete();
            $table->foreignId('sub_activity_id')
                ->nullable()
                ->constrained('sub_activities')
                ->nullOnDelete();
            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();
            $table->string('id_rup')->nullable();
            $table->string('nama_paket');
            $table->decimal('pagu', 18, 2)->default(0);
            $table->string('jenis_pengadaan')->nullable();
            $table->string('metode_pengadaan')->nullable();
            $table->unsignedTinyInteger('pemilihan_mulai_bulan')
                ->nullable();
            $table->unsignedTinyInteger('pemilihan_selesai_bulan')
                ->nullable();
            $table->unsignedTinyInteger('kontrak_mulai_bulan')
                ->nullable();
            $table->unsignedTinyInteger('kontrak_selesai_bulan')
                ->nullable();
            $table->enum('status', [
                'needs_review',
                'draft',
                'approved',
            ])->default('needs_review');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
