@php

$technicalComplete =
    $procurementPackage->technicalSpecification
    ? true
    : false;

$priceReferenceComplete =
    $procurementPackage->priceReferences()->exists();

$procurementRequestComplete =
    $procurementPackage->procurementRequest
    ? true
    : false;

$currentStep = 1;

if ($technicalComplete) {
    $currentStep = 2;
}

if ($technicalComplete && $priceReferenceComplete) {
    $currentStep = 3;
}

if (
    $technicalComplete
    && $priceReferenceComplete
    && $procurementRequestComplete
) {
    $currentStep = 4;
}

@endphp

<style>
    /* KEYFRAME ANIMASI PULSE UNTUK STEP AKTIF */
    @keyframes pulse-blue {
        0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.5); }
        70% { box-shadow: 0 0 0 12px rgba(0, 123, 255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
    }

    /* CUSTOM STEPPER UI (MODERN & PREMIUM) */
    .ui-stepper {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }
    .ui-stepper-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
    }
    
    /* Garis Penghubung Antar Tahapan */
    .ui-stepper-item::after {
        content: "";
        position: absolute;
        top: 22px; /* Tengah ikon 45px */
        width: 100%;
        left: 50%;
        height: 4px;
        background-color: #eaecf0;
        z-index: 1;
        border-radius: 5px;
        transition: background-color 0.5s ease-in-out;
    }
    .ui-stepper-item:last-child::after {
        display: none;
    }
    
    /* Lingkaran Ikon Base */
    .ui-step-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #f8f9fa;
        border: 3px solid #fff;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        position: relative;
        z-index: 2;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        margin-bottom: 12px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Efek membal saat berubah state */
    }

    /* Teks Judul Tahapan */
    .ui-step-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 8px;
        letter-spacing: 0.3px;
        transition: color 0.3s ease;
    }

    /* ---------------------------------------------------
       STATE 1: AKTIF (Sedang Dikerjakan) - GRADIENT & ANIMASI
    --------------------------------------------------- */
    .ui-stepper-item.active .ui-step-icon {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: #fff;
        border: 2px solid #fff;
        transform: scale(1.15);
        animation: pulse-blue 2s infinite; /* Menambahkan efek denyut */
    }
    .ui-stepper-item.active .ui-step-title {
        color: #0056b3;
    }

    /* ---------------------------------------------------
       STATE 2: SELESAI (Completed) - GRADIENT HIJAU
    --------------------------------------------------- */
    .ui-stepper-item.completed .ui-step-icon {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        color: #fff;
        border: 2px solid #fff;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
    }
    .ui-stepper-item.completed .ui-step-title {
        color: #1e7e34;
    }
    .ui-stepper-item.completed::after {
        background: linear-gradient(to right, #28a745, #20c997); /* Garis gradasi hijau ke tosca */
    }

    /* Badge Custom Styling */
    .badge-modern {
        font-weight: 600;
        letter-spacing: 0.5px;
        padding: 6px 14px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Kartu Progress Bar Premium */
    .progress-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04) !important;
        border-top: 4px solid #007bff !important;
    }

    /* Mode Layar HP */
    @media (max-width: 768px) {
        .ui-stepper {
            flex-direction: column;
            align-items: flex-start;
        }
        .ui-stepper-item {
            flex-direction: row;
            text-align: left;
            width: 100%;
            margin-bottom: 25px;
        }
        .ui-stepper-item::after {
            width: 4px;
            height: calc(100% + 25px);
            left: 22px; 
            top: 45px;
            background: linear-gradient(to bottom, #28a745, #20c997); /* Gradasi vertikal */
        }
        .ui-step-icon {
            margin-bottom: 0;
            margin-right: 18px;
            flex-shrink: 0;
        }
        .ui-step-content {
            padding-top: 4px;
        }
    }
</style>

<div class="card progress-card mb-4">
    <div class="card-header py-3 bg-white border-bottom-0" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
        <h6 class="mb-0 font-weight-bold" style="color: #2c3e50;">
            <i class="fas fa-tasks text-primary mr-2"></i>
            Progres Dokumen Pengadaan
        </h6>
    </div>

    <div class="card-body pt-0 pb-4">
        <div class="ui-stepper">

            {{-- STEP 1: SPESIFIKASI TEKNIS --}}
            <div class="ui-stepper-item {{ $currentStep > 1 ? 'completed' : ($currentStep == 1 ? 'active' : '') }}">
                <div class="ui-step-icon">
                    <i class="fas fa-file-signature"></i>
                </div>

                <div class="ui-step-content">
                    <div class="ui-step-title">1. Spesifikasi Teknis</div>
                    
                    @if($currentStep > 1)
                        <span class="badge badge-success badge-modern rounded-pill">
                            <i class="fas fa-check-circle mr-1"></i> Selesai
                        </span>
                    @elseif($currentStep == 1)
                        <span class="badge badge-primary badge-modern rounded-pill">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Dikerjakan
                        </span>
                    @else
                        <span class="badge badge-light border badge-modern rounded-pill text-muted">
                            <i class="far fa-clock mr-1"></i> Menunggu
                        </span>
                    @endif
                </div>
            </div>

            {{-- STEP 2: REFERENSI HARGA --}}
            <div class="ui-stepper-item {{ $currentStep > 2 ? 'completed' : ($currentStep == 2 ? 'active' : '') }}">
                <div class="ui-step-icon">
                    <i class="fas fa-tags"></i>
                </div>

                <div class="ui-step-content">
                    <div class="ui-step-title">2. Referensi Harga</div>

                    @if($currentStep > 2)
                        <span class="badge badge-success badge-modern rounded-pill">
                            <i class="fas fa-check-circle mr-1"></i> Selesai
                        </span>
                    @elseif($currentStep == 2)
                        <span class="badge badge-primary badge-modern rounded-pill">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Dikerjakan
                        </span>
                    @else
                        <span class="badge badge-light border badge-modern rounded-pill text-muted">
                            <i class="far fa-clock mr-1"></i> Menunggu
                        </span>
                    @endif
                </div>
            </div>

            {{-- STEP 3: SURAT PERMOHONAN --}}
            <div class="ui-stepper-item {{ $currentStep > 3 ? 'completed' : ($currentStep == 3 ? 'active' : '') }}">
                <div class="ui-step-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>

                <div class="ui-step-content">
                    <div class="ui-step-title">3. Surat Permohonan</div>

                    @if($currentStep > 3)
                        <span class="badge badge-success badge-modern rounded-pill">
                            <i class="fas fa-check-circle mr-1"></i> Selesai
                        </span>
                    @elseif($currentStep == 3)
                        <span class="badge badge-primary badge-modern rounded-pill">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Dikerjakan
                        </span>
                    @else
                        <span class="badge badge-light border badge-modern rounded-pill text-muted">
                            <i class="far fa-clock mr-1"></i> Menunggu
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>