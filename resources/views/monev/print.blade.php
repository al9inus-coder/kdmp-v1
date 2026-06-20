<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Kendali Sub Kegiatan - {{ $subActivity->kode }}</title>
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
        
        .w-4 { width: 4%; }
        .w-12 { width: 12%; }
        .w-20 { width: 20%; }
        .w-22 { width: 22%; }
        .w-14 { width: 14%; }
        .w-8 { width: 8%; }
        
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
        <h2>KARTU KENDALI SUB KEGIATAN</h2>
        @php
            $firstPackage = $subActivity->packages->first();
            $tahun = $firstPackage ? ($firstPackage->fiscalYear->tahun ?? date('Y')) : date('Y');
        @endphp
        <h3>Tahun Anggaran {{ $tahun }}</h3>
    </div>

    <div class="header-info">
        <table>
            <tr>
                <td>Program</td>
                <td>: {{ $subActivity->activity->program->kode ?? '-' }} - {{ $subActivity->activity->program->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kegiatan</td>
                <td>: {{ $subActivity->activity->kode ?? '-' }} - {{ $subActivity->activity->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sub Kegiatan</td>
                <td>: {{ $subActivity->kode }} - {{ $subActivity->nama }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th class="w-4">No.</th>
                <th class="w-12">Kode Rekening</th>
                <th class="w-22">Uraian Belanja</th>
                <th class="w-14">Pagu Anggaran Murni</th>
                <th class="w-12">Pagu Pergeseran/<br>Pergeseran</th>
                <th class="w-14">Realisasi</th>
                <th class="w-14">Sisa Pagu</th>
                <th class="w-8">Persentase<br>Serapan<br>Anggaran</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $sumPagu = 0;
                $sumRealisasi = 0;
            @endphp
            
            @php
                $groupedPackages = $subActivity->packages->groupBy(function($pkg) {
                    return $pkg->account ? $pkg->account->id : 'none';
                });
            @endphp
            
            @forelse($groupedPackages as $accountId => $packages)
                @php
                    $account = $packages->first()->account;
                    
                    $groupPagu = $packages->sum('pagu');
                    $groupRealisasi = 0;
                    foreach($packages as $pkg) {
                        if($pkg->procurementPackage && $pkg->procurementPackage->procurementProcess) {
                            $groupRealisasi += (float) $pkg->procurementPackage->procurementProcess->nilai_kontrak;
                        }
                    }
                    
                    $groupSisa = $groupPagu - $groupRealisasi;
                    $groupPersen = $groupPagu > 0 ? ($groupRealisasi / $groupPagu) * 100 : 0;

                    $sumPagu += $groupPagu;
                    $sumRealisasi += $groupRealisasi;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="text-center">{{ $account->kode ?? '-' }}</td>
                    <td>{{ $account->nama ?? 'Tanpa Uraian Belanja' }}</td>
                    <td class="text-right">{{ number_format($groupPagu, 2, ',', '.') }}</td>
                    <td class="text-center">-</td>
                    <td class="text-right">{{ number_format($groupRealisasi, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($groupSisa, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($groupPersen, 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: #666; font-style: italic;">Belum ada paket pekerjaan di sub kegiatan ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
                $sumSisa = $sumPagu - $sumRealisasi;
                $sumPersen = $sumPagu > 0 ? ($sumRealisasi / $sumPagu) * 100 : 0;
            @endphp
            <tr class="font-weight-bold">
                <td colspan="3" class="text-center">Jumlah</td>
                <td class="text-right">{{ number_format($sumPagu, 2, ',', '.') }}</td>
                <td class="text-center">-</td>
                <td class="text-right">{{ number_format($sumRealisasi, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($sumSisa, 2, ',', '.') }}</td>
                <td class="text-center">{{ number_format($sumPersen, 2, ',', '.') }}%</td>
            </tr>
        </tfoot>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
