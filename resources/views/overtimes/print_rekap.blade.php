<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Absensi Lembur</title>
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
        .header h3, .header h4 {
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        th {
            background-color: #ffffffff;
        }
        .text-left {
            text-align: left;
        }
        .signature-area {
            width: 100%;
            margin-top: 30px;
        }
        .signature-box {
            width: 30%;
            float: right;
            text-align: center;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .bg-holiday {
            background-color: #e2e8f0 !important;
        }
        .crossed-cell {
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100%' height='100%'><line x1='0' y1='0' x2='100%' y2='100%' stroke='black' stroke-width='0.5'/><line x1='0' y1='100%' x2='100%' y2='0' stroke='black' stroke-width='0.5'/></svg>") !important;
            background-size: 100% 100% !important;
            background-repeat: no-repeat !important;
        }
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            /* @page { size: 330mm 210mm; margin: 10mm; } */
        }
    </style>
</head>
<body>

    {{-- Tombol Aksi Pratinjau (Sembunyi saat cetak) --}}
    <div class="no-print" style="margin-bottom: 20px; padding: 10px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; display: flex; align-items: center; justify-content: space-between;">
        <span style="font-family: sans-serif; font-size: 12px; color: #334155; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #0284c7;"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
            Pratinjau Rekapitulasi Absensi Lembur
        </span>
        <button type="button" onclick="window.print()" style="padding: 8px 18px; background: #10b981; color: #ffffff; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 12px; font-family: sans-serif; display: inline-flex; align-items: center; gap: 6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/><rect width="12" height="8" x="6" y="14" rx="1"/></svg>
            Cetak Dokumen
        </button>
    </div>

    @include('overtimes.partials.rekap_absensi_bulan')
</body>
</html>
