@extends('adminlte::page')

@section('title', 'Edit SBU Uang Harian')

@section('content_header')
    <h1>Edit Standar Uang Harian</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sbu-uang-harians.update', $sbuUangHarian) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Provinsi</label>
                    <input type="text" name="provinsi" class="form-control @error('provinsi') is-invalid @enderror" value="{{ old('provinsi', $sbuUangHarian->provinsi) }}" required>
                    @error('provinsi')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" name="satuan" class="form-control @error('satuan') is-invalid @enderror" value="{{ old('satuan', $sbuUangHarian->satuan) }}" required>
                    @error('satuan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Uang Harian Luar Kota (Rp)</label>
                            <input type="number" name="luar_kota" class="form-control @error('luar_kota') is-invalid @enderror" value="{{ old('luar_kota', $sbuUangHarian->luar_kota) }}" min="0" required>
                            @error('luar_kota')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Uang Harian Diklat (Rp)</label>
                            <input type="number" name="diklat" class="form-control @error('diklat') is-invalid @enderror" value="{{ old('diklat', $sbuUangHarian->diklat) }}" min="0" required>
                            @error('diklat')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('sbu-uang-harians.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@stop
