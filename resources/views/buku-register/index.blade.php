@extends('adminlte::page')

@section('title', 'Buku Register')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Buku Register</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @foreach($groupedPackages as $programName => $packages)
            <div class="card card-outline card-primary mb-4">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">{{ $programName }}</h3>
                    <div class="card-tools">
                        <button type="button" onclick="printTable('table-program-{{ $loop->index }}', '{{ addslashes($programName) }}')" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-print"></i> Cetak
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" id="table-program-{{ $loop->index }}">
                        <table class="table table-bordered table-striped table-hover mb-0">
                            <thead class="text-center align-middle">
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="20%">Nama Paket</th>
                                    <th>Surat Permohonan</th>
                                    <th>Surat Pesanan</th>
                                    <th>BAST</th>
                                    <th>Invoice</th>
                                    <th>BAP</th>
                                    <th>Kwitansi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($packages as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->package ? $item->package->nama_paket : '-' }}</td>
                                        
                                        @php
                                            $kodeProgram = $item->package?->program?->kode ?? '2.11.04';
                                        @endphp

                                        <!-- Surat Permohonan -->
                                        <td>
                                            <div class="font-weight-bold">{{ $item->procurementRequest?->nomor_surat ? '000.3.2/'.$item->procurementRequest->nomor_surat.'/SP-PBJ/'.$kodeProgram.'/PERKIMPLH-C' : '-' }}</div>
                                            <div class="text-primary fst-italic">{{ $item->procurementRequest?->tanggal_surat ? \Carbon\Carbon::parse($item->procurementRequest->tanggal_surat)->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        
                                        <!-- Surat Pesanan -->
                                        <td>
                                            <div class="font-weight-bold">{{ $item->procurementProcess?->nomor_surat_pesanan ?? '-' }}</div>
                                            <div class="text-primary fst-italic">{{ $item->procurementProcess?->tanggal_surat_pesanan ? \Carbon\Carbon::parse($item->procurementProcess->tanggal_surat_pesanan)->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        
                                        <!-- BAST -->
                                        <td>
                                            <div class="font-weight-bold">{{ $item->payment?->nomor_bast ?? '-' }}</div>
                                            <div class="text-primary fst-italic">{{ $item->payment?->tanggal_bast ? \Carbon\Carbon::parse($item->payment->tanggal_bast)->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        
                                        <!-- Invoice -->
                                        <td>
                                            <div class="font-weight-bold">{{ $item->payment?->nomor_invoice ?? '-' }}</div>
                                            <div class="text-primary fst-italic">{{ $item->payment?->tanggal_invoice ? \Carbon\Carbon::parse($item->payment->tanggal_invoice)->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        
                                        <!-- BAP -->
                                        <td>
                                            <div class="font-weight-bold">{{ $item->payment?->nomor_bap ? $item->payment->nomor_bap.'/BAP/'.$kodeProgram.'/PERKIMPLH-C' : '-' }}</div>
                                            <div class="text-primary fst-italic">{{ $item->payment?->tanggal_bap ? \Carbon\Carbon::parse($item->payment->tanggal_bap)->format('d/m/Y') : '-' }}</div>
                                        </td>
                                        
                                        <!-- Kwitansi -->
                                        <td>
                                            <div class="font-weight-bold">{{ $item->payment?->nomor_kwitansi ? $item->payment->nomor_kwitansi.'/KWT/'.$kodeProgram.'/PERKIMPLH-C' : '-' }}</div>
                                            <div class="text-primary fst-italic">{{ $item->payment?->tanggal_kwitansi ? \Carbon\Carbon::parse($item->payment->tanggal_kwitansi)->format('d/m/Y') : '-' }}</div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">Tidak ada data paket pengadaan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach

            @if($groupedPackages->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i> Belum ada data dokumen pengadaan.
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .table th, .table td {
            vertical-align: middle !important;
        }
        .table thead th {
            background-color: #f4f6f9;
            font-size: 0.9rem;
        }
        .table tbody td {
            font-size: 0.85rem;
        }
    </style>
@stop

@section('js')
    <script>
        function printTable(containerId, programName) {
            const tableHtml = document.getElementById(containerId).innerHTML;
            
            // Hapus iframe lama jika ada
            let oldIframe = document.getElementById('print-iframe');
            if (oldIframe) {
                oldIframe.remove();
            }

            // Buat iframe tersembunyi
            const iframe = document.createElement('iframe');
            iframe.id = 'print-iframe';
            iframe.style.display = 'none';
            document.body.appendChild(iframe);

            // Tulis konten ke dalam iframe
            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`
                <!DOCTYPE html>
                <html lang="id">
                <head>
                    <meta charset="UTF-8">
                    <title>Cetak Buku Register - ${programName}</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body {
                            font-family: 'Times New Roman', Times, serif;
                            font-size: 12pt;
                            margin: 20px;
                        }
                        .table th, .table td {
                            vertical-align: middle !important;
                            padding: 0.3rem;
                            border-color: #000 !important;
                        }
                        .table thead th {
                            text-align: center;
                            font-weight: bold;
                        }
                        .title-header {
                            text-align: center;
                            margin-bottom: 20px;
                            font-weight: bold;
                        }
                        @media print {
                            @page {
                                size: landscape;
                                margin: 1cm;
                            }
                        }
                        .text-primary {
                            color: #555 !important;
                        }
                    </style>
                </head>
                <body>
                    <div class="title-header">
                        <h4>BUKU REGISTER DOKUMEN PENGADAAN</h4>
                        <h5>${programName.toUpperCase()}</h5>
                    </div>
                    ${tableHtml}
                </body>
                </html>
            `);
            doc.close();

            // Tunggu sedikit agar file CSS termuat sempurna, lalu cetak
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 800);
        }
    </script>
@stop
