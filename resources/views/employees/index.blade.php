@extends('adminlte::page')

@section('title', 'Master Pegawai')

@section('content_header')
    <h1>Master Pegawai</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header pt-3 pb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pegawai
                </a>
            </div>
            <form action="{{ route('employees.index') }}" method="GET" class="d-flex align-items-center">
                <input type="text" name="search" class="form-control" style="max-width: 300px; margin-right: 10px;" placeholder="Cari Nama / NIP..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Cari
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Golongan</th>
                            <th>Jabatan</th>
                            <th>Kategori Biaya</th>
                            <th>Tanggal Lahir</th>
                            <th style="width: 120px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td>{{ $emp->nama }}</td>
                                <td>{{ $emp->nip ?? '-' }}</td>
                                <td>{{ $emp->golongan ?? '-' }}</td>
                                <td>{{ $emp->jabatan ?? '-' }}</td>
                                <td>
                                    @if($emp->kategori_biaya)
                                        <span class="badge bg-info">{{ $emp->kategori_biaya }}</span>
                                    @else
                                        <span class="badge bg-secondary">Otomatis</span>
                                    @endif
                                </td>
                                <td>{{ $emp->tanggal_lahir ? $emp->tanggal_lahir->format('d-m-Y') : '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('employees.edit', $emp) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('employees.destroy', $emp) }}" method="POST" onsubmit="return confirm('Hapus pegawai ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Data pegawai belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($employees->hasPages())
            <div class="card-footer">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
@stop
