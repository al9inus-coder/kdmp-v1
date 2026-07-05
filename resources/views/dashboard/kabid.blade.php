@component('layouts.kdmp')
@section('title', 'Dashboard Kabid')

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
    <div class="relative overflow-hidden bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-16 right-24 w-40 h-40 bg-white/10 rounded-full"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1.5">
                    Selamat datang, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-sm text-emerald-100 mt-2 flex items-center gap-2">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    Dashboard Kepala Bidang &mdash; Tahun Anggaran
                    <span class="px-2.5 py-0.5 bg-white/20 backdrop-blur-md border border-white/30 rounded-full text-white font-bold text-xs shadow-sm">
                        {{ $activeFiscalYear?->tahun ?? '-' }}
                    </span>
                </p>
            </div>

            {{-- Panel statistik glass --}}
            <div class="flex gap-6 sm:gap-10 bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-6 py-4">
                <div>
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wide">Menunggu Persetujuan</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($submittedCount) }}</p>
                    <p class="text-[11px] text-emerald-100/80 mt-0.5">Rp {{ number_format($submittedPagu, 0, ',', '.') }}</p>
                </div>
                <div class="border-l border-white/25 pl-6 sm:pl-10">
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wide">Total Paket</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalPaket) }}</p>
                    <p class="text-[11px] text-emerald-100/80 mt-0.5">Rp {{ number_format($totalPagu, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Cards (klik untuk lihat daftar terfilter) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($statusSegments as $seg)
            <a href="{{ route('kabid.packages.index', ['status' => $seg['status']]) }}"
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

        {{-- Menunggu Persetujuan (2/3) --}}
        <div class="lg:col-span-2 flex flex-col">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden h-full flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-blue-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="inbox" class="w-4 h-4 text-blue-500"></i>
                        Menunggu Persetujuan Anda
                        @if($submittedCount > 0)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-600 text-white">{{ $submittedCount }}</span>
                        @endif
                    </h3>
                    <a href="{{ route('kabid.packages.index', ['status' => 'submitted']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                        Lihat semua →
                    </a>
                </div>
                <div class="divide-y divide-slate-100 flex-1">
                    @forelse($pendingPackages as $pkg)
                        <a href="{{ route('kabid.packages.show', $pkg) }}" class="flex items-center gap-4 px-4 py-3.5 hover:bg-slate-50 transition-colors group">
                            <div class="w-9 h-9 shrink-0 bg-blue-50 rounded-xl flex items-center justify-center">
                                <i data-lucide="file-clock" class="w-4 h-4 text-blue-500"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 truncate transition-colors">{{ $pkg->nama_paket }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    ID RUP: {{ $pkg->id_rup ?? '-' }}
                                    @if($pkg->submitter)
                                        &bull; diajukan {{ $pkg->submitter->name }}
                                    @endif
                                    @if($pkg->submitted_at)
                                        &bull; {{ $pkg->submitted_at->locale('id')->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($pkg->pagu ?? 0, 0, ',', '.') }}</p>
                                <p class="text-[11px] font-semibold text-blue-600 mt-0.5">Tinjau →</p>
                            </div>
                        </a>
                    @empty
                        <div class="h-full px-4 py-14 flex flex-col items-center justify-center text-center text-slate-400">
                            <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center mb-3">
                                <i data-lucide="check-circle" class="w-7 h-7 text-emerald-500"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-600">Tidak ada paket yang menunggu persetujuan.</p>
                            <p class="text-xs mt-1">Semua pengajuan sudah Anda proses. 👍</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Riwayat Persetujuan (1/3) --}}
        <div class="flex flex-col">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden h-full flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-500"></i>
                        Riwayat Persetujuan
                    </h3>
                </div>
                <div class="p-4 flex-1">
                    @if($recentApproved->isNotEmpty())
                        <ol class="relative border-l-2 border-slate-100 ml-4 space-y-5">
                            @foreach($recentApproved as $pkg)
                                <li class="relative pl-6">
                                    <span class="absolute -left-[17px] top-0 w-8 h-8 rounded-full bg-emerald-50 ring-4 ring-white flex items-center justify-center">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    </span>
                                    <a href="{{ route('kabid.packages.show', $pkg) }}" class="block group">
                                        <p class="text-sm font-semibold text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-2">
                                            {{ $pkg->nama_paket }}
                                        </p>
                                        <p class="text-xs text-slate-400 mt-0.5">Rp {{ number_format($pkg->pagu ?? 0, 0, ',', '.') }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            {{ $pkg->approved_at?->locale('id')->diffForHumans() }}
                                        </p>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <div class="h-full py-8 flex flex-col items-center justify-center text-center text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 mb-2 text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">Belum ada paket yang disetujui.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endcomponent
