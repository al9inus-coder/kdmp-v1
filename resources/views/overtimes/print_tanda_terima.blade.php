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
            .no-print { display: none !important; }
            /* @page { size: landscape; margin: 10mm; } */
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
<body>

    {{-- Tombol Aksi Pratinjau (Sembunyi saat cetak) --}}
    <div class="no-print" style="margin-bottom: 20px; padding: 10px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: space-between;">
        <span style="font-family: sans-serif; font-size: 12px; color: #334155; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0284c7;"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
            Pratinjau Tanda Terima Uang Lembur
        </span>
        <button type="button" onclick="window.print()" style="padding: 8px 18px; background: #10b981; color: #ffffff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 12px; font-family: sans-serif; display: inline-flex; align-items: center; gap: 6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect width="12" height="8" x="6" y="14" rx="1"/></svg>
            Cetak Dokumen
        </button>
    </div>

    @php
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
            4 => 'April', 5 => 'Mei', 6 => 'Juni', 
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $months[$overtime->bulan];

        // Perhitungan terpusat di Overtime::rekap(). Catatan semantik dokumen:
        // "Jumlah yang Diterima" pada tanda terima = upah lembur - PPh
        // (TANPA uang makan), berbeda dari rekap layar yang memasukkan uang makan.
        // Mode SPJ periode mengirim baris gabungan lintas bulan ($spjRows).
        if (!isset($spjRows)) {
            $rekapData = $overtime->rekap($sbuRates);
            $spjRows = $rekapData['rows'];
            $totalKeseluruhan = $rekapData['totalUpah'];
            $totalPph = $rekapData['totalPajak'];
        } else {
            $totalKeseluruhan = $spjTotalUpah;
            $totalPph = $spjTotalPajak;
        }
        $totalDiterimaBersih = $totalKeseluruhan - $totalPph;
        $labelPeriode = $periodeLabel ?? ($months[$overtime->bulan] . ' ' . $overtime->tahun);
    @endphp

    <div class="header">
        <h4>DAFTAR TANDA TERIMA UANG LEMBUR PETUGAS PENGELOLAAN SAMPAH<br>
        {{ strtoupper($skpd->nama ?? 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP KABUPATEN BENGKAYANG') }}</h4>
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
            <td>{{ strtoupper($labelPeriode) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 20%;">NAMA</th>
                <th rowspan="2" style="width: 18%;">JABATAN</th>
                <th colspan="3">PERHITUNGAN UANG LEMBUR</th>
                <th rowspan="2" style="width: 8%;">PPh</th>
                <th rowspan="2" style="width: 12%;">JUMLAH YANG DITERIMA</th>
                <th rowspan="2" style="width: 13%;">TANDA TANGAN</th>
            </tr>
            <tr>
                <th style="width: 8%;">SATUAN OJ</th>
                <th style="width: 6%;">JAM LEMBUR</th>
                <th style="width: 12%;">UANG LEMBUR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($spjRows as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['employee']->nama }}</td>
                    <td class="text-center">{{ strtoupper($row['employee']->jabatan ?? 'STAF') }}</td>
                    <td class="text-right">{{ $row['valLembur'] !== null ? 'Rp ' . number_format($row['valLembur'], 0, ',', '.') : '-' }}</td>
                    <td class="text-center">{{ $row['totalJam'] }}</td>
                    <td class="text-right">Rp {{ number_format($row['uangLembur'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ $row['pajak'] > 0 ? number_format($row['pajak'], 0, ',', '.') : '-' }}</td>
                    <td class="text-right">Rp {{ number_format($row['uangLembur'] - $row['pajak'], 0, ',', '.') }}</td>
                    <td class="text-left" style="vertical-align: top; position: relative;">
                        @if($index % 2 === 0)
                            <div style="text-align: left; padding-left: 4px;">{{ $index + 1 }}.</div>
                        @else
                            <div style="text-align: left; padding-left: 48%;">{{ $index + 1 }}.</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-footer">
                <th colspan="5" class="text-right">Jumlah :</th>
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
            <br><br><br><br><br>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $skpd->nama_pptk ?? '..........................' }}</p>
            <p style="margin: 0;">NIP. {{ $skpd->nip_pptk ?? '..........................' }}</p>
        </div>
        
        <div style="float: left; width: 40%; text-align: center;">
            <p style="margin: 0;">Mengetahui/ Menyetujui :</p>
            <p style="margin: 0; font-weight: bold;">Pengguna Anggaran Dinas PERKIMPLH<br>Kabupaten Bengkayang,</p>
            <br><br><br><br>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $skpd->kepala_skpd ?? '..........................' }}</p>
            <p style="margin: 0;">NIP. {{ $skpd->nip_kepala ?? '..........................' }}</p>
        </div>

        <div style="float: left; width: 30%; text-align: center;">
            <p style="margin: 0;">Bengkayang, ...................... {{ $overtime->tahun }}</p>
            <p style="margin: 0;">Lunas dibayarkan Oleh</p>
            <p style="margin: 0; font-weight: bold;">Bendahara Pengeluaran</p>
            <br><br><br><br>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $skpd->nama_bendahara ?? '..........................' }}</p>
            <p style="margin: 0;">NIP. {{ $skpd->nip_bendahara ?? '..........................' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
