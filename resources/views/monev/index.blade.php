@extends('adminlte::page')

@section('title', 'Monitoring & Evaluasi (Monev)')

@section('content_header')
    <h1>Monitoring & Evaluasi (Monev)</h1>
@stop

@section('content')
<style>
    .card-progress-wrapper {
        position: relative;
        padding: 4px;
        border-radius: 0.35rem;
        background: conic-gradient(
            var(--progress-color, #28a745) calc(var(--progress, 0) * 1%), 
            #e9ecef 0
        );
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        height: calc(100% - 1.5rem);
        display: flex;
        flex-direction: column;
    }
    .card-progress-wrapper::before {
        content: "";
        position: absolute;
        top: 4px; left: 4px; right: 4px; bottom: 4px;
        background-color: white;
        border-radius: calc(0.35rem - 2px);
        z-index: 0;
    }
    .card-progress-wrapper .card {
        margin-bottom: 0;
        height: 100%;
        border: none;
        border-radius: 0.25rem;
        z-index: 1;
        background: transparent;
    }
    .card-progress-wrapper .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
        color: #fff; /* Ensure text is readable on colored backgrounds */
    }
    .card-progress-wrapper .list-group-item {
        background: transparent;
    }
    .card-link-wrapper {
        text-decoration: none !important;
        color: inherit;
    }
    .card-progress-wrapper:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,.15) !important;
    }
</style>

<div class="container-fluid pb-4">
    @forelse($programs as $program)
        <h4 class="text-primary mt-4 mb-3 border-bottom pb-2">
            <i class="fas fa-project-diagram mr-2"></i>{{ $program->kode }} - {{ $program->nama }}
        </h4>

        @foreach($program->activities as $activity)
            @php
                // Generate a consistent color based on the Activity ID
                $bgColors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning text-dark', 'bg-danger', 'bg-secondary', 'bg-dark'];
                $headerColorClass = $bgColors[$activity->id % count($bgColors)];
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-3 mt-3 ml-2 mr-3">
                <h5 class="text-secondary mb-0">
                    <i class="fas fa-tasks mr-2"></i>Kegiatan: {{ $activity->kode }} - {{ $activity->nama }}
                </h5>
                <button type="button" class="btn btn-sm btn-outline-info font-weight-bold" onclick="printHidden('{{ route('control-cards.print', $activity) }}')">
                    <i class="fas fa-print mr-1"></i> Cetak Kendali
                </button>
            </div>
            
            <div class="row ml-1">
                @foreach($activity->subActivities as $subActivity)
                    @php
                        $totalPagu = 0;
                        $totalRealisasi = 0;
                        $paketSwakelola = 0;
                        $paketPenyedia = 0;

                        foreach($subActivity->packages as $pkg) {
                            $totalPagu += $pkg->pagu;
                            
                            if($pkg->procurementPackage && $pkg->procurementPackage->procurementProcess) {
                                $totalRealisasi += $pkg->procurementPackage->procurementProcess->nilai_kontrak;
                            }
                            
                            $jenis = strtolower(($pkg->jenis_pengadaan ?? '') . ' ' . ($pkg->metode_pengadaan ?? ''));
                            if(str_contains($jenis, 'swakelola')) {
                                $paketSwakelola++;
                            } else {
                                $paketPenyedia++;
                            }
                        }

                        $sisaPagu = $totalPagu - $totalRealisasi;
                        $progress = $totalPagu > 0 ? min(100, ($totalRealisasi / $totalPagu) * 100) : 0;
                        
                        $progressColor = '#dc3545';
                        if($progress >= 40) $progressColor = '#ffc107';
                        if($progress >= 75) $progressColor = '#28a745';
                        if($progress == 100) $progressColor = '#007bff';
                    @endphp

                    <div class="col-md-6 col-lg-4 col-xl-3 d-flex align-items-stretch">
                        <a href="{{ route('monev.show', $subActivity->id) }}" class="card-link-wrapper w-100">
                            <div class="card-progress-wrapper shadow-sm" style="--progress: {{ $progress }}; --progress-color: {{ $progressColor }};">
                                <div class="card">
                                    <div class="card-header {{ $headerColorClass }}">
                                        <h3 class="card-title text-truncate w-100" title="{{ $subActivity->kode }} - {{ $subActivity->nama }}">
                                            <b>{{ $subActivity->kode }}</b><br>
                                            <small class="text-wrap" style="line-height: 1.2;">{{ $subActivity->nama }}</small>
                                        </h3>
                                    </div>
                                    <div class="card-body bg-white">
                                        <ul class="list-group list-group-flush mb-3 small">
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span>Total Pagu</span>
                                            <span class="badge bg-primary rounded-pill">Rp {{ number_format($totalPagu, 0, ',', '.') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span>Realisasi</span>
                                            <span class="badge bg-success rounded-pill">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <span>Sisa Dana</span>
                                            <span class="badge bg-danger rounded-pill">Rp {{ number_format($sisaPagu, 0, ',', '.') }}</span>
                                        </li>
                                    </ul>
                                    
                                    <div class="d-flex justify-content-between mb-3 text-muted small">
                                        <span><i class="fas fa-users text-info"></i> Penyedia: <b>{{ $paketPenyedia }}</b></span>
                                        <span><i class="fas fa-people-carry text-warning"></i> Swakelola: <b>{{ $paketSwakelola }}</b></span>
                                    </div>
                                    
                                    <div class="progress mb-1" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%; background-color: {{ $progressColor }}" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="text-center font-weight-bold" style="color: {{ $progressColor }}; font-size: 0.85rem;">
                                        {{ number_format($progress, 1, ',', '.') }}% Terserap
                                    </div>
                                </div>
                                <div class="card-footer text-center bg-light border-0 pt-2 pb-2">
                                    <span class="text-primary small font-weight-bold">
                                        Klik untuk Detail <i class="fas fa-arrow-right ml-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endforeach
    @empty
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Belum ada data Program, Kegiatan, dan Sub Kegiatan yang dapat dimonitor.
        </div>
    @endforelse
</div>

@push('js')
<script>
    function printHidden(url) {
        var oldIframe = document.getElementById('hidden-print-iframe');
        if (oldIframe) {
            oldIframe.remove();
        }

        var iframe = document.createElement('iframe');
        iframe.id = 'hidden-print-iframe';
        // Use visibility hidden instead of display none to ensure browser renders it for printing
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        iframe.style.visibility = 'hidden';
        
        document.body.appendChild(iframe);
        
        // The loaded page already has window.onload = function() { window.print(); }
        iframe.src = url;
    }
</script>
@endpush
@stop
