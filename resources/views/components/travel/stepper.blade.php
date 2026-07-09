@props(['travelOrder', 'package', 'active' => 'sppd'])

@php
    $TO = \App\Models\TravelOrder::class;
    $status = $travelOrder->status;
    $spj = $travelOrder->spjStatus();
    $isApproved = $status === $TO::STATUS_APPROVED;

    // Halaman detail SPPD kini adaptif: tab Laporan & Biaya (SPJ) menyatu di sana.
    $detailUrl = route('staf.packages.travel-orders.show', [$package, $travelOrder]);

    // Tahap Pelaksanaan ditentukan dari tanggal perjalanan.
    // Selesai = H+1 tanggal kembali (pada hari kembalinya masih "Dalam Perjalanan").
    $today = \Illuminate\Support\Carbon::today();
    $berangkat = $travelOrder->tanggal_berangkat;
    $kembali = $travelOrder->tanggal_kembali;
    $tripStarted = $isApproved && $berangkat && $today->gte($berangkat->copy()->startOfDay());
    $tripDone = $isApproved && $kembali && $today->gt($kembali->copy()->startOfDay());

    $stages = [
        [
            'label' => 'Pengajuan',
            'icon'  => 'file-text',
            'state' => match (true) {
                $isApproved                       => 'done',
                $status === $TO::STATUS_SUBMITTED => 'current',
                $status === $TO::STATUS_REVISION  => 'warning',
                $status === $TO::STATUS_REJECTED  => 'rejected',
                default                           => 'current',
            },
            'sub'   => $travelOrder->statusMeta()['label'],
            'url'   => $detailUrl,
            'group' => 'sppd',
        ],
        [
            'label' => 'Pelaksanaan',
            'icon'  => 'map-pin',
            'state' => match (true) {
                !$isApproved => 'upcoming',
                $tripDone    => 'done',
                default      => 'current',
            },
            'sub'   => match (true) {
                !$isApproved  => 'Menunggu',
                $tripDone     => 'Selesai',
                $tripStarted  => 'Dalam Perjalanan',
                default       => 'Menunggu Berangkat',
            },
            'url'   => null,
            'group' => 'pelaksanaan',
        ],
        [
            'label' => 'Pelaporan',
            'icon'  => 'receipt-text',
            'state' => match (true) {
                !$isApproved                => 'upcoming',
                $spj === $TO::SPJ_APPROVED  => 'done',
                $spj === $TO::SPJ_SUBMITTED => 'current',
                $spj === $TO::SPJ_REVISION  => 'warning',
                $tripDone                   => 'current',
                default                     => 'upcoming',
            },
            'sub'   => !$isApproved ? 'Terkunci' : $travelOrder->spjStatusMeta()['label'],
            'url'   => $isApproved ? $detailUrl : null,
            'group' => 'spj',
        ],
    ];

    // Tahap aktif = tahap pertama yang belum selesai.
    $activeIdx = null;
    foreach ($stages as $i => $s) {
        if (in_array($s['state'], ['current', 'warning', 'rejected'], true)) {
            $activeIdx = $i;
            break;
        }
    }
    $circle = [
        'done'     => 'bg-emerald-500 text-white border-emerald-500',
        'current'  => 'bg-indigo-600 text-white border-indigo-600 ring-4 ring-indigo-100',
        'warning'  => 'bg-amber-50 text-amber-600 border-amber-400',
        'rejected' => 'bg-rose-50 text-rose-600 border-rose-400',
        'upcoming' => 'bg-white text-slate-400 border-dashed border-slate-300',
    ];
    $labelColor = [
        'done' => 'text-slate-700', 'current' => 'text-indigo-700',
        'warning' => 'text-amber-700', 'rejected' => 'text-rose-700', 'upcoming' => 'text-slate-400',
    ];
    $subColor = [
        'done' => 'text-emerald-600', 'current' => 'text-indigo-600',
        'warning' => 'text-amber-600', 'rejected' => 'text-rose-600', 'upcoming' => 'text-slate-400',
    ];
    $planeColor = [
        'current' => 'text-indigo-500', 'warning' => 'text-amber-500', 'rejected' => 'text-rose-500',
    ];

    // Ikon kendaraan mengikuti moda perjalanan pelaksana (prioritas: pesawat > mobil > motor).
    $modes = $travelOrder->personnels->pluck('jenis_kendaraan')->map(fn ($v) => $v ?: 'mobil');
    $vehicle = match (true) {
        $modes->contains('pesawat') => 'pesawat',
        $modes->contains('mobil')   => 'mobil',
        $modes->contains('motor')   => 'motor',
        default                     => 'mobil',
    };
    $vehicleIcon = ['pesawat' => 'plane', 'mobil' => 'car', 'motor' => 'motorbike'][$vehicle];
    // Glyph pesawat menghadap serong kanan-atas, mobil/motor sudah menghadap kanan.
    $vehicleForward = $vehicle === 'pesawat' ? 'rotate-45' : '';
    $vehicleBackward = $vehicle === 'pesawat' ? '-rotate-[135deg]' : '-scale-x-100';

    // Konektor setelah tahap $i: solid berwarna bila tahap $i selesai, putus-putus bila belum.
    $connector = function (int $i) use ($stages): string {
        $from = $stages[$i]['state'];
        $to = $stages[$i + 1]['state'];
        if ($from === 'done' && $to === 'warning') return 'border-t-2 border-amber-400';
        if ($from === 'done' && $to === 'rejected') return 'border-t-2 border-rose-400';
        if ($from === 'done') return 'border-t-2 border-emerald-400';
        return 'border-t-2 border-dashed border-slate-300';
    };
