<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Dokumen Pembayaran - {{ $procurementPackage->package->nama_paket }}</title>
    <style>
        /* Reset and Base Styles */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        /* @page {
            size: A4 portrait;
            @if(isset($type) && $type == 'ringkasan-kontrak')
            margin: 0.5cm 0.5cm 1cm 0.5cm;
            @else
            margin: 1cm;
            @endif
        } */

        /* Print Specific Styles */
        @media print {
            body {
                background: none;
            }
            .no-print {
                display: none !important;
            }
            .document-section {
                page-break-after: always;
            }
            .document-section:last-child {
                page-break-after: auto;
            }
        }

        /* Reusable Components */
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .font-weight-bold { font-weight: bold; }
        .mt-2 { margin-top: 10px; }
        .mt-3 { margin-top: 15px; }
        .mt-4 { margin-top: 20px; }
        .mt-5 { margin-top: 30px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        .mb-5 { margin-bottom: 30px; }
        .pt-2 { padding-top: 10px; }
        .pb-2 { padding-bottom: 10px; }
        
        table {
            border-collapse: collapse;
        }

        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            z-index: 1000;
        }
    </style>
</head>
<body>

    @if(!request('embed'))
        <button class="btn-print no-print" onclick="window.print()">Cetak</button>
    @endif

    @php
        $type = $type ?? 'all';
        $skpd = \App\Models\Skpd::first();
    @endphp

    @if($type === 'all' || $type === 'bap')
        @include('procurement-payments.partials.bap', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
    @endif

    @if($type === 'all' || $type === 'kwitansi')
        @include('procurement-payments.partials.kwitansi', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
    @endif

    @if(($type === 'all' && $payment->is_non_pkp) || $type === 'non-pkp')
        @include('procurement-payments.partials.non-pkp', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
    @endif

    @if($type === 'all' || $type === 'ringkasan-kontrak')
        @include('procurement-payments.partials.ringkasan-kontrak', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
    @endif

</body>
</html>
