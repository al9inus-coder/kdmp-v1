@php
$workflowStatuses = \App\Models\ProcurementPackage::getWorkflowStatuses();

$statusKeys = array_keys($workflowStatuses);
$currentStatusIndex = array_search($procurementPackage->workflow_status, $statusKeys);

if ($currentStatusIndex === false) {
    $currentStatusIndex = 0;
}

$steps = [
    [
        'title' => 'Persiapan Pengadaan',
        'icon' => 'fas fa-file-alt',
        'status_key' => \App\Models\ProcurementPackage::WORKFLOW_DRAFT,
        'url' => route('procurement-packages.show', $procurementPackage->package),
    ],
    [
        'title' => 'Pemilihan Penyedia',
        'icon' => 'fas fa-users',
        'status_keys' => [
            \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION,
            \App\Models\ProcurementPackage::WORKFLOW_PURCHASE_ORDER
        ],
        'url' => route('procurement-packages.procurement-process.show', $procurementPackage->package),
    ],
    [
        'title' => 'Pelaksanaan',
        'icon' => 'fas fa-truck-loading',
        'status_key' => \App\Models\ProcurementPackage::WORKFLOW_EXECUTION,
        'url' => route('procurement-packages.execution', $procurementPackage->package),
    ],
    [
        'title' => 'Pembayaran',
        'icon' => 'fas fa-money-check-alt',
        'status_key' => \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
        'url' => route('procurement-payments.show', $procurementPackage->package),
    ],
    [
        'title' => 'Selesai',
        'icon' => 'fas fa-check-circle',
        'status_key' => \App\Models\ProcurementPackage::WORKFLOW_COMPLETED,
        'url' => 'javascript:void(0);',
    ],
];

// Special correction for when workflow is past draft but still labeled as draft due to preparations completed
if ($procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_PREPARATION_COMPLETED) {
    $currentStatusIndex = 0; // It acts as index 0 (Draft) but with isCompleted = true for Draft.
}

$percentage = min(100, max(0, ($currentStatusIndex / (count($steps) - 1)) * 100));
@endphp

<style>
    .workflow-stepper {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }
    
    .workflow-step {
        position: relative;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .workflow-step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #fff;
        border: 4px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px auto;
        font-size: 1.2rem;
        color: #adb5bd;
        transition: all 0.3s;
    }

    .workflow-step-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
    }

    /* Completed state */
    .workflow-step.completed .workflow-step-icon {
        border-color: #28a745;
        background-color: #28a745;
        color: #fff;
    }

    .workflow-step.completed .workflow-step-title {
        color: #28a745;
    }

    /* Active state */
    .workflow-step.active .workflow-step-icon {
        border-color: #007bff;
        background-color: #fff;
        color: #007bff;
        box-shadow: 0 0 0 5px rgba(0, 123, 255, 0.2);
    }

    .workflow-step.active .workflow-step-title {
        color: #007bff;
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .workflow-stepper {
            flex-direction: column;
            align-items: flex-start;
        }
        .workflow-step {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
            text-align: left;
        }
        .workflow-step-icon {
            margin: 0 15px 0 0;
        }
        /* Hide horizontal progress bar in mobile, could use vertical */
        .workflow-progress-line {
            display: none;
        }
    }
    
    .workflow-step-link {
        text-decoration: none !important;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .workflow-step.completed .workflow-step-link:hover .workflow-step-icon,
    .workflow-step.active .workflow-step-link:hover .workflow-step-icon {
        transform: scale(1.1);
        transition: transform 0.2s ease;
    }
</style>

<div class="card shadow-sm mb-4 border-0" style="border-radius: 10px;">
    <div class="card-header bg-white border-0 pt-4 pb-0">
        <h5 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-project-diagram text-primary mr-2"></i> Status Pengadaan
        </h5>
    </div>
    <div class="card-body pt-2 pb-4">
        <div class="workflow-stepper">
            <div class="progress position-absolute workflow-progress-line" style="top: 24px; left: 5%; right: 5%; height: 4px; z-index: 0; background-color: #e9ecef; border-radius: 0;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            
            @foreach($steps as $index => $step)
                @php
                    if (isset($step['status_keys'])) {
                        $stepIndexArr = array_map(function($sk) use ($statusKeys) { return array_search($sk, $statusKeys); }, $step['status_keys']);
                        $maxStepIndex = max($stepIndexArr);
                        $isCompleted = $currentStatusIndex > $maxStepIndex;
                        $isActive = in_array($currentStatusIndex, $stepIndexArr);
                        $stepKeyToCheck = $step['status_keys'][0];
                    } else {
                        $stepIndex = array_search($step['status_key'], $statusKeys);
                        $isCompleted = $currentStatusIndex > $stepIndex;
                        $isActive = $currentStatusIndex === $stepIndex;
                        $stepKeyToCheck = $step['status_key'];
                    }
                    
                    if ($stepKeyToCheck === \App\Models\ProcurementPackage::WORKFLOW_DRAFT && $procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_PREPARATION_COMPLETED) {
                        $isCompleted = true;
                        $isActive = false;
                    }
                    
                    // Special case to light up "Persiapan Pengadaan" if completed
                    if ($stepKeyToCheck === \App\Models\ProcurementPackage::WORKFLOW_DRAFT && $currentStatusIndex > 0) {
                        $isCompleted = true;
                        $isActive = false;
                    }
                    
                    // Special case to make "Selesai" completely green
                    if ($stepKeyToCheck === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED && $procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED) {
                        $isCompleted = true;
                        $isActive = false;
                    }
                @endphp
                <div class="workflow-step {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}">
                    @if($isCompleted || $isActive)
                        <a href="{{ $step['url'] }}" class="workflow-step-link">
                    @else
                        <div class="workflow-step-link" style="cursor: not-allowed;">
                    @endif
                        <div class="workflow-step-icon">
                            @if($isCompleted)
                                <i class="fas fa-check"></i>
                            @else
                                <i class="{{ $step['icon'] }}"></i>
                            @endif
                        </div>
                        <div class="workflow-step-title">
                            {{ $step['title'] }}
                        </div>
                    @if($isCompleted || $isActive)
                        </a>
                    @else
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
