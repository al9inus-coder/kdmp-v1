@extends('adminlte::page')

@section('title', 'Paket Pengadaan')

@section('content_header')
    <h1>
        Daftar Paket Pengadaan
        <small class="text-muted">
            ({{ $procurementPackages->total() }} Data)
        </small>
    </h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>ID RUP</th>
                            <th>Nama Paket</th>
                            <th>Program</th>
                            <th style="width: 180px;">Pagu</th>
                            <th style="width: 130px;">Status</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($procurementPackages as $procurementPackage)
                            <tr>
                                <td>{{ $procurementPackage->id }}</td>
                                <td>{{ $procurementPackage->package->id_rup ?? '-' }}</td>
                                <td>{{ $procurementPackage->package->nama_paket }}</td>
                                <td>
                                    {{ $procurementPackage->package->program?->kode }}
                                    {{ $procurementPackage->package->program ? '- '.$procurementPackage->package->program->nama : '' }}
                                </td>
                                <td>Rp {{ number_format((float) $procurementPackage->package->pagu, 0, ',', '.') }}</td>
                                <td>
                                    @if($procurementPackage->status === 'complete')
                                        <span class="badge badge-success">Complete</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('procurement-packages.show', $procurementPackage->package) }}"
                                       class="btn btn-sm btn-info">
                                        Masuk
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    Belum ada Paket Pengadaan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($procurementPackages->hasPages())
            <div class="card-footer clearfix">
                {{ $procurementPackages->links() }}
            </div>
        @endif
    </div>
@stop
