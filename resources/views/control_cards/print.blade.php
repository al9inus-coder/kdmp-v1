<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Kendali Kegiatan - {{ $activity->kode }}</title>
    <style>
        @page {
            size: 330mm 215mm landscape; /* F4 Landscape */
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        h2 { font-size: 14pt; margin-bottom: 5px; }
        h3 { font-size: 12pt; margin-bottom: 15px; margin-top: 0; font-weight: normal; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10pt; }
        table.data-table, table.data-table th, table.data-table td { border: 1px solid #000; }
        table.data-table th { background-color: #f0f0f0; padding: 8px; text-align: center; vertical-align: middle; }
        table.data-table td { padding: 6px; vertical-align: top; }
        
        .w-5 { width: 5%; }
        .w-10 { width: 10%; }
        .w-15 { width: 15%; }
        .w-20 { width: 20%; }
        .w-25 { width: 25%; }
        
        .header-info { margin-bottom: 20px; }
        .header-info table { border: none; width: auto; margin-top: 0; font-size: 11pt;}
        .header-info table td { border: none; padding: 2px 10px 2px 0; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 12pt; cursor: pointer; background: #007bff; color: #fff; border: none; border-radius: 4px;">Cetak Halaman (Print)</button>
    </div>

    <div class="text-center">
        <h2>KARTU KENDALI KEGIATAN</h2>
        @php
            $firstPackage = null;
            foreach($activity->subActivities as $subAct) {
                if($subAct->packages->isNotEmpty()) {
                    $firstPackage = $subAct->packages->first();
                    break;
                }
            }
            // $tahun dari controller adalah id tahun anggaran; label tahunnya
            // diambil terpisah supaya tidak saling menimpa.
            $tahunLabel = $firstPackage ? ($firstPackage->fiscalYear->tahun ?? date('Y')) : date('Y');
        @endphp
        <h3>Tahun Anggaran {{ $tahunLabel }}</h3>
    </div>

    <div class="header-info">
        <table>
            <tr>
                <td>Program</td>
                <td>: {{ $activity->program->kode ?? '-' }} - {{ $activity->program->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kegiatan</td>
                <td>: {{ $activity->kode }} - {{ $activity->nama }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="w-5">No.</th>
                <th class="w-10">Kode Rekening</th>
                <th class="w-25">Uraian Belanja</th>
                <th class="w-15">Pagu Anggaran Murni</th>
                <th class="w-15">Pagu Setelah<br>Pergeseran/Perubahan</th>
                <th class="w-15">Realisasi</th>
                <th class="w-10">Sisa Pagu</th>
                <th class="w-5">Persentase<br>Serapan<br>Anggaran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $sumMurni = 0;
                $sumBerlaku = 0;
                $sumRealisasi = 0;
                $adaCadangan = false;
                $sbuRates = \App\Models\SbuLembur::all();

                $realisasiPaket = function ($packages) use ($sbuRates) {
                    $total = 0;
                    foreach ($packages as $pkg) {
                        if ($pkg->procurementPackage) {
                            $total += (float) $pkg->procurementPackage->realisasi;
                        }
                        foreach ($pkg->travelOrders ?? [] as $to) {
                            // Hanya SPJ (biaya rampung) yang sudah disetujui yang masuk realisasi.
                            if ($to->spjStatus() !== \App\Models\TravelOrder::SPJ_APPROVED) { continue; }
                            foreach ($to->personnels as $personnel) {
                                $total += $personnel->uang_harian + $personnel->biaya_penginapan
                                    + $personnel->biaya_representasi + $personnel->biaya_transport
                                    + ($personnel->biaya_taksi ?? 0);
                            }
                        }
                        foreach ($pkg->overtimes ?? [] as $overtime) {
                            if ($overtime->is_locked) {
                                $total += (float) $overtime->calculateTotalRealisasi($sbuRates);
                            }
                        }
                    }
                    return $total;
                };
            @endphp
            @forelse($activity->subActivities as $subActivity)
                @php
                    // Baris kartu kendali adalah rekening belanja: plafon DPA
                    // digabung dengan rekening yang sudah dipakai paket.
                    $grupPaket = $subActivity->packages->groupBy(fn ($pkg) => $pkg->account?->id ?? 'none');
                    $anggaranPerAkun = ($barisAnggaran[$subActivity->id] ?? collect())->keyBy(fn ($l) => $l->account_id ?? 'none');

                    $barisKendali = $anggaranPerAkun->keys()
                        ->merge($grupPaket->keys())
                        ->unique()
                        ->map(fn ($kunci) => [
                            'line' => $anggaranPerAkun->get($kunci),
                            'packages' => $grupPaket->get($kunci, collect()),
                        ])
                        ->map(fn ($b) => $b + ['account' => $b['line']?->account ?? $b['packages']->first()?->account])
                        ->sortBy(fn ($b) => $b['account']->kode ?? 'zzz')
                        ->values();
                @endphp
                <tr>
                    <td colspan="8" class="font-weight-bold" style="background-color: #fafafa;">Sub Kegiatan: {{ $subActivity->kode }} - {{ $subActivity->nama }}</td>
                </tr>
                @forelse($barisKendali as $baris)
                    @php
                        $line = $baris['line'];
                        $paguPaket = (float) $baris['packages']->sum('pagu');
                        $berlaku = $line ? (float) $line->pagu_efektif : $paguPaket;
                        $murni = $line ? (float) ($line->paguMurni() ?? $line->pagu_efektif) : $paguPaket;
                        $adaCadangan = $adaCadangan || ! $line;

                        $realisasi = $realisasiPaket($baris['packages']);
                        $sisa = $berlaku - $realisasi;
                        $persen = $berlaku > 0 ? ($realisasi / $berlaku) * 100 : 0;

                        $sumMurni += $murni;
                        $sumBerlaku += $berlaku;
                        $sumRealisasi += $realisasi;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="text-center">{{ $baris['account']->kode ?? '-' }}{{ $line ? '' : ' *' }}</td>
                        <td>{{ $baris['account']->nama ?? 'Tanpa Uraian Belanja' }}</td>
                        <td class="text-right">{{ number_format($murni, 2, ',', '.') }}</td>
                        <td class="text-right">{{ $berlaku != $murni ? number_format($berlaku, 2, ',', '.') : '-' }}</td>
                        <td class="text-right">{{ number_format($realisasi, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($sisa, 2, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($persen, 2, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="color: #666; font-style: italic;">Belum ada rekening belanja maupun paket pekerjaan di sub kegiatan ini.</td>
                    </tr>
                @endforelse
            @empty
                <tr>
                    <td colspan="8" class="text-center">Belum ada data sub kegiatan dan paket pekerjaan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
                $sumSisa = $sumBerlaku - $sumRealisasi;
                $sumPersen = $sumBerlaku > 0 ? ($sumRealisasi / $sumBerlaku) * 100 : 0;
            @endphp
            <tr class="font-weight-bold">
                <td colspan="3" class="text-center">Jumlah</td>
                <td class="text-right">{{ number_format($sumMurni, 2, ',', '.') }}</td>
                <td class="text-right">{{ $sumBerlaku != $sumMurni ? number_format($sumBerlaku, 2, ',', '.') : '-' }}</td>
                <td class="text-right">{{ number_format($sumRealisasi, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumSisa, 2, ',', '.') }}</td>
                <td class="text-center">{{ number_format($sumPersen, 2, ',', '.') }}%</td>
            </tr>
        </tfoot>
    </table>

    @if($adaCadangan)
        <p style="font-size: 9pt; color: #444; margin-top: 8px;">
            * Rekening belum punya plafon DPA; pagu yang tertera diambil dari jumlah pagu paket.
        </p>
    @endif

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
