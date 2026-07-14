@component('layouts.kdmp')
@section('title', 'Dashboard - KDMP')

@php
    // Kartu statistik berorientasi tindakan staf (klik untuk memfilter).
    $statTiles = [
        [
            'label' => 'Paket Perlu Dilengkapi',
            'count' => $needsReviewCount,
            'icon'  => 'clipboard-list',
            'tone'  => 'rose',
            'url'   => route('staf.packages.index', ['status' => 'needs_review']),
        ],
        [
            'label' => 'SPPD Perlu Revisi',
            'count' => $sppdCounts['revision'],
            'icon'  => 'file-warning',
            'tone'  => 'amber',
            'url'   => route('staf.sppd.index', ['status' => 'revision']),
        ],
        [
            'label' => 'SPPD Menunggu Persetujuan',
            'count' => $sppdCounts['submitted'],
            'icon'  => 'send',
            'tone'  => 'blue',
            'url'   => route('staf.sppd.index', ['status' => 'submitted']),
        ],
        [
            'label' => 'SPJ Perlu Diproses',
            'count' => $spjPendingCount,
            'icon'  => 'receipt-text',
            'tone'  => 'indigo',
            'url'   => route('staf.sppd.index', ['status' => 'spj_draft']),
        ],
    ];

    $toneMap = [
        'rose'    => ['ic' => 'bg-rose-50 text-rose-600 border-rose-100',       'txt' => 'text-rose-600',    'hover' => 'group-hover:text-rose-600'],
        'amber'   => ['ic' => 'bg-amber-50 text-amber-600 border-amber-100',    'txt' => 'text-amber-600',   'hover' => 'group-hover:text-amber-600'],
        'blue'    => ['ic' => 'bg-blue-50 text-blue-600 border-blue-100',       'txt' => 'text-blue-600',    'hover' => 'group-hover:text-blue-600'],
        'indigo'  => ['ic' => 'bg-indigo-50 text-indigo-600 border-indigo-100', 'txt' => 'text-indigo-600',  'hover' => 'group-hover:text-indigo-600'],
    ];

    $quickActions = [
        ['label' => 'Ajukan SPPD',  'icon' => 'plane',        'url' => route('staf.sppd.create')],
        ['label' => 'Tambah Paket', 'icon' => 'plus-circle',  'url' => route('staf.packages.create')],
        ['label' => 'Kalender',     'icon' => 'calendar-days','url' => route('staf.kalender.index')],
        ['label' => 'Arsip',        'icon' => 'folder-open',  'url' => route('staf.arsip.index')],
    ];
@endphp

