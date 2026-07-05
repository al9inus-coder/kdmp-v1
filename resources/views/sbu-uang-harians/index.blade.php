@extends('adminlte::page')

@section('title', 'Master SBU Uang Harian')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>SBU Uang Harian (Luar Daerah)</h1>
        <a href="{{ route('sbu-uang-harians.create') }}" class="btn btn-primary">Tambah Standar Biaya</a>
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
                            <th>Provinsi</th>
                            <th>Satuan</th>
                            <th>Uang Harian Luar Kota (Rp)</th>
                            <th>Uang Harian Diklat (Rp)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->provinsi }}</td>
                                <td>{{ $rate->satuan }}</td>
                                <td>Rp {{ number_format($rate->luar_kota, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($rate->diklat, 0, ',', '.') }}</td>
                                <td>
                                    <a href="{{ route('sbu-uang-harians.edit', $rate) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('sbu-uang-harians.destroy', $rate) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada data Standar Uang Harian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="card-footer">
                    {{ $rates->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
