@props(['procurementPackage'])

@php
    $steps = [
        ['title' => 'Persiapan Pengadaan',  'icon' => 'file-text'],
        ['title' => 'Pemilihan Penyedia',   'icon' => 'users'],
        ['title' => 'Pelaksanaan Kontrak',  'icon' => 'truck'],
        ['title' => 'Pembayaran (Selesai)', 'icon' => 'check-circle-2'],
    ];

    // Peta workflow_status -> indeks tahap aktif (4 = semua selesai).
    // 'preparation_completed' adalah nilai lama di DB: persiapan selesai, masuk pemilihan penyedia.
    $statusStep = [
        \App\Models\ProcurementPackage::WORKFLOW_DRAFT              => 0,
        'preparation_completed'                                     => 1,
        \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION => 1,
        \App\Models\ProcurementPackage::WORKFLOW_EXECUTION          => 2,
        \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS    => 3,
        \App\Models\ProcurementPackage::WORKFLOW_COMPLETED          => 4,
    ];

    $current = $statusStep[$procurementPackage->workflow_status] ?? 0;
    $isDone  = $current >= count($steps);

    // Tahap yang sudah dilalui / sedang berjalan bisa diklik menuju halamannya.
    $stepUrls = [null, null, null, null];
    if ($procurementPackage->package) {
        $pkg = $procurementPackage->package;
        if (auth()->user()?->hasRole('Kabid')) {
            $stepUrls = [
                route('kabid.procurement-packages.show', $pkg),
                route('kabid.procurement-packages.procurement-process.show', $pkg),
                route('kabid.procurement-packages.execution.show', $pkg),
                route('kabid.procurement-packages.payment.show', $pkg),
            ];
        } elseif (auth()->user()?->hasRole(['Admin', 'Super Admin'])) {
            $stepUrls = [
                route('admin.procurement-packages.show', $pkg),
                route('admin.procurement-packages.procurement-process.show', $pkg),
                route('admin.procurement-packages.execution.show', $pkg),
                route('admin.procurement-packages.payment', $pkg),
            ];
        }
    }
@endphp

<style>
    @keyframes kdmp-shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    .kdmp-connector-active {
        background-image: linear-gradient(90deg, #f59e0b 25%, #fcd34d 50%, #f59e0b 75%);
        background-size: 200% 100%;
        animation: kdmp-shimmer 2s linear infinite;
    }
</style>

<div class="bg-white border border-slate-200 shadow-sm rounded-2xl px-5 sm:px-6 py-4 overflow-hidden">
    <div class="flex items-center">
        @foreach($steps as $index => $step)
            @php
                $completed = $current > $index;
                $active    = !$isDone && $current === $index;
                // Hanya tahap yang sudah dilalui/berjalan yang bisa diklik.
                $url = ($completed || $active) ? ($stepUrls[$index] ?? null) : null;
                $tag = $url ? 'a' : 'div';
            @endphp

            {{-- Step --}}
            <{{ $tag }} @if($url) href="{{ $url }}" title="Buka {{ $step['title'] }}" @endif
                class="flex items-center gap-3 shrink-0 {{ $url ? 'rounded-xl -m-1.5 p-1.5 hover:bg-slate-50 transition-colors cursor-pointer' : '' }}">
                <div class="relative shrink-0">
                    @if($active)
                        <span class="absolute inset-0 rounded-full bg-amber-400 opacity-40 animate-ping"></span>
                    @endif
                    <div class="relative w-9 h-9 rounded-full flex items-center justify-center transition-colors
                        {{ $completed
                            ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm shadow-emerald-200'
                            : ($active
                                ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-md shadow-amber-200 ring-2 ring-amber-200'
                                : 'bg-slate-100 text-slate-300') }}">
                        <i data-lucide="{{ $completed ? 'check' : $step['icon'] }}" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="leading-tight pr-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest flex items-center gap-1
                        {{ $completed ? 'text-emerald-600' : ($active ? 'text-amber-600' : 'text-slate-300') }}">
                        Tahap {{ $index + 1 }}
                        @if($completed)
                            <i data-lucide="check" class="w-2.5 h-2.5"></i>
                        @elseif($active)
                            <span class="relative flex w-1.5 h-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full w-1.5 h-1.5 bg-amber-500"></span>
                            </span>
                        @endif
                    </p>
                    <p class="text-xs sm:text-sm font-semibold
                        {{ $completed ? 'text-emerald-700' : ($active ? 'text-slate-900' : 'text-slate-400') }}">
                        {{ $step['title'] }}
                    </p>
                </div>
            </{{ $tag }}>

            {{-- Connector --}}
            @if($index < count($steps) - 1)
                <div class="flex-1 h-1 rounded-full bg-slate-100 mx-2 sm:mx-4 min-w-[16px] overflow-hidden">
                    @if($index < $current)
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-all duration-700" style="width: 100%;"></div>
                    @elseif($active)
                        <div class="h-full rounded-full kdmp-connector-active transition-all duration-700" style="width: 35%;"></div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</div>
