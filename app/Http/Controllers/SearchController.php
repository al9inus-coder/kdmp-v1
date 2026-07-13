<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class SearchController extends Controller
{
    /**
     * Pencarian cepat paket berdasarkan ID RUP atau nama paket.
     * Dipakai oleh kotak pencarian di header (live search / JSON).
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $routeName = $this->packageRouteName($request->user());

        $packages = Package::query()
            ->where(function ($query) use ($q) {
                $query->where('id_rup', 'like', "%{$q}%")
                    ->orWhere('nama_paket', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'id_rup', 'nama_paket', 'status']);

        $results = $packages->map(function (Package $package) use ($routeName) {
            return [
                'id_rup'     => $package->id_rup,
                'nama_paket' => $package->nama_paket,
                'status'     => $package->status,
                'url'        => ($routeName && Route::has($routeName))
                    ? route($routeName, $package)
                    : '#',
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Nama route halaman detail paket sesuai peran user yang login.
     * Semua route ini terikat ke model Package (key: id_rup).
     */
    private function packageRouteName($user): ?string
    {
        if (!$user) {
            return null;
        }

        if ($user->hasRole('Staff')) {
            return 'staf.packages.show';
        }

        if ($user->hasRole('Kabid')) {
            return 'kabid.packages.show';
        }

        if ($user->hasAnyRole(['Admin', 'Super Admin'])) {
            return (auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.show';
        }

        return null;
    }
}
