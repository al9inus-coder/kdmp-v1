@extends('adminlte::page')

@section('title', 'Standar Biaya Umum - Transportasi')

@section('content_header')
    <h1>Standar Biaya Umum - Transportasi</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <a href="{{ route('sbu-transport-rates.create') }}" class="btn btn-primary">Tambah Biaya Transportasi</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tempat Kedudukan</th>
                            <th>Tempat Tujuan</th>
                            <th>Satuan</th>
                            <th>Biaya Mobil (Rp)</th>
                            <th>Biaya Motor (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->tempat_kedudukan }}</td>
                                <td>{{ $rate->tempat_tujuan }}</td>
                                <td>{{ $rate->satuan }}</td>
                                <td>Rp {{ number_format($rate->biaya_mobil, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($rate->biaya_motor, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('sbu-transport-rates.edit', $rate) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('sbu-transport-rates.destroy', $rate) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada data Standar Biaya Transportasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $rates->links() }}
        </div>
    </div>
@stop
