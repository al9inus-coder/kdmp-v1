<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak SSKK & SSUK</title>
    <style>
        @page {
            size: A4;
            margin: 11mm 15mm 11mm 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .document-paper {
            width: 100%;
            page-break-after: always;
        }

        .document-paper:last-child {
            page-break-after: auto;
        }

        .kop-pemerintah{
            font-size:14pt;
            text-transform:uppercase;
            line-height:1.1;
            margin-bottom:2px;
            text-align: center;
        }

        .kop-dinas{
            font-size:15pt;
            font-weight:bold;
            text-transform:uppercase;
            line-height:1.15;
            margin-bottom:4px;
            text-align: center;
        }

        .kop-alamat{
            font-size:10pt;
            line-height:1.1;
            margin-bottom:0;
            text-align: center;
        }

        .judul-dokumen{
            font-size:12pt;
            font-weight:bold;
            text-transform:uppercase;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Table Styles for SSKK */
        .sskk-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sskk-table td {
            vertical-align: top;
            padding: 5px;
        }
        .col-letter {
            width: 30px;
            font-weight: bold;
        }
        .col-title {
            width: 200px;
            font-weight: bold;
        }
        .col-colon {
            width: 15px;
        }

        .inner-table {
            width: 100%;
            border-collapse: collapse;
        }
        .inner-table td {
            padding: 2px 0;
        }
        .inner-label {
            width: 100px;
        }
        
        /* List styles for SSUK */
        .ssuk-list {
            padding-left: 20px;
        }
        .ssuk-list li {
            margin-bottom: 5px;
            text-align: justify;
        }

        .mt-2 { margin-top: 10px; }
        .mt-3 { margin-top: 15px; }
        .mt-4 { margin-top: 20px; }
        .text-justify { text-align: justify; }

    </style>
</head>
<body onload="window.print()">

    {{-- PAGE 1: SSKK --}}
    <div class="document-paper">
        <div class="judul-dokumen">
            SYARAT-SYARAT KHUSUS KONTRAK (SSKK) PESANAN
        </div>
        
        @include('procurement-processes.partials.sskk', ['procurementPackage' => $procurementPackage, 'process' => $process])
    </div>

    {{-- PAGE 2: SSUK --}}
    <div class="document-paper">
        <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px;">
            <tr>
                <td style="width: 100px; text-align: center; padding-bottom: 10px;">
                    <img src="{{ asset('images/logo-bengkayang.png') }}" alt="Logo" style="width: 70px;">
                </td>
                <td style="text-align: center; padding-bottom: 10px;">
                    <div class="kop-pemerintah">PEMERINTAH KABUPATEN BENGKAYANG</div>
                    <div class="kop-dinas">DINAS PERUMAHAN RAKYAT DAN KAWASAN<br>PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP</div>
                    <div class="kop-alamat">Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat, Kode Pos : 79211<br>Situs : bengkayangkab.go.id</div>
                </td>
            </tr>
        </table>

        <div class="judul-dokumen mt-4">
            SYARAT-SYARAT UMUM KONTRAK (SSUK) PESANAN
        </div>
        
        @include('procurement-processes.partials.ssuk', ['procurementPackage' => $procurementPackage, 'process' => $process])
    </div>

</body>
</html>
