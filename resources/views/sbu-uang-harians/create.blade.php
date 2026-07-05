@extends('adminlte::page')

@section('title', 'Tambah SBU Uang Harian')

@section('content_header')
    <h1>Tambah Standar Uang Harian (Luar Daerah)</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sbu-uang-harians.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Provinsi</label>
                    <input type="text" name="provinsi" class="form-control @error('provinsi') is-invalid @enderror" value="{{ old('provinsi') }}" placeholder="Contoh: DKI Jakarta" required>
                    @error('provinsi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" name="satuan" class="form-control @error('satuan') is-invalid @enderror" value="{{ old('satuan', 'OH') }}" required>
                    @error('satuan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Uang Harian Luar Kota (Rp)</label>
                            <input type="number" name="luar_kota" class="form-control @error('luar_kota') is-invalid @enderror" value="{{ old('luar_kota', 0) }}" min="0" required>
                            @error('luar_kota')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Uang Harian Diklat (Rp)</label>
                            <input type="number" name="diklat" class="form-control @error('diklat') is-invalid @enderror" value="{{ old('diklat', 0) }}" min="0" required>
                            @error('diklat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('sbu-uang-harians.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@stop
