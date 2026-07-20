<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SwakelolaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Package::class);

        $ruangFilter = $request->input('ruang');

        $base = Package::query()
            ->where('jenis_pengadaan', 'Swakelola')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(fn ($w) => $w
                    ->where('nama_paket', 'like', "%{$search}%")
                    ->orWhere('id_rup', 'like', "%{$search}%"));
            });

        $stats = [
            'perjalanan' => ['count' => 0, 'total' => 0],
            'lembur'     => ['count' => 0, 'total' => 0],
            'lainnya'    => ['count' => 0, 'total' => 0],
            'ruang'      => ['created' => 0, 'count' => 0, 'total' => 0],
        ];

        (clone $base)
            ->with('account:id,nama')
            ->withExists('procurementPackage')
            ->get()
            ->each(function (Package $p) use (&$stats) {
                $nama = strtolower($p->account->nama ?? '');
                $ruang = str_contains($nama, 'perjalanan dinas') ? 'perjalanan'
                    : (str_contains($nama, 'lembur') ? 'lembur' : 'lainnya');

                $pagu = (float) $p->pagu;
                $stats[$ruang]['count']++;
                $stats[$ruang]['total'] += $pagu;
                $stats['ruang']['count']++;
                $stats['ruang']['total'] += $pagu;
                if ($p->procurement_package_exists) {
                    $stats['ruang']['created']++;
                }
            });

        $packages = (clone $base)
            ->with(['subActivity', 'account'])
            ->withExists('procurementPackage')
            ->when($ruangFilter === 'perjalanan', fn ($q) => $q
                ->whereHas('account', fn ($a) => $a->where('nama', 'like', '%perjalanan dinas%')))
            ->when($ruangFilter === 'lembur', fn ($q) => $q
                ->whereHas('account', fn ($a) => $a->where('nama', 'like', '%lembur%')))
            ->when($ruangFilter === 'lainnya', fn ($q) => $q->where(fn ($w) => $w
                ->whereHas('account', fn ($a) => $a
                    ->where('nama', 'not like', '%perjalanan dinas%')
                    ->where('nama', 'not like', '%lembur%'))
                ->orWhereDoesntHave('account')))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.swakelola.index', compact('packages', 'stats', 'ruangFilter'));
    }
}
