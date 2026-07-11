<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SubActivity;
use App\Models\TravelOrder;
use Illuminate\Http\Request;

class SppdController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');

        // Filter virtual "spj_*" = SPPD approved + status SPJ tertentu.
        $spjFilter = $status && str_starts_with($status, 'spj_') ? substr($status, 4) : null;

        // Hanya SPPD hasil pengajuan (dibuat lewat alur staf).
        $base = TravelOrder::query()
            ->whereNotNull('created_by')
            ->with(['package.subActivity', 'personnels.employee', 'reviewer']);

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
                WHEN 'revision' THEN 0
                WHEN 'draft' THEN 1
                WHEN 'submitted' THEN 2
                WHEN 'approved' THEN 3
                WHEN 'rejected' THEN 4
                ELSE 5 END")
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        // Sub kegiatan yang berhak dibuatkan SPPD: punya paket rekening perjalanan dinas.
        $eligibleSubActivities = SubActivity::query()
            ->with([
                'activity.program',
                'packages' => fn ($q) => $q
                    ->whereHas('account', fn ($account) => $account->where('nama', 'like', '%perjalanan dinas%'))
                    ->with('account')
                    ->orderByDesc('id'),
            ])
            ->whereHas('packages.account', fn ($q) => $q->where('nama', 'like', '%perjalanan dinas%'))
            ->orderBy('kode')
            ->get();

        return view('staf.sppd.index', compact(
            'travelOrders', 'statusCounts', 'spjCounts', 'status', 'search', 'eligibleSubActivities'
        ));
    }

    public function create()
    {
        $eligibleSubActivity = SubActivity::query()
            ->with([
                'packages' => fn ($q) => $q
                    ->whereHas('account', fn ($account) => $account->where('nama', 'like', '%perjalanan dinas%'))
                    ->orderByDesc('id'),
            ])
            ->whereHas('packages.account', fn ($q) => $q->where('nama', 'like', '%perjalanan dinas%'))
            ->orderBy('kode')
            ->first();

        $firstEligiblePackage = $eligibleSubActivity?->packages->first();

        if ($firstEligiblePackage) {
            return redirect()->route('staf.packages.travel-orders.create', $firstEligiblePackage);
        }

        return redirect()->route('staf.sppd.index')->with('error', 'Tidak ada sub kegiatan dengan paket perjalanan dinas yang tersedia untuk diajukan.');
    }
}
