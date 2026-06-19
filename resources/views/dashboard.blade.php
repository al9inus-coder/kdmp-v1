@extends('adminlte::page')

@section('title', 'Beranda KDMP')

@section('content_header')
    <div class="d-flex justify-content-between align-items-end mb-2">
        <div>
            <h2 class="font-weight-bold text-dark" style="letter-spacing: -1px;">
                Dashboard <span class="text-primary">Monitoring</span>
            </h2>
            <p class="text-muted mb-0">Pantau aktivitas pengadaan dan realisasi anggaran secara real-time.</p>
        </div>
        <div>
            <form method="GET" action="{{ route('dashboard') }}" class="form-inline">
                <label class="mr-2 font-weight-bold">Tahun Anggaran:</label>
                <select name="fiscal_year_id" class="form-control rounded-pill border-primary" style="font-weight: bold; padding-left: 20px; padding-right: 20px;" onchange="this.form.submit()">
                    @foreach($fiscalYears as $year)
                        <option value="{{ $year->id }}" {{ $fiscalYearId == $year->id ? 'selected' : '' }}>
                            {{ $year->tahun }} {{ $year->is_active ? '(Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
@stop

<style>
    /* ---------------------------------------------------
       BENTO GRID SYSTEM (TREN UI MODERN)
    --------------------------------------------------- */
    .bento-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-auto-rows: minmax(120px, auto);
        gap: 20px;
        margin-bottom: 40px;
    }

    /* KARTU BENTO DASAR */
    .bento-card {
        background: #ffffff;
        border-radius: 24px; /* Sudut sangat melengkung khas Bento UI */
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }

    .bento-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    }

    /* UKURAN SPESIFIK KARTU (ASYMMETRIC) */
    .bento-hero {
        grid-column: span 2;
        grid-row: span 2;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); /* Warna Gelap Elegan */
        color: white;
    }
    
    .bento-stat-tall {
        grid-column: span 1;
        grid-row: span 2;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    }

    .bento-stat-wide {
        grid-column: span 2;
        grid-row: span 1;
    }

    .bento-stat-small {
        grid-column: span 1;
        grid-row: span 1;
    }

    /* ELEMEN ESTETIK DI DALAM KARTU */
    .bento-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 15px;
    }

    .bg-glass {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* TIMELINE AKTIVITAS (MINIMALIS) */
    .minimal-timeline {
        position: relative;
        padding-left: 20px;
        margin-top: 15px;
    }
    .minimal-timeline::before {
        content: '';
        position: absolute;
        left: 4px;
        top: 5px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -21px;
        top: 4px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid #fff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
    }

    /* RESPONSIVE (Ubah ke satu kolom di HP) */
    @media (max-width: 992px) {
        .bento-grid {
            display: flex;
            flex-direction: column;
        }
    }
</style>

@section('content')

    <div class="bento-grid">

        {{-- 1. HERO CARD (Besar Kiri Atas) --}}
        <div class="bento-card bento-hero">
            <div class="d-flex justify-content-between mb-4">
                <div class="bento-icon-wrapper bg-glass text-white">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="badge bg-glass text-white px-3 py-2" style="border-radius: 20px;">
                    <i class="fas fa-money-bill-wave mr-1"></i> Penyerapan Anggaran
                </span>
            </div>
            
            <h3 class="font-weight-bold mb-2" style="font-size: 1.8rem; letter-spacing: -0.5px;">Realisasi: Rp {{ number_format($realizedBudget, 0, ',', '.') }}</h3>
            <p class="text-light opacity-80 mb-4" style="font-size: 1rem; line-height: 1.6;">
                Total Pagu Dikelola: <strong>Rp {{ number_format($totalPagu, 0, ',', '.') }}</strong>
            </p>
            
            <div class="mt-auto pt-4 border-top border-secondary d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 mr-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="d-block text-sm text-light font-weight-bold">Progress Penyerapan</span>
                        <span class="d-block text-sm text-light font-weight-bold">{{ $absorptionPercentage }}%</span>
                    </div>
                    <div class="progress rounded-pill bg-glass" style="height: 10px;">
                        <div class="progress-bar {{ $absorptionPercentage < 50 ? 'bg-danger' : ($absorptionPercentage < 80 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $absorptionPercentage }}%"></div>
                    </div>
                </div>
                <a href="{{ route('procurement-packages.index') }}" class="btn btn-primary rounded-pill px-4 py-2 font-weight-bold shadow-lg">
                    Lihat Paket <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        {{-- 2. TALL CARD (Tinggi Kanan - Log Aktivitas) --}}
        <div class="bento-card bento-stat-tall" style="overflow-y: auto;">
            <h6 class="font-weight-bold text-dark mb-4"><i class="fas fa-history text-primary mr-2"></i> Aktivitas Terbaru</h6>
            
            @if($recentActivities->count() > 0)
                <div class="minimal-timeline">
                    @foreach($recentActivities as $activity)
                        <div class="timeline-item">
                            <small class="text-muted font-weight-bold d-block mb-1">{{ $activity->updated_at->diffForHumans() }}</small>
                            <p class="mb-0 text-sm text-dark">
                                Paket <strong><a href="{{ route('procurement-packages.show', $activity->package) }}" class="text-primary">{{ Str::limit($activity->package->nama_paket, 30) }}</a></strong> 
                                diupdate ke status <span class="badge badge-info">{{ App\Models\ProcurementPackage::getWorkflowStatuses()[$activity->workflow_status] ?? $activity->workflow_status }}</span>.
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-muted my-4">
                    <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">Belum ada aktivitas.</p>
                </div>
            @endif
        </div>

        {{-- 3. SMALL CARD (Kecil - Paket Aktif) --}}
        <div class="bento-card bento-stat-small bg-primary text-white">
            <div class="bento-icon-wrapper bg-glass text-white mb-3">
                <i class="fas fa-box-open"></i>
            </div>
            <h2 class="font-weight-bold mb-0">{{ $totalPackages - $completedCount }}</h2>
            <span class="text-sm opacity-80 mt-1">Paket Sedang Proses</span>
            
            <i class="fas fa-spinner fa-spin position-absolute" style="font-size: 100px; right: -20px; bottom: -20px; opacity: 0.1;"></i>
        </div>

        {{-- 4. SMALL CARD (Kecil - Selesai) --}}
        <div class="bento-card bento-stat-small">
            <div class="bento-icon-wrapper mb-3" style="background: #e6f4ea; color: #28a745;">
                <i class="fas fa-check-double"></i>
            </div>
            <h2 class="font-weight-bold text-dark mb-0">{{ $completedCount }}</h2>
            <span class="text-sm text-muted mt-1 font-weight-bold">Paket Selesai</span>
        </div>

        {{-- 5. WIDE CARD (Daftar Paket Terlambat / Warning) --}}
        <div class="bento-card bento-stat-wide" style="grid-column: span 3; grid-row: span 2;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="font-weight-bold text-danger mb-0"><i class="fas fa-exclamation-triangle mr-2"></i> Peringatan: Paket Terlambat Timeline</h6>
                <span class="badge badge-danger rounded-pill">{{ $latePackages->count() }} Paket</span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-borderless table-hover mb-0">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Nama Paket</th>
                            <th>Target Selesai</th>
                            <th>Status Saat Ini</th>
                            <th>Keterlambatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latePackages as $late)
                            <tr>
                                <td class="align-middle font-weight-bold">
                                    {{ Str::limit($late->nama_paket, 40) }}
                                    <div class="text-xs text-muted font-weight-normal mt-1">{{ $late->activity->nama ?? '-' }}</div>
                                </td>
                                <td class="align-middle">
                                    {{ \Carbon\Carbon::parse($late->target_procurement_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-warning text-dark">{{ App\Models\ProcurementPackage::getWorkflowStatuses()[$late->procurementPackage->workflow_status ?? \App\Models\ProcurementPackage::WORKFLOW_DRAFT] ?? 'Draft' }}</span>
                                </td>
                                <td class="align-middle text-danger font-weight-bold">
                                    {{ \Carbon\Carbon::parse($late->target_procurement_date)->diffForHumans(now(), ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }} terlewat
                                </td>
                                <td class="align-middle">
                                    <a href="{{ route('procurement-packages.show', $late) }}" class="btn btn-sm btn-outline-primary rounded-pill">Tindaklanjuti</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-success font-weight-bold">
                                    <i class="fas fa-shield-alt fa-2x mb-2 d-block"></i>
                                    Semua paket berjalan sesuai timeline (Tidak ada yang terlambat).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
@stop

@section('js')
<script>
    // Inisialisasi plugin tambahan jika diperlukan di masa depan
</script>
@stop