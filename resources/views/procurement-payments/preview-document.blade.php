@component('layouts.kdmp')

@section('title', 'Preview Dokumen Pembayaran')

@slot('header')
    <div class="flex justify-between items-center">
        <h1 class="font-bold text-slate-800">
            <i class="fas fa-file-pdf text-red-600 mr-2"></i> Preview Dokumen Pembayaran
        </h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.index') }}">Daftar Paket</a></li>
            <li class="breadcrumb-item"><a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.show', $procurementPackage->package) }}">{{ $procurementPackage->package->id_rup }}</a></li>
            <li class="breadcrumb-item active">Preview Dokumen</li>
        </ol>
    </div>
@endslot


@push('css')
<style>
    .document-paper {
        max-width: 850px;
        width: 100%;
        min-height: 1100px; /* Approximate A4 height proportion */
        padding: 40px;
        margin: 0 auto;
        background: white;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        position: relative;
    }
    
    /* Custom styling for inactive tabs */
    .nav-tabs .nav-link:not(.active) {
        color: #495057;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-bottom: 0;
        margin-right: 5px;
    }
    .nav-tabs .nav-link:not(.active):hover {
        background-color: #e2e6ea;
    }
    .nav-tabs .nav-link.active {
        border: 1px solid #dee2e6;
        border-bottom-color: transparent;
        background-color: #fff;
        color: #007bff;
        margin-right: 5px;
    }
</style>
@endpush
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
    <!-- Kolom Aksi (Kiri) -->
    <div class="md:col-span-3">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-t-4 border-blue-500 sticky-top" style="top: 20px;">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center font-bold">Aksi Dokumen</h3>
            </div>
            <div class="p-6">
                <p class="text-slate-500 small mb-6">Pastikan tampilan dokumen di sebelah kanan sudah sesuai sebelum Anda mencetaknya.</p>
                <button onclick="printPdf('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.print-document', ['package' => $procurementPackage->package, 'type' => 'bap']) }}')" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full justify-center mb-2 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak BAP
                </button>
                <button onclick="printPdf('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.print-document', ['package' => $procurementPackage->package, 'type' => 'kwitansi']) }}')" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full justify-center mb-2 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak Kwitansi
                </button>
                @if($payment->is_non_pkp)
                <button onclick="printPdf('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.print-document', ['package' => $procurementPackage->package, 'type' => 'non-pkp']) }}')" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full justify-center mb-2 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak Non-PKP
                </button>
                @endif
                <button onclick="printPdf('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.print-document', ['package' => $procurementPackage->package, 'type' => 'ringkasan-kontrak']) }}')" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full justify-center mb-6 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak Ringkasan Kontrak
                </button>
                <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.show', $procurementPackage->package) }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 w-full justify-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Pembayaran
                </a>
                
                <hr class="mt-6 mb-6">
                
                <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.complete', $procurementPackage->package) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 w-full justify-center -lg shadow-sm" onclick="return confirm('Apakah Anda yakin semua dokumen sudah dicetak dan proses pengadaan telah selesai?')">
                        <i class="fas fa-check-circle mr-2"></i> Selesaikan Proses
                    </button>
                </form>
            </div>
        </div>
        
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden border-t-4 border-blue-400 mt-6">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
                <h3 class="text-lg font-semibold text-slate-800 flex items-center font-bold"><i class="fas fa-info-circle text-blue-400 mr-2"></i> Dokumen Tercetak</h3>
            </div>
            <div class="p-6 p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item"><i class="fas fa-check text-emerald-600 mr-2"></i> Berita Acara Pembayaran (BAP)</li>
                    <li class="list-group-item"><i class="fas fa-check text-emerald-600 mr-2"></i> Kwitansi</li>
                    @if($payment->is_non_pkp)
                    <li class="list-group-item"><i class="fas fa-check text-emerald-600 mr-2"></i> Surat Pernyataan Non-PKP</li>
                    @endif
                    <li class="list-group-item"><i class="fas fa-check text-emerald-600 mr-2"></i> Ringkasan Kontrak</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kolom Preview (Kanan) -->
    <div class="col-md-9">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden shadow">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 bg-white p-6 border-bottom">
                <ul class="nav nav-tabs border-0" id="document-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-bold" id="tab-bap" data-toggle="tab" href="#content-bap" role="tab" style="padding: 12px 25px;"><i class="fas fa-file-signature mr-2"></i> BAP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-bold" id="tab-kwitansi" data-toggle="tab" href="#content-kwitansi" role="tab" style="padding: 12px 25px;"><i class="fas fa-file-invoice-dollar mr-2"></i> Kwitansi</a>
                    </li>
                    @if($payment->is_non_pkp)
                    <li class="nav-item">
                        <a class="nav-link font-bold" id="tab-non-pkp" data-toggle="tab" href="#content-non-pkp" role="tab" style="padding: 12px 25px;"><i class="fas fa-file-contract mr-2"></i> Surat Non-PKP</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link font-bold" id="tab-ringkasan" data-toggle="tab" href="#content-ringkasan" role="tab" style="padding: 12px 25px;"><i class="fas fa-list-alt mr-2"></i> Ringkasan Kontrak</a>
                    </li>
                </ul>
            </div>
            @php
                $skpd = \App\Models\Skpd::first();
            @endphp
            <div class="p-6 bg-light p-6" style="min-height: 100vh; overflow-x: auto;">
                <div class="tab-content" id="document-tabsContent">
                    {{-- Kertas 1: BAP --}}
                    <div class="tab-pane fade show active" id="content-bap" role="tabpanel">
                        <div class="document-paper mb-8">
                            @include('procurement-payments.partials.bap', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
                        </div>
                    </div>

                    {{-- Kertas 2: Kwitansi --}}
                    <div class="tab-pane fade" id="content-kwitansi" role="tabpanel">
                        <div class="document-paper mb-8">
                            @include('procurement-payments.partials.kwitansi', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
                        </div>
                    </div>

                    {{-- Kertas 3: Non PKP (Jika Ya) --}}
                    @if($payment->is_non_pkp)
                    <div class="tab-pane fade" id="content-non-pkp" role="tabpanel">
                        <div class="document-paper mb-8">
                            @include('procurement-payments.partials.non-pkp', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
                        </div>
                    </div>
                    @endif

                    {{-- Kertas 4: Ringkasan Kontrak --}}
                    <div class="tab-pane fade" id="content-ringkasan" role="tabpanel">
                        <div class="document-paper mb-8">
                            @include('procurement-payments.partials.ringkasan-kontrak', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
                        </div>
                    </div>
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
