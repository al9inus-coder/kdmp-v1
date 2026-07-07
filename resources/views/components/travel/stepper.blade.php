@props(['travelOrder', 'package', 'active' => 'sppd'])

@php
    $TO = \App\Models\TravelOrder::class;
    $status = $travelOrder->status;
    $spj = $travelOrder->spjStatus();
    $isApproved = $status === $TO::STATUS_APPROVED;

    $detailUrl = route('staf.packages.travel-orders.show', [$package, $travelOrder]);
    $spjUrl = route('staf.packages.travel-orders.spj.show', [$package, $travelOrder]);

    $stages = [
        [
            'label' => 'Pengajuan SPPD',
            'icon'  => 'file-text',
            'state' => $status === $TO::STATUS_DRAFT ? 'current' : 'done',
            'sub'   => $status === $TO::STATUS_DRAFT ? 'Draf' : 'Terkirim',
            'url'   => $detailUrl,
            'group' => 'sppd',
        ],
        [
            'label' => 'Persetujuan',
            'icon'  => 'gavel',
            'state' => match (true) {
                $status === $TO::STATUS_APPROVED  => 'done',
                $status === $TO::STATUS_SUBMITTED => 'current',
                $status === $TO::STATUS_REVISION  => 'warning',
                $status === $TO::STATUS_REJECTED  => 'rejected',
                default                           => 'upcoming',
            },
            'sub'   => $travelOrder->statusMeta()['label'],
            'url'   => $detailUrl,
            'group' => 'sppd',
        ],
        [
            'label' => 'SPJ / Biaya',
            'icon'  => 'receipt-text',
            'state' => match (true) {
                !$isApproved                 => 'upcoming',
                $spj === $TO::SPJ_APPROVED   => 'done',
                $spj === $TO::SPJ_SUBMITTED  => 'current',
                $spj === $TO::SPJ_REVISION   => 'warning',
                default                      => 'current',
            },
            'sub'   => !$isApproved ? 'Terkunci' : $travelOrder->spjStatusMeta()['label'],
            'url'   => $isApproved ? $spjUrl : null,
            'group' => 'spj',
        ],
        [
            'label' => 'Selesai',
            'icon'  => 'flag',
            'state' => $spj === $TO::SPJ_APPROVED ? 'done' : 'upcoming',
            'sub'   => $spj === $TO::SPJ_APPROVED ? 'Tuntas' : 'Menunggu',
            'url'   => null,
            'group' => 'spj',
        ],
    ];

    $circle = [
        'done'     => 'bg-emerald-100 text-emerald-600 border-emerald-200',
        'current'  => 'bg-indigo-600 text-white border-indigo-600 ring-4 ring-indigo-100',
        'warning'  => 'bg-amber-100 text-amber-600 border-amber-200',
        'rejected' => 'bg-rose-100 text-rose-600 border-rose-200',
        'upcoming' => 'bg-white text-slate-400 border-slate-200',
    ];
    $labelColor = [
        'done' => 'text-slate-700', 'current' => 'text-indigo-700',
        'warning' => 'text-amber-700', 'rejected' => 'text-rose-700', 'upcoming' => 'text-slate-400',
    ];
    $subColor = [
        'done' => 'text-emerald-600', 'current' => 'text-indigo-600',
        'warning' => 'text-amber-600', 'rejected' => 'text-rose-600', 'upcoming' => 'text-slate-400',
    ];
    $lineDone = 'bg-emerald-400';
    $lineTodo = 'bg-slate-200';
@endphp

<nav aria-label="Tahapan perjalanan dinas"
     class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-5 sm:px-6">
    <ol class="grid grid-cols-4 gap-0">
        @foreach ($stages as $i => $stage)
            @php
                $isActivePage = $stage['group'] === $active;
                $done = $stage['state'] === 'done';
                $tag = $stage['url'] ? 'a' : 'div';
            @endphp
            <li class="relative flex flex-col items-center text-center px-1 {{ $isActivePage ? '' : '' }}">
                {{-- Konektor kiri --}}
                @if ($i > 0)
                    <span class="absolute top-[18px] right-1/2 w-full h-0.5 {{ $stages[$i-1]['state'] === 'done' ? $lineDone : $lineTodo }}"></span>
                @endif
                {{-- Konektor kanan --}}
                @if ($i < count($stages) - 1)
                    <span class="absolute top-[18px] left-1/2 w-full h-0.5 {{ $done ? $lineDone : $lineTodo }}"></span>
                @endif

                <{{ $tag }} @if($stage['url']) href="{{ $stage['url'] }}" @endif
                    class="relative z-10 flex flex-col items-center {{ $stage['url'] ? 'group cursor-pointer' : ($stage['state'] === 'upcoming' ? 'cursor-default' : '') }}"
                    @if($isActivePage) aria-current="step" @endif>
                    <span class="w-9 h-9 rounded-full border flex items-center justify-center shrink-0 transition-transform {{ $stage['url'] ? 'group-hover:scale-105' : '' }} {{ $circle[$stage['state']] }}">
                        <i data-lucide="{{ $stage['icon'] }}" class="w-4 h-4"></i>
                    </span>
                    <span class="mt-2 text-[11px] sm:text-xs font-bold leading-tight {{ $labelColor[$stage['state']] }} {{ $isActivePage ? 'underline decoration-2 decoration-indigo-300 underline-offset-4' : '' }}">
                        {{ $stage['label'] }}
                    </span>
                    <span class="mt-0.5 text-[10px] font-semibold {{ $subColor[$stage['state']] }}">{{ $stage['sub'] }}</span>
                </{{ $tag }}>
            </li>
        @endforeach
    </ol>
</nav>
