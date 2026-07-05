<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\TravelOrder;
use Illuminate\Http\Request;

class SppdController extends Controller
{
    public function index(Request $request)
    {
        // Default menampilkan yang perlu ditinjau (Diajukan) lebih dahulu.
        $status = $request->input('status');
        $search = $request->input('search');

        $base = TravelOrder::query()
            ->whereNotNull('created_by')
            ->with(['package.subActivity', 'personnels.employee', 'creator', 'reviewer']);

        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $travelOrders = (clone $base)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('tempat_tujuan', 'like', "%{$search}%")
                ->orWhere('maksud_perjalanan', 'like', "%{$search}%")
                ->orWhereHas('package', fn ($p) => $p->where('nama_paket', 'like', "%{$search}%"))
            ))
            ->orderByRaw("CASE status
                WHEN 'submitted' THEN 0
                WHEN 'revision' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
                ELSE 4 END")
            ->orderByDesc('submitted_at')
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('kabid.sppd.index', compact('travelOrders', 'statusCounts', 'status', 'search'));
    }
}
