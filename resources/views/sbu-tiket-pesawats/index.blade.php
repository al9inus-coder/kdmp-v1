@extends('adminlte::page')

@section('title', 'Master SBU Tiket Pesawat')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>SBU Tiket Pesawat</h1>
        <a href="{{ route('sbu-tiket-pesawats.create') }}" class="btn btn-primary">Tambah Standar Biaya</a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Tujuan</th>
                            <th>Satuan</th>
                            <th>Bisnis (Rp)</th>
                            <th>Ekonomi (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->tujuan }}</td>
                                <td>{{ $rate->satuan }}</td>
                                <td>Rp {{ number_format($rate->bisnis, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($rate->ekonomi, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('sbu-tiket-pesawats.edit', $rate) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('sbu-tiket-pesawats.destroy', $rate) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data Standar Tiket Pesawat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="card-footer">
                </div>
            </div>
        </div>
    </div>
@stop
