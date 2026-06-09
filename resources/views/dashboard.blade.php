@extends('adminlte::page')

@section('title', 'Beranda KDMP')

@section('content_header')
    <div class="mb-2">
        <h2 class="font-weight-bold text-dark" style="letter-spacing: -1px;">
            Overview <span class="text-primary">KDMP</span>
        </h2>
        <p class="text-muted mb-0">Pantau seluruh aktivitas pengadaan di satu dasbor terpusat.</p>
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
                    <i class="fas fa-bolt"></i>
                </div>
                <span class="badge bg-glass text-white px-3 py-2" style="border-radius: 20px;">T.A. 2026</span>
            </div>
            
            <h3 class="font-weight-bold mb-2" style="font-size: 1.8rem; letter-spacing: -0.5px;">Siap menyusun dokumen hari ini?</h3>
            <p class="text-light opacity-80 mb-4" style="font-size: 1rem; line-height: 1.6;">
                Asisten AI siap membantu Anda mengotomatisasi penyusunan Spesifikasi Teknis, KAK, dan Referensi Harga dalam hitungan detik.
            </p>
            
            <div class="mt-auto pt-4 border-top border-secondary d-flex justify-content-between align-items-center">
                <div>
                    <span class="d-block text-sm text-light mb-1 opacity-70">Total Pagu Dikelola</span>
                    <h4 class="font-weight-bold text-white mb-0">Rp 12.540.000.000</h4>
                </div>
                <button class="btn btn-primary rounded-pill px-4 py-2 font-weight-bold shadow-lg">
                    Buat Paket <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>

        {{-- 2. TALL CARD (Tinggi Kanan - Log Aktivitas) --}}
        <div class="bento-card bento-stat-tall">
            <h6 class="font-weight-bold text-dark mb-4"><i class="fas fa-history text-primary mr-2"></i> Aktivitas Terbaru</h6>
            
            <div class="minimal-timeline">
                <div class="timeline-item">
                    <small class="text-muted font-weight-bold d-block mb-1">10 Menit yang lalu</small>
                    <p class="mb-0 text-sm text-dark"><strong>AI Selesai</strong> menyusun draf KAK untuk <em>Pengadaan Truck Arm Roll</em>.</p>
                </div>
                <div class="timeline-item">
                    <small class="text-muted font-weight-bold d-block mb-1">1 Jam yang lalu</small>
                    <p class="mb-0 text-sm text-dark">Dokumen <strong>Spesifikasi Teknis</strong> disetujui oleh PPK.</p>
                </div>
                <div class="timeline-item">
                    <small class="text-muted font-weight-bold d-block mb-1">Kemarin, 14:30</small>
                    <p class="mb-0 text-sm text-dark">Paket <em>Rehabilitasi Taman</em> masuk ke status <strong>Tender</strong>.</p>
                </div>
            </div>
            
            <button class="btn btn-light btn-sm w-100 mt-auto rounded-pill text-primary font-weight-bold border">
                Lihat Semua Log
            </button>
        </div>

        {{-- 3. SMALL CARD (Kecil - Paket Aktif) --}}
        <div class="bento-card bento-stat-small bg-primary text-white">
            <div class="bento-icon-wrapper bg-glass text-white mb-3">
                <i class="fas fa-box-open"></i>
            </div>
            <h2 class="font-weight-bold mb-0">18</h2>
            <span class="text-sm opacity-80 mt-1">Paket Sedang Proses</span>
            
            {{-- Hiasan Garis Abstrak di belakang --}}
            <i class="fas fa-chart-line position-absolute" style="font-size: 100px; right: -20px; bottom: -20px; opacity: 0.1;"></i>
        </div>

        {{-- 4. SMALL CARD (Kecil - Selesai) --}}
        <div class="bento-card bento-stat-small">
            <div class="bento-icon-wrapper mb-3" style="background: #e6f4ea; color: #28a745;">
                <i class="fas fa-check-double"></i>
            </div>
            <h2 class="font-weight-bold text-dark mb-0">96</h2>
            <span class="text-sm text-muted mt-1 font-weight-bold">Paket Selesai (100%)</span>
        </div>

        {{-- 5. WIDE CARD (Lebar Bawah - Progress Cepat) --}}
        <div class="bento-card bento-stat-wide">
            <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-tasks text-warning mr-2"></i> Paket Prioritas (Drafting AI)</h6>
            
            <div class="d-flex align-items-center mb-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="mr-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="fas fa-truck text-info"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="font-weight-bold text-dark text-sm">Pengadaan Truck Arm Roll</span>
                        <span class="text-primary text-sm font-weight-bold">75%</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                    </div>
                </div>
                <button class="btn btn-sm btn-white border ml-3 rounded-pill shadow-sm"><i class="fas fa-play text-success"></i></button>
            </div>

            <div class="d-flex align-items-center p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div class="mr-3">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="fas fa-leaf text-success"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="font-weight-bold text-dark text-sm">Penutupan TPA Magmagan</span>
                        <span class="text-warning text-sm font-weight-bold">30%</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 30%"></div>
                    </div>
                </div>
                <button class="btn btn-sm btn-white border ml-3 rounded-pill shadow-sm"><i class="fas fa-play text-success"></i></button>
            </div>

        </div>

        {{-- 6. SMALL CARD (Akses Pintas) --}}
        <div class="bento-card bento-stat-small justify-content-center align-items-center text-center" style="border: 2px dashed #cbd5e1; background: transparent; box-shadow: none;">
            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 60px; height: 60px;">
                <i class="fas fa-folder-plus text-primary fa-lg"></i>
            </div>
            <h6 class="font-weight-bold text-dark">Arsip Dokumen</h6>
            <a href="#" class="text-sm text-primary font-weight-bold stretched-link">Jelajahi Data &rarr;</a>
        </div>

    </div>

@stop