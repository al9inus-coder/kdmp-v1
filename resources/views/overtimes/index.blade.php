@extends('adminlte::page')

@section('title', 'Belanja Lembur - ' . $package->nama_paket)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0"><i class="fas fa-clock text-warning"></i> Modul Belanja Lembur</h1>
            <p class="text-muted mt-2 mb-0">Paket: {{ $package->nama_paket }} (Tahun: {{ $package->created_at ? $package->created_at->format('Y') : date('Y') }})</p>
        </div>
        <div>
            <a href="{{ route('packages.show', $package) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Paket
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Sukses!</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Pilih Bulan Rekapitulasi</h3>
        </div>
        <div class="card-body">
            <div class="row">
                @php
                    $months = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                        4 => 'April', 5 => 'Mei', 6 => 'Juni', 
                        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
                        10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                @endphp

                @foreach($months as $num => $name)
                    @php
                        // Check if overtime record exists for this month
                        $exists = \App\Models\Overtime::where('package_id', $package->id)->where('bulan', $num)->exists();
                    @endphp
                    <div class="col-md-3 col-sm-6 mb-4">
                        <a href="{{ route('packages.overtimes.show', [$package, $num]) }}" class="text-decoration-none text-dark">
                            <div class="card shadow-sm h-100 hover-card {{ $exists ? 'border-warning' : '' }}" style="transition: 0.3s; cursor: pointer;">
                                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center py-4">
                                    <h1 class="display-4 text-{{ $exists ? 'warning' : 'secondary' }} mb-2">
                                        <i class="fas {{ $exists ? 'fa-calendar-check' : 'fa-calendar-alt' }}"></i>
                                    </h1>
                                    <h5 class="font-weight-bold mb-0">{{ $name }}</h5>
                                    @if($exists)
                                        <span class="badge badge-warning mt-2">Data Tersedia</span>
                                    @else
                                        <span class="badge badge-secondary mt-2">Belum Diisi</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: #ffc107 !important;
    }
    .hover-card:hover h1 {
        color: #ffc107 !important;
    }
</style>
@stop
