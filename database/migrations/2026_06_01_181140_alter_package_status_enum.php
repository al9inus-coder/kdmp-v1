<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite tidak memiliki tipe ENUM maupun sintaks ALTER ... MODIFY.
        // Kolom enum pada SQLite diperlakukan sebagai string, sehingga nilai
        // "submitted" tetap dapat digunakan saat test tanpa perubahan skema.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE packages
            MODIFY status ENUM(
                'needs_review',
                'draft',
                'submitted',
                'approved'
            ) NOT NULL DEFAULT 'needs_review'
        ");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE packages
            MODIFY status ENUM(
                'needs_review',
                'draft',
                'approved'
            ) NOT NULL DEFAULT 'needs_review'
        ");
    }
};
