<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technical_specification_items', function (Blueprint $table) {
            if (!Schema::hasColumn('technical_specification_items', 'harga_satuan_dpa')) {
                $table->decimal('harga_satuan_dpa', 15, 2)
                    ->nullable()
                    ->after('satuan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('technical_specification_items', function (Blueprint $table) {
            if (Schema::hasColumn('technical_specification_items', 'harga_satuan_dpa')) {
                $table->dropColumn('harga_satuan_dpa');
            }
        });
    }
};
