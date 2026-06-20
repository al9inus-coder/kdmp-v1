@extends('adminlte::page')

@section('title', 'Preview Dokumen Pembayaran')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="font-weight-bold text-dark">
            <i class="fas fa-file-pdf text-danger mr-2"></i> Preview Dokumen Pembayaran
        </h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('procurement-packages.index') }}">Daftar Paket</a></li>
            <li class="breadcrumb-item"><a href="{{ route('procurement-payments.show', $procurementPackage->package) }}">{{ $procurementPackage->package->id_rup }}</a></li>
            <li class="breadcrumb-item active">Preview Dokumen</li>
        </ol>
    </div>
@stop

@section('content')
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
<div class="row">
    <!-- Kolom Aksi (Kiri) -->
    <div class="col-md-3">
        <div class="card card-outline card-primary sticky-top" style="top: 20px;">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Aksi Dokumen</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-4">Pastikan tampilan dokumen di sebelah kanan sudah sesuai sebelum Anda mencetaknya.</p>
                <button onclick="printPdf('{{ route('procurement-payments.print-document', ['package' => $procurementPackage->package, 'type' => 'bap']) }}')" class="btn btn-outline-danger btn-block mb-2 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak BAP
                </button>
                <button onclick="printPdf('{{ route('procurement-payments.print-document', ['package' => $procurementPackage->package, 'type' => 'kwitansi']) }}')" class="btn btn-outline-danger btn-block mb-2 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak Kwitansi
                </button>
                @if($payment->is_non_pkp)
                <button onclick="printPdf('{{ route('procurement-payments.print-document', ['package' => $procurementPackage->package, 'type' => 'non-pkp']) }}')" class="btn btn-outline-danger btn-block mb-2 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak Non-PKP
                </button>
                @endif
                <button onclick="printPdf('{{ route('procurement-payments.print-document', ['package' => $procurementPackage->package, 'type' => 'ringkasan-kontrak']) }}')" class="btn btn-outline-danger btn-block mb-3 text-left">
                    <i class="fas fa-print mr-2"></i> Cetak Ringkasan Kontrak
                </button>
                <a href="{{ route('procurement-payments.show', $procurementPackage->package) }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Pembayaran
                </a>
                
                <hr class="mt-4 mb-4">
                
                <form action="{{ route('procurement-payments.complete', $procurementPackage->package) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-block btn-lg shadow-sm" onclick="return confirm('Apakah Anda yakin semua dokumen sudah dicetak dan proses pengadaan telah selesai?')">
                        <i class="fas fa-check-circle mr-2"></i> Selesaikan Proses
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card card-outline card-info mt-3">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle text-info mr-2"></i> Dokumen Tercetak</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i> Berita Acara Pembayaran (BAP)</li>
                    <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i> Kwitansi</li>
                    @if($payment->is_non_pkp)
                    <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i> Surat Pernyataan Non-PKP</li>
                    @endif
                    <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i> Ringkasan Kontrak</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Kolom Preview (Kanan) -->
    <div class="col-md-9">
        <div class="card shadow">
            <div class="card-header bg-white p-3 border-bottom">
                <ul class="nav nav-tabs border-0" id="document-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="tab-bap" data-toggle="tab" href="#content-bap" role="tab" style="padding: 12px 25px;"><i class="fas fa-file-signature mr-2"></i> BAP</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-kwitansi" data-toggle="tab" href="#content-kwitansi" role="tab" style="padding: 12px 25px;"><i class="fas fa-file-invoice-dollar mr-2"></i> Kwitansi</a>
                    </li>
                    @if($payment->is_non_pkp)
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-non-pkp" data-toggle="tab" href="#content-non-pkp" role="tab" style="padding: 12px 25px;"><i class="fas fa-file-contract mr-2"></i> Surat Non-PKP</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="tab-ringkasan" data-toggle="tab" href="#content-ringkasan" role="tab" style="padding: 12px 25px;"><i class="fas fa-list-alt mr-2"></i> Ringkasan Kontrak</a>
                    </li>
                </ul>
            </div>
            @php
                $skpd = \App\Models\Skpd::first();
            @endphp
            <div class="card-body bg-light p-4" style="min-height: 100vh; overflow-x: auto;">
                <div class="tab-content" id="document-tabsContent">
                    {{-- Kertas 1: BAP --}}
                    <div class="tab-pane fade show active" id="content-bap" role="tabpanel">
                        <div class="document-paper mb-5">
                            @include('procurement-payments.partials.bap', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
                        </div>
                    </div>

                    {{-- Kertas 2: Kwitansi --}}
                    <div class="tab-pane fade" id="content-kwitansi" role="tabpanel">
                        <div class="document-paper mb-5">
                            @include('procurement-payments.partials.kwitansi', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
                        </div>
                    </div>

                    {{-- Kertas 3: Non PKP (Jika Ya) --}}
                    @if($payment->is_non_pkp)
                    <div class="tab-pane fade" id="content-non-pkp" role="tabpanel">
                        <div class="document-paper mb-5">
                            @include('procurement-payments.partials.non-pkp', ['procurementPackage' => $procurementPackage, 'process' => $process, 'payment' => $payment, 'skpd' => $skpd])
                        </div>
                    </div>
                    @endif

                    {{-- Kertas 4: Ringkasan Kontrak --}}
                    <div class="tab-pane fade" id="content-ringkasan" role="tabpanel">
                        <div class="document-paper mb-5">
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

@stop

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
