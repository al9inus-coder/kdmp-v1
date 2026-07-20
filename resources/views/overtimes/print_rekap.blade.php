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
            /* @page { size: 330mm 210mm; margin: 10mm; } */
        }
    </style>
</head>
<body>

    @include('overtimes.partials.rekap_absensi_bulan')

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
        window.onafterprint = () => {
            window.close();
        };
    </script>
</body>
</html>
