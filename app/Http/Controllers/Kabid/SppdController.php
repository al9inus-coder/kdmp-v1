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

        // Filter virtual "spj_*" = SPPD approved + status SPJ tertentu.
        $spjFilter = $status && str_starts_with($status, 'spj_') ? substr($status, 4) : null;

        $base = TravelOrder::query()
            ->whereNotNull('created_by')
            ->where('status', '!=', 'draft')
            ->with(['package.subActivity', 'personnels.employee', 'creator', 'reviewer']);

        $statusCounts = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Hitungan status SPJ (hanya relevan untuk SPPD yang sudah disetujui; null = draf).
        $spjCounts = (clone $base)
            ->where('status', TravelOrder::STATUS_APPROVED)
            ->selectRaw("COALESCE(NULLIF(spj_status, ''), 'draft') as spj, COUNT(*) as total")
            ->groupBy('spj')
            ->pluck('total', 'spj');

        $travelOrders = (clone $base)
            ->when($spjFilter, fn ($q) => $q
                ->where('status', TravelOrder::STATUS_APPROVED)
                ->when($spjFilter === TravelOrder::SPJ_DRAFT,
                    fn ($qq) => $qq->where(fn ($w) => $w->whereNull('spj_status')->orWhere('spj_status', TravelOrder::SPJ_DRAFT)),
                    fn ($qq) => $qq->where('spj_status', $spjFilter)))
            ->when($status && !$spjFilter, fn ($q) => $q->where('status', $status))
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

        return view('kabid.sppd.index', compact('travelOrders', 'statusCounts', 'spjCounts', 'status', 'search'));
    }
}
