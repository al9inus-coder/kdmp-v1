@extends('adminlte::page')

@section('title', 'Proses Pengadaan')

@section('content_header')
    <h1 class="mb-1 text-dark font-weight-bold">
        <i class="fas fa-file-signature text-primary mr-2"></i> Proses Pengadaan (Surat Pesanan)
    </h1>
@stop

@section('content')
    @include('components.workflow-progress', ['procurementPackage' => $procurementPackage])

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-check mr-1"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Terjadi Kesalahan!</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $isComplete = $process->nomor_surat_pesanan && $process->nama_penyedia && $process->tanggal_surat_pesanan;
    @endphp    <form action="{{ route('procurement-packages.procurement-process.update', $procurementPackage->package) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-md-8">
                {{-- CARD A: Data Surat Pesanan --}}
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-file-alt text-primary mr-2"></i> Formulir Surat Pesanan
                        </h3>
                    </div>
                    <div class="card-body">
                        <h5 class="text-muted border-bottom pb-2 mb-3">A. Data Surat Pesanan</h5>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nomor Surat Pesanan <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_surat_pesanan" class="form-control" value="{{ old('nomor_surat_pesanan', $process->nomor_surat_pesanan) }}" required placeholder="Contoh: 027/01/SP/2026">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tanggal Surat Pesanan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_surat_pesanan" class="form-control" value="{{ old('tanggal_surat_pesanan', optional($process->tanggal_surat_pesanan)->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nilai Kontrak (Rp) <span class="text-danger">*</span></label>
                                <input type="text" name="nilai_kontrak" class="form-control currency-input" value="{{ number_format((float) old('nilai_kontrak', $process->nilai_kontrak ?? 0), 0, ',', '.') }}" required placeholder="0">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Tanggal Barang Diterima <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_barang_diterima" class="form-control" value="{{ old('tanggal_barang_diterima', optional($process->tanggal_barang_diterima)->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label>Catatan Tambahan</label>
                            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan operasional (Opsional)">{{ old('catatan', $process->catatan) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- CARD B: Data Penyedia --}}
                <div class="card card-outline card-secondary shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="text-muted border-bottom pb-2 mb-3">B. Data Penyedia</h5>
                        <div class="form-group">
                            <label>Nama Penyedia <span class="text-danger">*</span></label>
                            <input type="text" name="nama_penyedia" class="form-control" value="{{ old('nama_penyedia', $process->nama_penyedia ?? $procurementPackage->procurementRequest?->nama_penyedia) }}" required placeholder="Contoh: PT. Maju Bersama">
                        </div>
                        
                        <div class="form-group">
                            <label>Alamat Penyedia <span class="text-danger">*</span></label>
                            <textarea name="alamat_penyedia" class="form-control" rows="3" required placeholder="Alamat lengkap penyedia">{{ old('alamat_penyedia', $process->alamat_penyedia) }}</textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>NPWP Penyedia <span class="text-danger">*</span></label>
                            <input type="text" name="npwp_penyedia" class="form-control" value="{{ old('npwp_penyedia', $process->npwp_penyedia) }}" required placeholder="Nomor Pokok Wajib Pajak">
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nama PIC (Wakil Sah) <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pic" class="form-control" value="{{ old('nama_pic', $process->nama_pic) }}" required placeholder="Nama lengkap wakil sah penyedia">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Jabatan PIC <span class="text-danger">*</span></label>
                                <input type="text" name="jabatan_pic" class="form-control" value="{{ old('jabatan_pic', $process->jabatan_pic) }}" required placeholder="Contoh: Direktur Utama">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Nama Bank <span class="text-danger">*</span></label>
                                <input type="text" name="nama_bank" class="form-control" value="{{ old('nama_bank', $process->nama_bank) }}" required placeholder="Contoh: Bank Kalbar">
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Nomor Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_rekening" class="form-control" value="{{ old('nomor_rekening', $process->nomor_rekening) }}" required placeholder="Nomor Rekening Penyedia">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-right">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan Data
                        </button>
                    </div>
                </div>
                
                <a href="{{ route('procurement-packages.show', $procurementPackage->package) }}" class="btn btn-default mb-4">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Paket
                </a>
            </div>

            <div class="col-md-4">
                <div class="sticky-top" style="top: 20px;">
                    <div class="card card-outline card-success shadow-sm mb-4">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-print text-success mr-2"></i> Cetak Dokumen
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> Tombol cetak akan aktif setelah Data Surat Pesanan lengkap tersimpan.
                            </div>

                            <a href="{{ $isComplete ? route('procurement-packages.procurement-process.preview-document', $procurementPackage->package) : '#' }}" 
                               class="btn btn-primary btn-block mb-3 {{ !$isComplete ? 'disabled' : '' }}">
                                <i class="fas fa-file-alt mr-2"></i> Buat SSKK & SSUK
                            </a>
                        </div>
                    </div>

                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-lightbulb text-info mr-2"></i> Petunjuk Pengisian
                            </h3>
                        </div>
                        <div class="card-body">
                            <ul class="pl-3 mb-0 text-muted" style="font-size: 0.9rem;">
                                <li class="mb-2">Nomor surat pesanan dapat disesuaikan dengan nomor surat pesanan pada sistem e-Katalog.</li>
                                <li class="mb-2"><strong>Waktu Pelaksanaan</strong> dihitung otomatis berdasarkan rentang Tanggal Surat Pesanan hingga Tanggal Barang Diterima.</li>
                                <li class="mb-2">Pastikan <strong>Nama Bank</strong> dan <strong>Nomor Rekening</strong> penyedia sesuai dengan dokumen tagihan pembayaran.</li>
                                <li class="mb-3"><strong>NPWP Penyedia</strong> adalah NPWP badan usaha apabila statusnya badan usaha, bukan NPWP Direktur. Apabila penyedia perorangan, gunakan NPWP Pribadi.</li>
                                <li class="text-danger font-weight-bold" style="list-style-type: none; margin-left: -1rem;">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> PERINGATAN: Harap cek dan pastikan seluruh data yang diinput sudah benar satu persatu sebelum beralih ke tahap selanjutnya.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop
