<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Jam Lembur — {{ $periodeLabel }}</title>
    <style>
        /* @page {
            size: A4 landscape;
            margin: 10mm 15mm;
        } */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            -webkit-print-color-adjust: exact;
        }
        .page {
            max-width: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border-radius: 8px;
            box-sizing: border-box;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 20px;
            line-height: 1.4;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
            font-size: 10px;
            font-weight: bold;
        }
        .info-table td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            font-size: 9.5px;
        }
        .data-table th {
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .signature-section {
            width: 100%;
            margin-top: 25px;
            font-size: 10px;
            page-break-inside: avoid;
        }
        .signature-box {
            float: right;
            width: 40%;
            text-align: center;
        }
        .signature-box-left {
            float: right;
            width: 45%;
            text-align: center;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        @media print {
            body { background: none; margin: 0; padding: 0; }
            .page {
                max-width: none;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- Tombol Aksi Pratinjau (Sembunyi saat cetak) --}}
    <div class="no-print" style="margin-bottom: 20px; padding: 10px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: space-between;">
        <span style="font-family: sans-serif; font-size: 12px; color: #334155; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0284c7;"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
            Pratinjau Dokumen Rekap Jam Lembur
        </span>
        <button type="button" onclick="window.print()" style="padding: 8px 18px; background: #10b981; color: #ffffff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 12px; font-family: sans-serif; display: inline-flex; align-items: center; gap: 6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect width="12" height="8" x="6" y="14" rx="1"/></svg>
            Cetak Dokumen
        </button>
    </div>

    <div class="page">
        {{-- Judul Kop --}}
    <div class="header-title">
        REKAPITULASI LEMBUR PETUGAS PENGELOLAAN SAMPAH<br>
        {{ strtoupper($skpd->nama ?? 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN PERTANAHAN DAN LINGKUNGAN HIDUP KABUPATEN BENGKAYANG') }}
    </div>

    {{-- Info Metadata --}}
    <table class="info-table">
        <tr>
            <td style="width: 130px;">PROGRAM</td>
            <td style="width: 10px;">:</td>
            <td style="width: 55%;">{{ strtoupper($package->program->nama ?? '') }}</td>
            <td style="width: 100px;">No. BKU</td>
            <td style="width: 10px;">:</td>
            <td></td>
        </tr>
        <tr>
            <td>KEGIATAN</td>
            <td>:</td>
            <td>{{ strtoupper($package->activity->nama ?? '') }}</td>
            <td>TANGGAL</td>
            <td>:</td>
            <td></td>
        </tr>
        <tr>
            <td>SUB KEGIATAN</td>
            <td>:</td>
            <td>{{ strtoupper($package->subActivity->nama ?? '') }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>KODE REKENING</td>
            <td>:</td>
            <td>{{ $package->account->kode ?? '' }} ({{ strtoupper($package->account->nama ?? '') }})</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>PERIODE LEMBUR</td>
            <td>:</td>
            <td>{{ strtoupper($periodeLabel) }}</td>
            <td colspan="3"></td>
        </tr>
    </table>

    {{-- Tabel Utama --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 22%;">NAMA</th>
                <th rowspan="2" style="width: 20%;">JABATAN</th>
                <th colspan="{{ count($selectedMonths) }}">REKAPITULASI JAM LEMBUR PER BULAN</th>
                <th rowspan="2" style="width: 9%;">JUMLAH JAM LEMBUR</th>
                <th rowspan="2" style="width: 16%;">TANDA TANGAN</th>
            </tr>
            <tr>
                @foreach($selectedMonths as $mNum => $mName)
                    <th style="width: {{ round(30 / count($selectedMonths), 1) }}%;">{{ $mName }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rekapRows as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left" style="font-weight: bold;">{{ $row['employee']->nama }}</td>
                    <td class="text-left">{{ strtoupper($row['jabatan']) }}</td>
                    @foreach($selectedMonths as $mNum => $mName)
                        <td class="text-center">{{ $row['monthlyHours'][$mNum] ?? 0 }}</td>
                    @endforeach
                    <td class="text-center" style="font-weight: bold;">{{ $row['totalJam'] }}</td>
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
    </table>

    {{-- Blok Tanda Tangan Footer --}}
    <div class="signature-section clearfix">
        <div style="float: right; width: 100%;">
            {{-- Kolom Pembuat (Kanan) --}}
            <div style="float: right; width: 35%; text-align: center;">
                <p style="margin: 0;">Bengkayang, {{ \Carbon\Carbon::now()->translatedFormat('j F Y') }}</p>
                <p style="margin: 4px 0 0 0;">Dibuat oleh :</p>
                <p style="margin: 0; font-weight: bold;">{{ $pembuat ? ($pembuat->jabatan ?? 'Operator Layanan Operasional') : 'Operator Layanan Operasional' }}</p>
                <div style="height: 55px;"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                    {{ $pembuat ? $pembuat->nama : ($skpd->nama_pptk ?? 'HERKULANUS') }}
                </p>
                @if($pembuat && $pembuat->golongan)
                    <p style="margin: 0;">{{ $pembuat->golongan }}</p>
                @endif
                <p style="margin: 0;">NIP. {{ $pembuat ? ($pembuat->nip ?? '-') : ($skpd->nip_pptk ?? '198004152007011012') }}</p>
            </div>

            {{-- Kolom Verifikator (Tengah / Kiri) --}}
            <div style="float: right; width: 45%; text-align: center;">
                <p style="margin: 0;">&nbsp;</p>
                <p style="margin: 4px 0 0 0;">Diverifikasi oleh :</p>
                <p style="margin: 0; font-weight: bold;">
                    Kepala Bidang Pengelolaan Persampahan dan<br>Ruang Terbuka Hijau
                </p>
                <div style="height: 40px;"></div>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                    {{ $skpd->nama_pptk ?? 'ALGINUS, S.Si' }}
                </p>
                <p style="margin: 0;">{{ $skpd->pangkat_pptk ?? 'Penata Tk. I/ III-d' }}</p>
                <p style="margin: 0;">NIP. {{ $skpd->nip_pptk ?? '198603112011011003' }}</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
