@extends('adminlte::page')

@section('title', 'Edit SKPD')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1>Edit SKPD: {{ $skpd->kode }}</h1>
        <a href="{{ route('skpds.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    </div>
@stop

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('skpds.update', $skpd) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Informasi SKPD -->
        <div class="col-md-6">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-building mr-2"></i>Informasi Perangkat Daerah</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Kode SKPD <span class="text-danger">*</span></label>
                        <input type="text" name="kode" class="form-control" value="{{ old('kode', $skpd->kode) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Nama SKPD <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $skpd->nama) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Singkatan Nama</label>
                        <input type="text" name="singkatan" class="form-control" value="{{ old('singkatan', $skpd->singkatan) }}">
                    </div>
                    <div class="form-group">
                        <label>NPWP Dinas</label>
                        <input type="text" name="npwp_dinas" class="form-control" value="{{ old('npwp_dinas', $skpd->npwp_dinas) }}">
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $skpd->alamat) }}</textarea>
                    </div>
                </div>
            </div>
            
            <!-- PA / Kadis -->
            <div class="card card-success card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user-tie mr-2"></i>Pengguna Anggaran (PA) / Kepala Dinas</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Kepala Perangkat Daerah</label>
                        <input type="text" name="kepala_skpd" class="form-control" value="{{ old('kepala_skpd', $skpd->kepala_skpd) }}">
                    </div>
                    <div class="form-group">
                        <label>NIP Kepala Perangkat Daerah</label>
                        <input type="text" name="nip_kepala" class="form-control" value="{{ old('nip_kepala', $skpd->nip_kepala) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- PPK -->
            <div class="card card-warning card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user-shield mr-2"></i>Pejabat Pembuat Komitmen (PPK)</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama PPK</label>
                        <input type="text" name="nama_ppk" class="form-control" value="{{ old('nama_ppk', $skpd->nama_ppk) }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP PPK</label>
                                <input type="text" name="nip_ppk" class="form-control" value="{{ old('nip_ppk', $skpd->nip_ppk) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pangkat / Golongan</label>
                                <input type="text" name="pangkat_ppk" class="form-control" value="{{ old('pangkat_ppk', $skpd->pangkat_ppk) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>No. Telpon</label>
                                <input type="text" name="telepon_ppk" class="form-control" value="{{ old('telepon_ppk', $skpd->telepon_ppk) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email_ppk" class="form-control" value="{{ old('email_ppk', $skpd->email_ppk) }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Username PPK (SPSE/LPSE)</label>
                        <input type="text" name="username_ppk" class="form-control" value="{{ old('username_ppk', $skpd->username_ppk) }}">
                    </div>
                </div>
            </div>

            <!-- PPTK -->
            <div class="card card-info card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-user-edit mr-2"></i>Pejabat Pelaksana Teknis Kegiatan (PPTK)</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama PPTK</label>
                        <input type="text" name="nama_pptk" class="form-control" value="{{ old('nama_pptk', $skpd->nama_pptk) }}">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>NIP PPTK</label>
                                <input type="text" name="nip_pptk" class="form-control" value="{{ old('nip_pptk', $skpd->nip_pptk) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Pangkat / Golongan</label>
                                <input type="text" name="pangkat_pptk" class="form-control" value="{{ old('pangkat_pptk', $skpd->pangkat_pptk) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mb-4">
                <button type="submit" class="btn btn-primary btn-lg px-5"><i class="fas fa-save mr-2"></i> Update Data SKPD</button>
            </div>
        </div>
    </div>
</form>

@stop