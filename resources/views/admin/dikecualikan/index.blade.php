@component('layouts.kdmp')
@section('title', 'Pengadaan Dikecualikan')

@php
    $rolePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'kabid';
    $money = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $formatM = fn ($v) => rupiahSingkat($v);

    $typeLabels = [
        'di_dalam_sistem' => ['label' => 'Di Dalam Sistem', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'di_luar_sistem'  => ['label' => 'Di Luar Sistem',  'badge' => 'bg-violet-50 text-violet-700 border-violet-200'],
    ];

    $jenisCards = [
        'di_dalam_sistem' => [
            'label' => 'Di Dalam Sistem', 'desc' => 'Tercatat pada e-purchasing',
            'icon' => 'monitor-check', 'iconBg' => 'bg-blue-50 text-blue-500',
            'bar' => 'bg-blue-500', 'ring' => 'ring-blue-300',
        ],
        'di_luar_sistem' => [
            'label' => 'Di Luar Sistem', 'desc' => 'Manual / non-elektronik',
            'icon' => 'file-signature', 'iconBg' => 'bg-violet-50 text-violet-500',
            'bar' => 'bg-violet-500', 'ring' => 'ring-violet-300',
        ],
    ];

    $jenisUrl = fn($j) => route($rolePrefix . '.dikecualikan.index', array_filter([
        'jenis'  => $j,
        'search' => request('search'),
    ], fn($v) => $v !== null && $v !== ''));

    $dokPct = $stats['dokumen']['count'] > 0
        ? round($stats['dokumen']['lengkap'] / $stats['dokumen']['count'] * 100)
        : 0;
    $serapanPct = $stats['all']['total'] > 0
        ? round($stats['all']['realisasi'] / $stats['all']['total'] * 100)
        : 0;
@endphp

<x-ui.toast />

<x-ui.workspace title="Pengadaan Dikecualikan" description="Pengadaan tanpa tahapan workflow — klik kartu jenis untuk memfilter, pantau kelengkapan dokumen & realisasi.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="file-warning" class="w-4 h-4 text-amber-500"></i>
            {{ $stats['all']['count'] }} paket &bull; {{ $formatM($stats['all']['total']) }}
        </div>
    </x-slot:actions>

    {{-- KPI --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        {{-- Kartu jenis (sekaligus filter) --}}
        @foreach($jenisCards as $key => $card)
            @php $isActive = $typeFilter === $key; @endphp
            <a href="{{ $isActive ? $jenisUrl(null) : $jenisUrl($key) }}"
                class="group relative bg-white rounded-2xl border shadow-sm p-5 transition-all hover:-translate-y-0.5 hover:shadow-md
                    {{ $isActive ? 'border-transparent ring-2 '.$card['ring'] : 'border-slate-200' }}">
                <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full {{ $card['bar'] }} {{ $isActive ? 'opacity-100' : 'opacity-30 group-hover:opacity-70' }} transition-opacity"></div>

                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl {{ $card['iconBg'] }} flex items-center justify-center">
                        <i data-lucide="{{ $card['icon'] }}" class="w-5 h-5"></i>
                    </div>
                    @if($isActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white">
                            <i data-lucide="filter" class="w-2.5 h-2.5"></i> Aktif
                        </span>
                    @endif
                </div>

                <div class="flex items-baseline gap-2 mt-3">
                    <span class="text-3xl font-black text-slate-900">{{ $stats[$key]['count'] }}</span>
                    <span class="text-xs font-bold text-slate-600">{{ $card['label'] }}</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $card['desc'] }}</p>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                    <span class="font-bold text-slate-800">{{ $formatM($stats[$key]['total']) }}</span>
                    <span class="text-slate-400">anggaran</span>
                </div>
            </a>
        @endforeach

        {{-- Kelengkapan dokumen --}}
        <div class="relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full bg-amber-500 opacity-30"></div>
            <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                    <i data-lucide="file-check-2" class="w-5 h-5"></i>
                </div>
                <span class="text-2xl font-black {{ $dokPct >= 100 ? 'text-emerald-600' : 'text-slate-900' }}">{{ $dokPct }}%</span>
            </div>
            <div class="flex items-baseline gap-2 mt-3">
                <span class="text-3xl font-black text-slate-900">{{ $stats['dokumen']['lengkap'] }}<span class="text-lg text-slate-400 font-bold">/{{ $stats['dokumen']['count'] }}</span></span>
                <span class="text-xs font-bold text-slate-600">Dokumen</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">Paket yang dokumennya sudah dicatat</p>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full" style="width: {{ min(100, $dokPct) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Realisasi / penyerapan --}}
        <div class="relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full bg-emerald-500 opacity-30"></div>
            <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <span class="text-2xl font-black {{ $serapanPct >= 100 ? 'text-emerald-600' : 'text-slate-900' }}">{{ $serapanPct }}%</span>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-black text-emerald-700">{{ $formatM($stats['all']['realisasi']) }}</span>
                <p class="text-xs font-bold text-slate-600 mt-0.5">Realisasi</p>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">dari pagu {{ $formatM($stats['all']['total']) }}</p>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full" style="width: {{ min(100, $serapanPct) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <x-ui.card padding="none" class="mb-6">
        <form method="GET" action="{{ route($rolePrefix . '.dikecualikan.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            @if($typeFilter)
                <input type="hidden" name="jenis" value="{{ $typeFilter }}">
            @endif

            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama paket atau ID RUP..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            </div>

            <div class="flex items-center gap-2">
                @if($typeFilter)
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border {{ $typeLabels[$typeFilter]['badge'] }}">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i> {{ $typeLabels[$typeFilter]['label'] }}
                    </span>
                @endif
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="search" class="w-4 h-4"></i> Cari
                </button>
                <a href="{{ route($rolePrefix . '.dikecualikan.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                </a>
            </div>
        </form>
    </x-ui.card>

    {{-- Tabel --}}
    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">ID RUP</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Paket</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Jenis</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pagu</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Realisasi</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Dokumen</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($procurementPackages as $pp)
                        @php
                            $pkg = $pp->package;
                            $meta = $typeLabels[$pp->dikecualikan_type] ?? ['label' => $pp->dikecualikan_type, 'badge' => 'bg-slate-100 text-slate-700 border-slate-200'];
                            $realisasi = (float) ($pp->realisasi_sum ?? 0);
                            $pagu = (float) ($pkg->pagu ?? 0);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $pkg->id_rup ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900 leading-snug">{{ $pkg->nama_paket ?? '-' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $pkg->subActivity->nama ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $meta['badge'] }}">
                                    {{ $meta['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 tabular-nums whitespace-nowrap">{{ $money($pagu) }}</td>
                            <td class="px-6 py-4 text-right tabular-nums whitespace-nowrap">
                                <span class="font-semibold {{ $realisasi > 0 ? 'text-emerald-700' : 'text-slate-400' }}">{{ $money($realisasi) }}</span>
                                @if($pagu > 0 && $realisasi > 0)
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ round($realisasi / $pagu * 100, 1) }}% dari pagu</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($pp->external_records_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="file-check-2" class="w-3 h-3"></i> {{ $pp->external_records_count }} dokumen
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        <i data-lucide="file-x" class="w-3 h-3"></i> Belum ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route($rolePrefix . '.procurement-packages.show', $pkg) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors" title="Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10">
                                <x-ui.empty-state icon="file-warning" title="Belum Ada Data" description="Belum ada paket pengadaan yang ditandai dikecualikan." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($procurementPackages->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $procurementPackages->firstItem() }}–{{ $procurementPackages->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $procurementPackages->total() }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    @if($procurementPackages->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                    @else
                        <a href="{{ $procurementPackages->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                    @endif
                    @foreach($procurementPackages->getUrlRange(max(1, $procurementPackages->currentPage() - 2), min($procurementPackages->lastPage(), $procurementPackages->currentPage() + 2)) as $page => $url)
                        @if($page == $procurementPackages->currentPage())
                            <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-bold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($procurementPackages->hasMorePages())
                        <a href="{{ $procurementPackages->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
