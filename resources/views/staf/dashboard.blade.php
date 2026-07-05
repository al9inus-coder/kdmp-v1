@component('layouts.kdmp')
@section('title', 'Dashboard Staf - KDMP')

@php
    $total = max($totalPaket, 1);
    $statusSegments = [
        ['label' => 'Needs Review', 'count' => $needsReviewCount, 'bar' => 'bg-rose-400',    'dot' => 'bg-rose-500',    'text' => 'text-rose-600',    'arrow' => 'group-hover:text-rose-600',    'status' => 'needs_review'],
        ['label' => 'Draft',        'count' => $draftCount,       'bar' => 'bg-amber-400',   'dot' => 'bg-amber-500',   'text' => 'text-amber-600',   'arrow' => 'group-hover:text-amber-600',   'status' => 'draft'],
        ['label' => 'Submitted',    'count' => $submittedCount,   'bar' => 'bg-blue-400',    'dot' => 'bg-blue-500',    'text' => 'text-blue-600',    'arrow' => 'group-hover:text-blue-600',    'status' => 'submitted'],
        ['label' => 'Approved',     'count' => $approvedCount,    'bar' => 'bg-emerald-400', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'arrow' => 'group-hover:text-emerald-600', 'status' => 'approved'],
    ];
@endphp

<div class="space-y-6">
    <x-ui.toast />

    {{-- Hero --}}
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

            {{-- Panel statistik dengan efek glass --}}
            <div class="flex gap-6 sm:gap-10 bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-6 py-4">
                <div>
                    <p class="text-xs font-semibold text-indigo-100 uppercase tracking-wide">Total Paket</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalPaket) }}</p>
                </div>
                <div class="border-l border-white/25 pl-6 sm:pl-10">
                    <p class="text-xs font-semibold text-indigo-100 uppercase tracking-wide">Total Pagu</p>
                    <p class="text-3xl font-bold text-white mt-1">
                        Rp {{ number_format($totalPagu / 1000000, 1, ',', '.') }} <span class="text-lg font-semibold text-indigo-100">Jt</span>
                    </p>
                    <p class="text-[11px] text-indigo-100/80 mt-0.5">Rp {{ number_format($totalPagu, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Cards (klik untuk filter) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($statusSegments as $seg)
            <a href="{{ route('staf.packages.index', ['status' => $seg['status']]) }}"
                class="group bg-white border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        <span class="w-2 h-2 rounded-full {{ $seg['dot'] }}"></span>
                        {{ $seg['label'] }}
                    </span>
                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-300 {{ $seg['arrow'] }} transition-colors"></i>
                </div>
                <p class="text-3xl font-bold {{ $seg['text'] }} mt-3">{{ number_format($seg['count']) }}</p>
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden mt-3">
                    <div class="h-full {{ $seg['bar'] }} rounded-full" style="width: {{ round($seg['count'] / $total * 100) }}%"></div>
                </div>
            </a>
        @endforeach
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri (2/3) --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Distribusi Status: stacked bar --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-slate-400"></i>
                        Distribusi Status Paket
                    </h3>
                    <span class="text-xs text-slate-400">{{ number_format($totalPaket) }} paket total</span>
                </div>

                @if($totalPaket > 0)
                    <div class="flex h-3 rounded-full overflow-hidden bg-slate-100">
                        @foreach($statusSegments as $seg)
                            @if($seg['count'] > 0)
                                <div class="{{ $seg['bar'] }} first:rounded-l-full last:rounded-r-full"
                                    style="width: {{ $seg['count'] / $total * 100 }}%"
                                    title="{{ $seg['label'] }}: {{ $seg['count'] }} paket"></div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="h-3 rounded-full bg-slate-100"></div>
                @endif

                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-4">
                    @foreach($statusSegments as $seg)
                        <a href="{{ route('staf.packages.index', ['status' => $seg['status']]) }}"
                            class="flex items-center gap-1.5 text-xs font-semibold {{ $seg['text'] }} hover:underline">
                            <span class="w-2 h-2 rounded-full {{ $seg['dot'] }}"></span>
                            {{ $seg['label'] }}
                            <span class="text-slate-400 font-medium">&middot; {{ $seg['count'] }} ({{ $totalPaket ? round($seg['count'] / $total * 100) : 0 }}%)</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Dua list berdampingan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 flex-1">

                {{-- Perlu Dilengkapi --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 bg-rose-50/50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-500"></i>
                            Perlu Dilengkapi
                        </h3>
                        <a href="{{ route('staf.packages.index', ['status' => 'needs_review']) }}" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                            Lihat semua →
                        </a>
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

                {{-- Paket Terbaru --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                            <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                            Paket Terbaru
                        </h3>
                        <a href="{{ route('staf.packages.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Lihat semua →
                        </a>
                    </div>
                    <div class="divide-y divide-slate-100 flex-1">
                        @forelse($recentPackages as $pkg)
                            <a href="{{ route('staf.packages.show', $pkg) }}" class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 transition-colors group">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-600 truncate">{{ $pkg->nama_paket }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Rp {{ number_format($pkg->pagu ?? 0, 0, ',', '.') }}</p>
                                </div>
                                <div class="ml-3 flex-shrink-0">
                                    @if($pkg->status === 'approved')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Approved
                                        </span>
                                    @elseif($pkg->status === 'submitted')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-700">
                                            <i data-lucide="send" class="w-3 h-3"></i> Submitted
                                        </span>
                                    @elseif($pkg->status === 'draft')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-700">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Draft
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-rose-100 text-rose-700">
                                            <i data-lucide="alert-circle" class="w-3 h-3"></i> Review
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-10 text-center">
                                <i data-lucide="package-x" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Belum ada paket ditambahkan.</p>
                                <a href="{{ route('staf.packages.create') }}" class="text-xs text-indigo-600 hover:underline mt-1 inline-block">Tambah sekarang →</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (1/3) --}}
        <div class="flex flex-col">

            {{-- Aktivitas Saya --}}
            @php
                $activityStyles = [
                    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
                    'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'ring' => 'ring-blue-100'],
                    'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'ring' => 'ring-indigo-100'],
                ];
            @endphp
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden h-full flex flex-col">
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
                                        <p class="text-sm font-semibold text-slate-800 group-hover:text-indigo-600 transition-colors line-clamp-2">
                                            {{ $activity['title'] }}
                                        </p>
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
                            <p class="text-xs mt-1">Impor atau ajukan paket untuk memulai.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endcomponent
