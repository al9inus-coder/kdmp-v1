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
        Schema::create('procurement_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')
                ->unique()
                ->constrained('packages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('number')->nullable();
            $table->enum('status', ['draft', 'complete'])->default('draft');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_packages');
    }
};
