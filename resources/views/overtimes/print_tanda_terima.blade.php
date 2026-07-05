<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tanda Terima Uang Lembur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h4 {
            margin: 0;
            padding: 0;
            font-size: 13px;
        }
        .header p {
            margin: 5px 0;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.info-table td {
            border: none;
            padding: 2px 5px;
            vertical-align: top;
            font-weight: bold;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        table.data-table th {
            text-align: center;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        @media print {
            body { margin: 0; padding: 0; }
            @page { size: landscape; margin: 10mm; }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .bg-footer {
                background-color: #dcdcdc !important;
                box-shadow: inset 0 0 0 1000px #dcdcdc !important;
            }
        }
        .bg-footer {
            background-color: #dcdcdc;
        }
    </style>
</head>
<body onload="window.print()" onafterprint="window.close()">

    @php
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
            4 => 'April', 5 => 'Mei', 6 => 'Juni', 
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $months[$overtime->bulan];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $overtime->bulan, $overtime->tahun);
        $totalKeseluruhan = 0;
        $totalPph = 0;
        $totalDiterimaBersih = 0;
    @endphp

    <div class="header">
        <h4>DAFTAR TANDA TERIMA UANG LEMBUR</h4>
        <p>BERDASARKAN DENGAN SURAT TUGAS {{ strtoupper($skpd->nama ?? 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP KABUPATEN BENGKAYANG') }}<br>
        {!! nl2br(e(strtoupper($overtime->dasar_pelaksanaan ?? 'NOMOR : ..............................................'))) !!}</p>
    </div>

    <table class="info-table" style="width: 100%; margin-bottom: 15px;">
        <tr>
            <td style="width: 150px;">PROGRAM</td>
            <td style="width: 10px;">:</td>
            <td>{{ strtoupper($package->program->nama ?? '') }}</td>
        </tr>
        <tr>
            <td>KEGIATAN</td>
            <td>:</td>
            <td>{{ strtoupper($package->activity->nama ?? '') }}</td>
        </tr>
        <tr>
            <td>SUB KEGIATAN</td>
            <td>:</td>
            <td>{{ strtoupper($package->subActivity->nama ?? '') }}</td>
        </tr>
        <tr>
            <td>KODE REKENING</td>
            <td>:</td>
            <td>{{ $package->account->kode ?? '' }} ({{ strtoupper($package->account->nama ?? '') }})</td>
        </tr>
        <tr>
            <td>PERIODE</td>
            <td>:</td>
            <td>{{ strtoupper($monthName) }} {{ $overtime->tahun }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 15%;">NAMA</th>
                <th rowspan="2" style="width: 15%;">PANGKAT/GOLONGAN</th>
                <th rowspan="2" style="width: 12%;">KEDUDUKAN DALAM TIM</th>
                <th colspan="2">JAM LEMBUR</th>
                <th rowspan="2" style="width: 10%;">JUMLAH UANG LEMBUR</th>
                <th rowspan="2" style="width: 9%;">PPh</th>
                <th rowspan="2" style="width: 10%;">JUMLAH YANG DITERIMA</th>
                <th rowspan="2" style="width: 15%;">TANDA TANGAN</th>
            </tr>
            <tr>
                <th style="width: 5%;">JAM</th>
                <th style="width: 6%;">SATUAN OJ</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($overtime->details as $index => $detail)
                @php
                    $emp = $detail->employee;
                    $golongan = $detail->golongan_fix ?? $emp->golongan ?? '-';
                    
                    $totalJam = 0;
                    for($d = 1; $d <= $daysInMonth; $d++) {
                        $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                        if($val >= 2) {
                            $totalJam += $val;
                        }
                    }
                    
                    if($totalJam == 0) continue; // Only show if they worked
                    
                    $empGol = strtoupper($golongan);
                    $mappedGolongan = 'P3K Paruh Waktu'; // default

                    if (str_contains($empGol, 'IV-') || str_contains($empGol, '/IV') || str_contains($empGol, 'GOLONGAN IV')) {
                        $mappedGolongan = 'Golongan IV';
                    } elseif (str_contains($empGol, 'III-') || str_contains($empGol, '/III') || str_contains($empGol, 'GOLONGAN III')) {
                        $mappedGolongan = 'Golongan III';
                    } elseif (str_contains($empGol, 'II-') || str_contains($empGol, '/II') || str_contains($empGol, 'GOLONGAN II') || str_contains($empGol, 'VII')) {
                        $mappedGolongan = 'Golongan II';
                    } elseif (str_contains($empGol, 'I-') || str_contains($empGol, '/I') || str_contains($empGol, 'GOLONGAN I')) {
                        $mappedGolongan = 'Golongan I';
                    }
                    
                    if (!is_null($detail->rate_lembur_fix)) {
                        $valLembur = $detail->rate_lembur_fix;
                    } else {
                        $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->where('golongan', $mappedGolongan)->first();
                        if(!$rateLembur) $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->sortBy('besaran')->first();
                        $valLembur = $rateLembur ? $rateLembur->besaran : 0;
                    }
                    $uangLembur = $totalJam * $valLembur;
                    
                    // Hitung PPh 21 berdasarkan Golongan
                    $pphRate = 0;
                    if (str_contains(strtoupper($golongan), 'III')) {
                        $pphRate = 0.05;
                    } elseif (str_contains(strtoupper($golongan), 'IV')) {
                        $pphRate = 0.15;
                    }
                    $pph = $uangLembur * $pphRate;
                    $diterima = $uangLembur - $pph;
                    
                    $totalKeseluruhan += $uangLembur;
                    $totalPph += $pph;
                    $totalDiterimaBersih += $diterima;
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $emp->nama }}</td>
                    <td class="text-center">{{ $golongan }}</td>
                    <td class="text-center">{{ strtoupper($emp->jabatan ?? 'STAF') }}</td>
                    <td class="text-center">{{ $totalJam }}</td>
                    <td class="text-right">Rp {{ number_format($valLembur, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($uangLembur, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ $pph > 0 ? number_format($pph, 0, ',', '.') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($diterima, 0, ',', '.') }}</td>
                    <td class="text-left" style="vertical-align: top;">
                        {{ $no - 1 }}.
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-footer">
                <th colspan="6" class="text-right">Jumlah :</th>
                <th class="text-right">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalPph, 0, ',', '.') }}</th>
                <th class="text-right">Rp {{ number_format($totalDiterimaBersih, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div style="width: 100%; margin-top: 30px;">
        <div style="float: left; width: 30%; text-align: center;">
            <p style="margin: 0;">Dibuat oleh :</p>
            <p style="margin: 0; font-weight: bold;">Pejabat Pelaksana Teknis Kegiatan</p>
            <br><br><br><br>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $skpd->nama_pptk ?? '..........................' }}</p>
            <p style="margin: 0;">NIP. {{ $skpd->nip_pptk ?? '..........................' }}</p>
        </div>
        
        <div style="float: left; width: 40%; text-align: center;">
            <p style="margin: 0;">Mengetahui/ Menyetujui :</p>
            <p style="margin: 0; font-weight: bold;">Pengguna Anggaran Dinas PERKIMPLH<br>Kabupaten Bengkayang,</p>
            <br><br><br>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $skpd->kepala_skpd ?? '..........................' }}</p>
            <p style="margin: 0;">NIP. {{ $skpd->nip_kepala ?? '..........................' }}</p>
        </div>

        <div style="float: left; width: 30%; text-align: center;">
            <p style="margin: 0;">Bengkayang, ...................... {{ $overtime->tahun }}</p>
            <p style="margin: 0;">Lunas dibayarkan Oleh</p>
            <p style="margin: 0; font-weight: bold;">Bendahara</p>
            <br><br><br>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $skpd->nama_bendahara ?? '..........................' }}</p>
            <p style="margin: 0;">NIP. {{ $skpd->nip_bendahara ?? '..........................' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
