<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\SubActivity;
use App\Models\TravelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Kalender Kegiatan (Staff): Hanya perjalanan dinas dan hari libur.
 */
class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $tahun = max(2000, min(2100, $tahun));
        $awalTahun = Carbon::create($tahun, 1, 1)->startOfDay();
        $akhirTahun = Carbon::create($tahun, 12, 31)->endOfDay();

        // Perjalanan dinas (tampilkan semua kecuali yang ditolak)
        $travels = TravelOrder::query()
            ->whereNotNull('created_by')
            ->where('status', '!=', TravelOrder::STATUS_REJECTED)
            ->whereDate('tanggal_berangkat', '<=', $akhirTahun)
            ->whereDate('tanggal_kembali', '>=', $awalTahun)
            ->with(['package:id', 'personnels.employee:id,nama'])
            ->get()
            ->filter(fn ($to) => $to->package && $to->tanggal_berangkat && $to->tanggal_kembali)
            ->map(function ($to) {
                $ketua = $to->personnels->sortBy('urutan')->first()?->employee?->nama ?? 'Pegawai';
                $jumlah = $to->personnels->count();

                return [
                    'label' => 'SPPD — ' . $to->tempat_tujuan,
                    'sub' => $ketua . ($jumlah > 1 ? ' +' . ($jumlah - 1) . ' pelaksana' : ''),
                    'start' => $to->tanggal_berangkat->format('Y-m-d'),
                    'end' => $to->tanggal_kembali->format('Y-m-d'),
                    'url' => route('staf.packages.travel-orders.show', [$to->package, $to]),
                ];
            })->values();

        // Hari libur (map iso => keterangan)
        $holidays = Holiday::whereYear('holiday_date', $tahun)
            ->get()
            ->mapWithKeys(fn ($h) => [
                Carbon::parse($h->holiday_date)->format('Y-m-d') => $h->description ?? 'Hari libur',
            ]);

        // Sub kegiatan yang berhak dibuatkan SPPD (punya paket rekening perjalanan dinas).
        // Setiap item membawa package_id paket perjalanan dinas-nya untuk create SPPD.
        $eligibleSubActivities = SubActivity::query()
            ->with([
                'packages' => fn ($q) => $q
                    ->whereHas('account', fn ($account) => $account->where('nama', 'like', '%perjalanan dinas%'))
                    ->orderByDesc('id'),
            ])
            ->whereHas('packages.account', fn ($q) => $q->where('nama', 'like', '%perjalanan dinas%'))
            ->orderBy('kode')
            ->get()
            ->map(fn ($sa) => [
                'id' => $sa->id,
                'label' => trim(($sa->kode ? $sa->kode . ' — ' : '') . $sa->nama),
                'package_id' => $sa->packages->first()?->id,
            ])
            ->filter(fn ($sa) => $sa['package_id'])
            ->values();

        $bulanAwal = $request->filled('bulan')
            ? max(0, min(11, (int) $request->input('bulan')))
            : ($tahun === now()->year ? now()->month - 1 : 0);

        return view('staf.calendar.index', compact('tahun', 'travels', 'holidays', 'bulanAwal', 'eligibleSubActivities'));
    }
}
