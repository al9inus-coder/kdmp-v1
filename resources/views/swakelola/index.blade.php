@extends('adminlte::page')

@section('title', 'Daftar Swakelola')

@section('content_header')
    <h1>
        Daftar Swakelola
        <small class="text-muted">
            ({{ $packages->total() }} Data)
        </small>
    </h1>
@stop

@section('content')
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <form action="{{ route('swakelola.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-10 mb-2 mb-md-0">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama Paket atau ID RUP..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 10%;">ID RUP</th>
                            <th>Nama Paket</th>
                            <th>Sub Kegiatan</th>
                            <th class="text-end text-nowrap">Pagu</th>
                            <th class="text-nowrap">Metode</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th style="width: 90px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td class="text-center">{{ $package->id_rup ?? '-' }}</td>
                                <td class="fw-bold text-wrap">{{ $package->nama_paket }}</td>
                                <td class="text-nowrap">{{ $package->subActivity->nama ?? '-' }}</td>
                                <td class="text-end text-nowrap fw-bold text-success">
                                    Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}
                                </td>
                                <td class="text-nowrap">{{ $package->metode_pengadaan ?? '-' }}</td>
                                <td class="text-center">
                                    @if($package->status === 'needs_review')
                                        <span class="badge bg-danger">Needs Review</span>
                                    @elseif($package->status === 'draft')
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @elseif($package->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $package->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('packages.show', $package) }}" class="btn btn-sm btn-info text-white" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    Data swakelola belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($packages->hasPages())
            <div class="card-footer d-flex justify-content-center pt-4 pb-3">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
@stop