@endphp

<nav aria-label="Tahapan perjalanan dinas"
     class="bg-white border border-slate-200 rounded-2xl shadow-sm px-4 py-3 sm:px-6">

    {{-- Rute: titik pemberhentian + konektor (label di samping kanan ikon) --}}
    <ol class="flex items-center pt-5">
        @foreach ($stages as $i => $stage)
            @php
                $isActivePage = $stage['group'] === $active;
                $isActiveStage = $activeIdx === $i;
                $done = $stage['state'] === 'done';
                $tag = $stage['url'] ? 'a' : 'div';
            @endphp

            <li class="shrink-0">
                <{{ $tag }} @if($stage['url']) href="{{ $stage['url'] }}" @endif
                    class="flex items-center gap-2.5 {{ $stage['url'] ? 'group cursor-pointer' : '' }}"
                    @if($isActivePage) aria-current="step" @endif>

                    <span class="relative">
                        {{-- Kendaraan di tahap aktif (berbalik arah saat revisi/ditolak) --}}
                        @if($isActiveStage)
                            <span class="stepper-plane absolute -top-7 left-1/2 {{ $planeColor[$stage['state']] ?? 'text-indigo-500' }}">
                                <i data-lucide="{{ $vehicleIcon }}" class="w-5 h-5 {{ in_array($stage['state'], ['warning', 'rejected'], true) ? $vehicleBackward : $vehicleForward }}"></i>
                            </span>
                        @endif
                        <span class="w-9 h-9 rounded-full border-2 flex items-center justify-center shrink-0 transition-transform {{ $stage['url'] ? 'group-hover:scale-105' : '' }} {{ $circle[$stage['state']] }}">
                            <i data-lucide="{{ $done ? 'check' : $stage['icon'] }}" class="w-4 h-4"></i>
                        </span>
                    </span>

                    <span class="flex flex-col text-left">
                        <span class="text-[11px] sm:text-xs font-bold leading-tight {{ $labelColor[$stage['state']] }} {{ $isActivePage ? 'underline decoration-2 decoration-indigo-300 underline-offset-4' : '' }}">
                            {{ $stage['label'] }}
                        </span>
                        <span class="mt-0.5 text-[10px] font-semibold {{ $subColor[$stage['state']] }}">{{ $stage['sub'] }}</span>
                    </span>
                </{{ $tag }}>
            </li>

            @if ($i < count($stages) - 1)
                <li aria-hidden="true" class="flex-1 mx-3 sm:mx-4 {{ $connector($i) }}"></li>
            @endif
        @endforeach
    </ol>
</nav>

<style>
    .stepper-plane { animation: kdmp-fly 2.2s ease-in-out infinite; }
    @keyframes kdmp-fly {
        0%, 100% { transform: translate(-50%, 0); }
        50%      { transform: translate(-50%, -4px); }
    }
</style>
