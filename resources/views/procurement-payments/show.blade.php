@extends('adminlte::page')

@section('title', 'Tahap Pembayaran')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="font-weight-bold text-dark">
            <i class="fas fa-money-check-alt text-success mr-2"></i> Tahap Pembayaran
        </h1>
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole(['Admin', 'Super Admin']) ? route('admin.packages.index') : route('kabid.penyedia.index') }}">Daftar Paket</a></li>
            <li class="breadcrumb-item"><a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.show', $procurementPackage->package) }}">{{ $procurementPackage->package->id_rup }}</a></li>
            <li class="breadcrumb-item active">Pembayaran</li>
        </ol>
    </div>
@stop

@section('content')

    {{-- Progress Workflow --}}
    @include('components.workflow-progress', ['procurementPackage' => $procurementPackage])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Detail Pembayaran Kiri -->
        <div class="col-md-6">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-file-invoice-dollar text-success mr-2"></i> Data Penagihan</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 40%">Nama Paket</th>
                                <td>{{ $procurementPackage->package->nama_paket ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Nilai Tagihan (Sesuai Kontrak)</th>
                                <td class="font-weight-bold text-success">Rp {{ number_format($process->nilai_kontrak, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>No. dan Tanggal BAST</th>
                                <td>{{ $payment->nomor_bast ?? '-' }} tanggal {{ optional($payment->tanggal_bast)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>No. dan Tanggal Invoice</th>
                                <td>{{ $payment->nomor_invoice ?? '-' }} tanggal {{ optional($payment->tanggal_invoice)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Data PPTK</th>
                                <td>
                                    <strong>{{ $payment->nama_pptk ?? '-' }}</strong><br>
                                    <small class="text-muted">NIP: {{ $payment->nip_pptk ?? '-' }}</small><br>
                                    <small class="text-muted">Pangkat/Gol: {{ $payment->pangkat_golongan_pptk ?? '-' }}</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detail Pembayaran Kanan -->
        <div class="col-md-6">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-file-signature text-success mr-2"></i> Data Dokumen Pembayaran</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            <tr>
                                <th style="width: 40%">No. dan Tanggal BAP</th>
                                <td>{{ $payment->nomor_bap ?? '-' }}/BAP/{{ $procurementPackage->package->program->kode ?? '2.11.04' }}/PERKIMPLH-C tanggal {{ optional($payment->tanggal_bap)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>No. dan Tanggal Kwitansi</th>
                                <td>{{ $payment->nomor_kwitansi ?? '-' }}/KWT/{{ $procurementPackage->package->program->kode ?? '2.11.04' }}/PERKIMPLH-C tanggal {{ optional($payment->tanggal_kwitansi)->translatedFormat('d F Y') }}</td>
                            </tr>
                            <tr>
                                <th>Status Non-PKP</th>
                                <td>
                                    @if($payment->is_non_pkp)
                                        <span class="badge badge-warning">Ya, dilampirkan</span><br>
                                        <small class="text-muted">Tanggal: {{ optional($payment->tanggal_non_pkp)->translatedFormat('d F Y') }}</small>
                                    @else
                                        <span class="badge badge-secondary">Tidak / PKP</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tgl Ringkasan Kontrak</th>
                                <td>{{ optional($payment->tanggal_ringkasan_kontrak)->translatedFormat('d F Y') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <div class="card shadow-sm">
                <div class="card-body py-4 bg-light">
                    <h5 class="font-weight-bold mb-3">Dokumen Pembayaran Siap Dicetak</h5>
                    <p class="text-muted mb-4">Pastikan data di atas sudah benar. Jika ada kesalahan, silakan hubungi administrator.</p>
                    
                    <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.preview-document', $procurementPackage->package) }}" class="btn btn-lg btn-success px-5 rounded-pill shadow-sm">
                        <i class="fas fa-print mr-2"></i> Pratinjau &amp; Cetak Dokumen Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.execution.show', $procurementPackage->package) }}?action=edit-payment" class="btn btn-default mb-4">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Tahap Pelaksanaan
    </a>
@stop
