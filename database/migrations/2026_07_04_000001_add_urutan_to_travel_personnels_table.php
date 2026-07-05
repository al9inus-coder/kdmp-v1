<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_personnels', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->default(0)->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('travel_personnels', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
