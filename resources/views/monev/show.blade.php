@extends('adminlte::page')

@section('title', 'Detail Monev - ' . $subActivity->kode)

@section('content_header')
    <div class="mb-2">
        <h1>Detail Monitoring: {{ $subActivity->kode }}</h1>
        <p class="text-muted mt-2 mb-0">{{ $subActivity->nama }}</p>
        <p class="text-muted small">Program: {{ $subActivity->activity->program->nama ?? '-' }} | Kegiatan: {{ $subActivity->activity->nama ?? '-' }}</p>
    </div>
@stop

@section('content')
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
@endphp

<div class="row">
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box bg-primary">
        <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Pagu</span>
          <span class="info-box-number">Rp {{ number_format($totalPagu, 0, ',', '.') }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box bg-success">
        <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Realisasi</span>
          <span class="info-box-number">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box bg-danger">
        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Sisa Dana</span>
          <span class="info-box-number">Rp {{ number_format($sisaPagu, 0, ',', '.') }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box bg-info">
        <span class="info-box-icon"><i class="fas fa-chart-pie"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Serapan Anggaran</span>
          <span class="info-box-number">{{ number_format($progress, 2, ',', '.') }}%</span>
          <div class="progress">
            <div class="progress-bar" style="width: {{ $progress }}%"></div>
          </div>
        </div>
      </div>
    </div>
</div>

<div class="card mt-2">
    <div class="card-header">
        <h3 class="card-title mb-0"><i class="fas fa-list-alt mr-2"></i> Kartu Kendali Kegiatan</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">Kode Rekening</th>
                        <th class="text-left" style="width: 30%;">Uraian Belanja</th>
                        <th class="text-end" style="width: 15%;">Pagu (Rp)</th>
                        <th class="text-end" style="width: 15%;">Realisasi (Rp)</th>
                        <th class="text-end" style="width: 10%;">Sisa (Rp)</th>
                        <th style="width: 10%;">Serapan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedPackages = $subActivity->packages->groupBy(function($pkg) {
                            return $pkg->account ? $pkg->account->id : 'none';
                        });
                        $no = 1;
                    @endphp
                    @forelse($groupedPackages as $accountId => $packages)
                        @php
                            $account = $packages->first()->account;
                            $groupPagu = $packages->sum('pagu');
                            $groupRealisasi = 0;
                            foreach($packages as $p) {
                                if($p->procurementPackage && $p->procurementPackage->procurementProcess) {
                                    $groupRealisasi += $p->procurementPackage->procurementProcess->nilai_kontrak;
                                }
                            }
                            $groupSisa = $groupPagu - $groupRealisasi;
                            $groupPersen = $groupPagu > 0 ? ($groupRealisasi / $groupPagu) * 100 : 0;
                        @endphp
                        
                        <!-- Account Group Row -->
                        <tr class="table-light font-weight-bold">
                            <td class="text-center">{{ $no++ }}</td>
                            <td class="text-center">{{ $account->kode ?? '-' }}</td>
                            <td>{{ $account->nama ?? 'Tanpa Uraian Belanja' }}</td>
                            <td class="text-end text-primary">Rp {{ number_format($groupPagu, 0, ',', '.') }}</td>
                            <td class="text-end"></td>
                            <td class="text-end text-danger">Rp {{ number_format($groupSisa, 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($groupPersen, 1, ',', '.') }}%</td>
                        </tr>
                        
                        <!-- Package Rows -->
                        @foreach($packages as $pkg)
                            @php
                                $pkgRealisasi = 0;
                                if($pkg->procurementPackage && $pkg->procurementPackage->procurementProcess) {
                                    $pkgRealisasi = $pkg->procurementPackage->procurementProcess->nilai_kontrak;
                                }
                                $pkgSisa = $pkg->pagu - $pkgRealisasi;
                                $pkgPersen = $pkg->pagu > 0 ? ($pkgRealisasi / $pkg->pagu) * 100 : 0;
                            @endphp
                            <tr>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="pl-4">
                                    <i class="fas fa-angle-right text-muted mr-2"></i> {{ $pkg->nama_paket }}
                                    <span class="badge bg-secondary ml-1">{{ $pkg->jenis_pengadaan }}</span>
                                </td>
                                <td class="text-end text-muted"></td>
                                <td class="text-end text-success">Rp {{ number_format($pkgRealisasi, 0, ',', '.') }}</td>
                                <td class="text-end text-muted"></td>
                                <td class="text-center"></td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada paket pekerjaan di sub kegiatan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mb-5 mt-3">
    <a href="{{ route('monev.index') }}" class="btn btn-secondary px-4">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
    <button type="button" class="btn btn-info font-weight-bold px-4" onclick="printHidden('{{ route('monev.print', $subActivity) }}')">
        <i class="fas fa-print mr-2"></i> Cetak Kartu Kendali
    </button>
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
        iframe.style.position = 'absolute';
        iframe.style.width = '0';
        iframe.style.height = '0';
        iframe.style.border = 'none';
        iframe.style.visibility = 'hidden';
        
        document.body.appendChild(iframe);
        
        iframe.src = url;
    }
</script>
@endpush
@stop
