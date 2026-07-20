<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_corrections', function (Blueprint $table) {
            $table->id();

            // Objek bisnis (kunci registry, mis. 'paket-pengadaan') + id model akarnya
            $table->string('object_type', 40);
            $table->unsignedBigInteger('object_id');

            // Model & baris tempat kolom yang dikoreksi benar-benar berada
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');

            $table->string('field_key', 60);
            $table->string('field_label', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason');
            $table->string('attachment_path')->nullable();

            $table->foreignId('user_id')->constrained();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['object_type', 'object_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_corrections');
    }
};