<div class="space-y-6">
    <x-ui.toast />

    {{-- Hero: sapaan + tahun anggaran + aksi cepat (tanpa pagu) --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-500 via-indigo-600 to-violet-600 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-16 right-24 w-40 h-40 bg-white/10 rounded-full"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <p class="text-xs font-semibold text-indigo-100 uppercase tracking-wider">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1.5">
                    Selamat datang, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-sm text-indigo-100 mt-2 flex items-center gap-2">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    Tahun Anggaran
                    <span class="px-2.5 py-0.5 bg-white/20 backdrop-blur-md border border-white/30 rounded-full text-white font-bold text-xs shadow-sm">
                        {{ $activeFiscalYear?->tahun ?? '-' }}
                    </span>
                </p>
            </div>

            {{-- Aksi cepat --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                @foreach($quickActions as $qa)
                    <a href="{{ $qa['url'] }}"
                        class="flex flex-col items-center justify-center gap-1.5 px-4 py-3 bg-white/15 hover:bg-white/25 backdrop-blur-md border border-white/25 rounded-xl text-white transition-colors min-w-[92px]">
                        <i data-lucide="{{ $qa['icon'] }}" class="w-5 h-5"></i>
                        <span class="text-xs font-semibold whitespace-nowrap">{{ $qa['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Kartu statistik tindakan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($statTiles as $tile)
            @php $t = $toneMap[$tile['tone']]; @endphp
            <a href="{{ $tile['url'] }}"
                class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between">
                    <span class="w-10 h-10 rounded-xl border flex items-center justify-center {{ $t['ic'] }}">
                        <i data-lucide="{{ $tile['icon'] }}" class="w-5 h-5"></i>
                    </span>
                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-300 {{ $t['hover'] }} transition-colors"></i>
                </div>
                <p class="text-3xl font-bold {{ $t['txt'] }} mt-3">{{ number_format($tile['count']) }}</p>
                <p class="text-xs font-semibold text-slate-500 mt-1 leading-tight">{{ $tile['label'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri (2/3) --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Perjalanan Dinas Terbaru --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="plane" class="w-4 h-4 text-indigo-500"></i>
                        Perjalanan Dinas Terbaru
                    </h3>
                    <a href="{{ route('staf.sppd.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Lihat semua →</a>
                </div>
                <div class="divide-y divide-slate-100 flex-1">
                    @forelse($recentTravels as $to)
                        @php
                            $meta = $to->statusMeta();
                            $ketua = $to->personnels->sortBy('urutan')->first()?->employee?->nama ?? 'Pegawai';
                            $jumlah = $to->personnels->count();
                        @endphp
                        <a href="{{ route('staf.packages.travel-orders.show', [$to->package, $to]) }}"
                            class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-600 truncate">{{ $to->tempat_tujuan }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate">
                                        {{ $ketua }}{{ $jumlah > 1 ? ' +'.($jumlah - 1) : '' }} &middot;
                                        {{ $to->tanggal_berangkat?->locale('id')->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold border shrink-0 {{ $meta['badge'] }}">
                                <i data-lucide="{{ $meta['icon'] }}" class="w-3 h-3"></i> {{ $meta['label'] }}
                            </span>
                        </a>
                    @empty
                        <div class="px-4 py-10 text-center">
                            <i data-lucide="plane" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">Belum ada perjalanan dinas.</p>
                            <a href="{{ route('staf.sppd.create') }}" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">Ajukan SPPD →</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Paket Perlu Dilengkapi --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-rose-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-rose-500"></i>
                        Paket Perlu Dilengkapi
                    </h3>
                    <a href="{{ route('staf.packages.index', ['status' => 'needs_review']) }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800">Lihat semua →</a>
                </div>
                <div class="divide-y divide-slate-100 flex-1">
                    @forelse($needsReviewPackages as $pkg)
                        <a href="{{ route('staf.packages.show', $pkg) }}" class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 transition-colors group">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-600 truncate">{{ $pkg->nama_paket }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">ID RUP: {{ $pkg->id_rup ?? '-' }}</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-indigo-500 ml-2 shrink-0 transition-colors"></i>
                        </a>
                    @empty
                        <div class="px-4 py-10 text-center text-slate-400">
                            <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-emerald-400"></i>
                            <p class="text-sm font-medium text-slate-600">Semua paket sudah lengkap!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (1/3) --}}
        <div class="flex flex-col gap-6">

            {{-- Agenda Perjalanan --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-emerald-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-emerald-500"></i>
                        Agenda Perjalanan
                    </h3>
                    <a href="{{ route('staf.kalender.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">Kalender →</a>
                </div>
                <div class="divide-y divide-slate-100 flex-1">
                    @forelse($upcomingTravels as $to)
                        @php
                            $ketua = $to->personnels->sortBy('urutan')->first()?->employee?->nama ?? 'Pegawai';
                            $jumlah = $to->personnels->count();
                            $sedang = $to->tanggal_berangkat && \Carbon\Carbon::today()->betweenIncluded($to->tanggal_berangkat, $to->tanggal_kembali);
                        @endphp
                        <a href="{{ route('staf.packages.travel-orders.show', [$to->package, $to]) }}"
                            class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group">
                            <div class="flex flex-col items-center justify-center w-11 shrink-0 rounded-lg bg-slate-50 border border-slate-100 py-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase leading-none">{{ $to->tanggal_berangkat?->locale('id')->translatedFormat('M') }}</span>
                                <span class="text-base font-black text-slate-700 leading-tight">{{ $to->tanggal_berangkat?->format('d') }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-emerald-600 truncate">{{ $to->tempat_tujuan }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $ketua }}{{ $jumlah > 1 ? ' +'.($jumlah - 1) : '' }}</p>
                            </div>
                            @if($sedang)
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 rounded-full px-2 py-0.5 shrink-0">Berlangsung</span>
                            @endif
                        </a>
                    @empty
                        <div class="px-4 py-10 text-center text-slate-400">
                            <i data-lucide="calendar-off" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-600">Tidak ada agenda perjalanan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Aktivitas Saya --}}
            @php
                $activityStyles = [
                    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
                    'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600'],
                    'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600'],
                ];
            @endphp
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="activity" class="w-4 h-4 text-amber-500"></i>
                        Aktivitas Saya
                    </h3>
                </div>
                <div class="p-4 flex-1">
                    @if($recentActivities->isNotEmpty())
                        <ol class="relative border-l-2 border-slate-100 ml-4 space-y-5">
                            @foreach($recentActivities as $activity)
                                @php $style = $activityStyles[$activity['color']] ?? $activityStyles['indigo']; @endphp
                                <li class="relative pl-6">
                                    <span class="absolute -left-[17px] top-0 w-8 h-8 rounded-full {{ $style['bg'] }} ring-4 ring-white flex items-center justify-center">
                                        <i data-lucide="{{ $activity['icon'] }}" class="w-3.5 h-3.5 {{ $style['text'] }}"></i>
                                    </span>
                                    <a href="{{ $activity['url'] }}" class="block group">
                                        <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors line-clamp-2">{{ $activity['title'] }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $activity['desc'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            {{ $activity['time']?->locale('id')->diffForHumans() }}
                                        </p>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <div class="py-8 text-center text-slate-400">
                            <i data-lucide="coffee" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">Belum ada aktivitas tercatat.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endcomponent
