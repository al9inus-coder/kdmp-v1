@extends('adminlte::page')

@section('title', 'Edit Biaya Transportasi')

@section('content_header')
    <h1>Edit Biaya Transportasi</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sbu-transport-rates.update', $sbuTransportRate) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Tempat Kedudukan</label>
                    <input type="text" name="tempat_kedudukan" class="form-control @error('tempat_kedudukan') is-invalid @enderror" value="{{ old('tempat_kedudukan', $sbuTransportRate->tempat_kedudukan) }}" required>
                    @error('tempat_kedudukan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Tempat Tujuan</label>
                    <input type="text" name="tempat_tujuan" class="form-control @error('tempat_tujuan') is-invalid @enderror" value="{{ old('tempat_tujuan', $sbuTransportRate->tempat_tujuan) }}" required>
                    @error('tempat_tujuan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" name="satuan" class="form-control @error('satuan') is-invalid @enderror" value="{{ old('satuan', $sbuTransportRate->satuan) }}" required>
                    @error('satuan')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Biaya Mobil (Rp)</label>
                            <input type="number" name="biaya_mobil" class="form-control @error('biaya_mobil') is-invalid @enderror" value="{{ old('biaya_mobil', $sbuTransportRate->biaya_mobil) }}" min="0" required>
                            @error('biaya_mobil')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Biaya Sepeda Motor (Rp)</label>
                            <input type="number" name="biaya_motor" class="form-control @error('biaya_motor') is-invalid @enderror" value="{{ old('biaya_motor', $sbuTransportRate->biaya_motor) }}" min="0" required>
                            @error('biaya_motor')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('sbu-transport-rates.index') }}" class="btn btn-default">Batal</a>
            </form>
        </div>
    </div>
@stop
