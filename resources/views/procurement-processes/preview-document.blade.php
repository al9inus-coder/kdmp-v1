@component('layouts.kdmp')

@section('title', 'Preview SSKK & SSUK')

@slot('header')
    <div class="flex justify-between items-center">
        <h1 class="font-bold text-slate-800">
            <i class="fas fa-file-pdf text-red-600 mr-2"></i> Preview Dokumen SSKK & SSUK
        </h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.index') }}">Daftar Paket</a></li>
            <li class="breadcrumb-item"><a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-process.show', $procurementPackage->package) }}">{{ $procurementPackage->package->id_rup }}</a></li>
            <li class="breadcrumb-item active">Preview Dokumen</li>
        </ol>
    </div>
@endslot
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

<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <!-- Kolom Aksi (Kiri) -->
    <div class="md:col-span-3">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-t-4 border-blue-500 sticky-top" style="top: 20px;">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center font-bold">Aksi Dokumen</h3>
            </div>
            <div class="p-6">
                <p class="text-slate-500 small mb-6">Pastikan tampilan dokumen di sebelah kanan sudah sesuai sebelum Anda mencetaknya.</p>
                
                <button onclick="printPdf('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-process.print-document', $procurementPackage->package) }}')" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full justify-center -lg shadow-sm mb-6">
                    <i class="fas fa-print mr-2"></i> Cetak Dokumen PDF
                </button>

                @if(in_array($procurementPackage->workflow_status, [
                    \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION,
                    \App\Models\ProcurementPackage::WORKFLOW_EXECUTION,
                    \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
                    \App\Models\ProcurementPackage::WORKFLOW_COMPLETED
                ]))
                    <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.execution.start', $procurementPackage->package) }}" method="POST" class="mb-6">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 w-full justify-center shadow-sm">
                            <i class="fas fa-play mr-2"></i> Lanjut Pelaksanaan
                        </button>
                    </form>
                @endif
                
                <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-process.show', $procurementPackage->package) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 w-full justify-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Surat Pesanan
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-t-4 border-blue-400 mt-6">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center font-bold"><i class="fas fa-info-circle text-blue-400 mr-2"></i> Dokumen Tercetak</h3>
            </div>
            <div class="p-6 p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item"><i class="fas fa-check text-emerald-600 mr-2"></i> Syarat-Syarat Khusus Kontrak (SSKK)</li>
                    <li class="list-group-item"><i class="fas fa-check text-emerald-600 mr-2"></i> Syarat-Syarat Umum Kontrak (SSUK)</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kolom Preview (Kanan) -->
    <div class="col-md-9">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden shadow">
            @php
                $skpd = \App\Models\Skpd::first();
            @endphp
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 bg-dark text-white">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center"><i class="fas fa-eye mr-2"></i> Tampilan Pratinjau Halaman Cetak</h3>
            </div>
            <div class="p-6 bg-light p-6" style="max-height: 800px; overflow-y: auto;">
                
                {{-- Kertas 1: SSKK --}}
                <div class="document-paper shadow-sm bg-white mb-8" style="padding: 2cm 2cm 2cm 2.5cm; width: 100%; border: 1px solid #ccc; font-family: 'Times New Roman', Times, serif; color: #000; position: relative;">
                    <div style="font-weight: bold; text-align: center; font-size: 14pt; margin-bottom: 20px;">
                        SYARAT-SYARAT KHUSUS KONTRAK (SSKK) PESANAN
                    </div>
                    @include('procurement-processes.partials.sskk', ['procurementPackage' => $procurementPackage, 'process' => $process, 'skpd' => $skpd])
                </div>

                {{-- Kertas 2: SSUK --}}
                <div class="document-paper shadow-sm bg-white" style="padding: 2cm 2cm 2cm 2.5cm; width: 100%; border: 1px solid #ccc; font-family: 'Times New Roman', Times, serif; color: #000; position: relative;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <img src="{{ asset('images/logo-bengkayang.png') }}" style="width: 80px; margin-bottom: 10px;">
                        <div style="font-size: 14pt;">PEMERINTAH KABUPATEN BENGKAYANG</div>
                        <div style="font-size: 16pt; font-weight: bold;">{{ strtoupper($skpd->nama) }}</div>
                        <div style="font-size: 12pt;">Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat</div>
                        <div style="font-size: 12pt;">Situs : bengkayangkab.go.id</div>
                        <hr style="border-top: 3px solid #000; border-bottom: 1px solid #000; margin-top: 5px; margin-bottom: 20px; padding-bottom: 2px;">
                    </div>
                    
                    <div style="font-weight: bold; text-align: center; font-size: 14pt; margin-bottom: 20px;">
                        SYARAT-SYARAT UMUM KONTRAK (SSUK) PESANAN
                    </div>
                    @include('procurement-processes.partials.ssuk', ['procurementPackage' => $procurementPackage, 'process' => $process, 'skpd' => $skpd])
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Iframe tersembunyi untuk mencetak PDF secara background -->
<iframe id="print-iframe" style="display: none;"></iframe>



@push('js')
<script>
    function printPdf(url) {
        let iframe = document.getElementById('print-iframe');
        iframe.src = url;
        iframe.onload = function() {
            setTimeout(function() {
                iframe.contentWindow.print();
            }, 500); // Tunggu setengah detik agar iframe benar-benar termuat sebelum print dialog muncul
        };
    }
</script>
@endpush

@endcomponent
