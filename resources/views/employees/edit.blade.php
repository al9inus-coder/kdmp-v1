@extends('adminlte::page')

@section('title', 'Edit Pegawai')

@section('content_header')
    <h1>Edit Pegawai</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('employees.update', $employee) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label>Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $employee->nama) }}" required>
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label>NIP</label>
                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $employee->nip) }}">
                    @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label>Golongan</label>
                    <input type="text" name="golongan" class="form-control @error('golongan') is-invalid @enderror" value="{{ old('golongan', $employee->golongan) }}">
                    @error('golongan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label>Jabatan</label>
                    <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $employee->jabatan) }}">
                    @error('jabatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $employee->tanggal_lahir ? $employee->tanggal_lahir->format('Y-m-d') : '') }}">
                    @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label>Kategori Biaya Perjalanan (SBU)</label>
                    <select name="kategori_biaya" class="form-control @error('kategori_biaya') is-invalid @enderror">
                        <option value="">-- Otomatis berdasarkan Jabatan/Golongan --</option>
                        <option value="Eselon II" {{ old('kategori_biaya', $employee->kategori_biaya) == 'Eselon II' ? 'selected' : '' }}>Eselon II</option>
                        <option value="Eselon III, Gol. IV dan Jafung Madya" {{ old('kategori_biaya', $employee->kategori_biaya) == 'Eselon III, Gol. IV dan Jafung Madya' ? 'selected' : '' }}>Eselon III, Gol. IV dan Jafung Madya</option>
                        <option value="Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN" {{ old('kategori_biaya', $employee->kategori_biaya) == 'Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN' ? 'selected' : '' }}>Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN</option>
                    </select>
                    @error('kategori_biaya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@stop
