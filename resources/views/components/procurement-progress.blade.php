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
        margin-top: 0.5rem;
        margin-bottom: 0;
    }
    a.ui-stepper-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        text-decoration: none !important;
        cursor: pointer;
    }
    a.ui-stepper-item:hover .ui-step-title {
        color: #007bff;
    }
    
    /* Garis Penghubung Antar Tahapan */
    .ui-stepper-item::after {
        content: "";
        position: absolute;
        top: 20px; /* Tengah ikon 40px */
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
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f8f9fa;
        border: 3px solid #fff;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        position: relative;
        z-index: 2;
        box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        margin-bottom: 8px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    /* Teks Judul Tahapan */
    .ui-step-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 0;
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
        animation: pulse-blue 2s infinite;
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
        background: linear-gradient(to right, #28a745, #20c997);
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
        a.ui-stepper-item {
            flex-direction: row;
            text-align: left;
            width: 100%;
            margin-bottom: 15px;
        }
        .ui-stepper-item::after {
            width: 4px;
            height: calc(100% + 15px);
            left: 18px; 
            top: 40px;
            background: linear-gradient(to bottom, #28a745, #20c997);
        }
        .ui-step-icon {
            margin-bottom: 0;
            margin-right: 15px;
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

    <div class="card-body pt-0 pb-3">
        <div class="ui-stepper">

            {{-- STEP 1: SPESIFIKASI TEKNIS --}}
            <a href="{{ route('procurement-packages.technical-specifications.show', $procurementPackage->package) }}" class="ui-stepper-item {{ $currentStep > 1 ? 'completed' : ($currentStep == 1 ? 'active' : '') }}">
                <div class="ui-step-icon">
                    <i class="fas fa-file-signature"></i>
                </div>

                <div class="ui-step-content">
                    <div class="ui-step-title">1. Spesifikasi Teknis</div>
                </div>
            </a>

            {{-- STEP 2: REFERENSI HARGA --}}
            <a href="{{ route('procurement-packages.price-references.index', $procurementPackage->package) }}" class="ui-stepper-item {{ $currentStep > 2 ? 'completed' : ($currentStep == 2 ? 'active' : '') }}">
                <div class="ui-step-icon">
                    <i class="fas fa-tags"></i>
                </div>

                <div class="ui-step-content">
                    <div class="ui-step-title">2. Referensi Harga</div>
                </div>
            </a>

            {{-- STEP 3: SURAT PERMOHONAN --}}
            <a href="{{ route('procurement-packages.procurement-request.show', $procurementPackage->package) }}" class="ui-stepper-item {{ $currentStep > 3 ? 'completed' : ($currentStep == 3 ? 'active' : '') }}">
                <div class="ui-step-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>

                <div class="ui-step-content">
                    <div class="ui-step-title">3. Surat Permohonan</div>
                </div>
            </a>

        </div>
    </div>
</div>