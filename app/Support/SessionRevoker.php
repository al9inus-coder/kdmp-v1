<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class SessionRevoker
{
    /**
     * Hapus sesi database milik user, opsional dengan pengecualian sesi aktif.
     * Driver non-database harus memakai mekanisme revocation sesuai backend-nya.
     */
    public static function revoke(User $user, ?string $exceptSessionId = null): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $table = config('session.table', 'sessions');
        $connection = config('session.connection');
        $query = $connection
            ? DB::connection($connection)->table($table)
            : DB::table($table);

        $query->where('user_id', $user->getKey())
            ->when($exceptSessionId, fn ($q) => $q->where('id', '!=', $exceptSessionId))
            ->delete();
    }
}
