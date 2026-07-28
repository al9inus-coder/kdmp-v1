<?php

namespace App\Http\Controllers\Kabid;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\ProcurementPackage;
use App\Models\TravelOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Kalender Kegiatan (Kabid): perjalanan dinas, masa & batas akhir kontrak,
 * dan hari libur dalam satu tampilan bulan/tahun.
 */
class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $tahun = max(2000, min(2100, $tahun));
        $awalTahun = Carbon::create($tahun, 1, 1)->startOfDay();
        $akhirTahun = Carbon::create($tahun, 12, 31)->endOfDay();

        // Perjalanan dinas disetujui yang bersinggungan dengan tahun tampil.
        $travels = TravelOrder::query()
            ->whereNotNull('created_by')
            ->where('status', TravelOrder::STATUS_APPROVED)
            ->whereDate('tanggal_berangkat', '<=', $akhirTahun)
            ->whereDate('tanggal_kembali', '>=', $awalTahun)
            ->with(['package:id', 'personnels.employee:id,nama'])
            ->get()
            ->filter(fn ($to) => $to->package && $to->tanggal_berangkat && $to->tanggal_kembali)
            ->map(function ($to) {
                $pelaksana = $to->personnels->sortBy('urutan')
                    ->map(fn ($p) => $p->employee?->nama)
                    ->filter()
                    ->values();

                $ketua = $pelaksana->first() ?? 'Pegawai';
                $jumlah = $pelaksana->count();

                return [
                    'label' => 'SPPD — ' . $to->tempat_tujuan,
                    // 'sub' tetap bentuk ringkas untuk tooltip sel yang sempit;
                    // 'nama' memuat semua pelaksana untuk panel Agenda.
                    'sub' => $ketua . ($jumlah > 1 ? ' +' . ($jumlah - 1) . ' pelaksana' : ''),
                    'nama' => $jumlah ? $pelaksana->implode(', ') : 'Pegawai',
                    'tujuan' => $to->tempat_tujuan,
                    'start' => $to->tanggal_berangkat->format('Y-m-d'),
                    'end' => $to->tanggal_kembali->format('Y-m-d'),
                    'url' => route('kabid.packages.travel-orders.show', [$to->package, $to]),
                ];
            })->values();

        // Kontrak dengan jadwal pelaksanaan yang bersinggungan dengan tahun tampil.
        $contracts = ProcurementPackage::query()
            ->whereIn('workflow_status', [
                ProcurementPackage::WORKFLOW_EXECUTION,
                ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
                ProcurementPackage::WORKFLOW_COMPLETED,
            ])
            ->whereHas('procurementProcess', fn ($q) => $q
                ->whereNotNull('tanggal_surat_pesanan')
                ->whereNotNull('tanggal_barang_diterima'))
            ->with([
                'package:id,nama_paket',
                'procurementProcess:id,procurement_package_id,tanggal_surat_pesanan,tanggal_barang_diterima',
                'payment:id,procurement_package_id,tanggal_bast',
            ])
            ->get()
            ->filter(function ($pp) use ($awalTahun, $akhirTahun) {
                $p = $pp->procurementProcess;

                return $pp->package
                    && $p
                    && $p->tanggal_surat_pesanan->lte($akhirTahun)
                    && $p->tanggal_barang_diterima->gte($awalTahun);
            })
            ->map(function ($pp) {
                $batas = $pp->procurementProcess->tanggal_barang_diterima->format('Y-m-d');
                $bast = $pp->payment?->tanggal_bast
                    ? Carbon::parse($pp->payment->tanggal_bast)->format('Y-m-d')
                    : null;

                return [
                    'label' => Str::limit($pp->package->nama_paket, 60),
                    'start' => $pp->procurementProcess->tanggal_surat_pesanan->format('Y-m-d'),
                    // Kalender berhenti menandai di hari serah terima, bukan di batas
                    // kontrak: setelah BAST pekerjaannya sudah selesai, jadi hari
                    // sesudahnya tidak perlu ditandai apa-apa.
                    'end' => $bast ?: $batas,
                    'batas' => $batas,
                    'bast' => $bast,
                    'finished' => in_array($pp->workflow_status, [
                        ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
                        ProcurementPackage::WORKFLOW_COMPLETED,
                    ]),
                    'url' => route('kabid.procurement-packages.execution.show', $pp->package),
                ];
            })->values();

        // Kode huruf identitas kontrak. Diurutkan tanggal mulai supaya kode yang
        // sama selalu menunjuk kontrak yang sama selama daftarnya tidak berubah,
        // dan supaya pembacaan legenda mengikuti urutan kalender.
        $contracts = $contracts
            ->sortBy(fn ($e) => $e['start'] . '|' . $e['label'])
            ->values()
            ->map(function ($e, $i) {
                $e['kode'] = $this->kodeHuruf($i);

                return $e;
            });

        // Hari libur (map iso => keterangan).
        $holidays = Holiday::whereYear('holiday_date', $tahun)
            ->get()
            ->mapWithKeys(fn ($h) => [
                Carbon::parse($h->holiday_date)->format('Y-m-d') => $h->description ?? 'Hari libur',
            ]);

        // Bulan awal tampilan: param ?bulan=0-11, default bulan berjalan / Januari.
        $bulanAwal = $request->filled('bulan')
            ? max(0, min(11, (int) $request->input('bulan')))
            : ($tahun === now()->year ? now()->month - 1 : 0);

        return view('kabid.calendar.index', compact('tahun', 'travels', 'contracts', 'holidays', 'bulanAwal'));
    }

    /**
     * Kode huruf ala kolom spreadsheet: A..Z, lalu AA, AB, dan seterusnya.
     * Dinas biasanya hanya punya belasan kontrak setahun, jadi praktis selalu
     * satu huruf — dua huruf cuma jaring pengaman supaya kode tidak pernah
     * bertabrakan kalau suatu tahun kontraknya membengkak.
     */
    private function kodeHuruf(int $index): string
    {
        $kode = '';

        for ($n = $index; $n >= 0; $n = intdiv($n, 26) - 1) {
            $kode = chr(65 + $n % 26) . $kode;
        }

        return $kode;
    }
}
