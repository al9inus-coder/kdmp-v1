@component('layouts.kdmp')
@section('title', 'Daftar Swakelola')

@php
    $formatM = fn($num) => rupiahSingkat($num);

    $ruangCards = [
        'perjalanan' => [
            'label' => 'Perjalanan Dinas', 'desc' => 'Eksekusi via SPPD & SPJ',
            'icon' => 'plane', 'iconBg' => 'bg-sky-50 text-sky-500',
            'bar' => 'bg-sky-500', 'ring' => 'ring-sky-300',
        ],
        'lembur' => [
            'label' => 'Lembur', 'desc' => 'Eksekusi via kalender lembur',
            'icon' => 'alarm-clock', 'iconBg' => 'bg-amber-50 text-amber-500',
            'bar' => 'bg-amber-500', 'ring' => 'ring-amber-300',
        ],
        'lainnya' => [
            'label' => 'Swakelola Lainnya', 'desc' => 'Ruang swakelola umum',
            'icon' => 'handshake', 'iconBg' => 'bg-teal-50 text-teal-500',
            'bar' => 'bg-teal-500', 'ring' => 'ring-teal-300',
        ],
    ];

    $ruangUrl = fn($r) => route('kabid.swakelola.index', array_filter([
        'ruang'  => $r,
        'search' => request('search'),
    ], fn($v) => $v !== null && $v !== ''));

    $ruangPct = $stats['ruang']['count'] > 0
        ? round($stats['ruang']['created'] / $stats['ruang']['count'] * 100)
        : 0;
@endphp

<x-ui.toast />

<x-ui.workspace title="Daftar Swakelola" description="Paket pengadaan jenis Swakelola — klik kartu jenis ruang untuk memfilter.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="handshake" class="w-4 h-4 text-emerald-500"></i>
            {{ $stats['ruang']['count'] }} paket &bull; {{ $formatM($stats['ruang']['total']) }}
        </div>
    </x-slot:actions>

    {{-- KPI Ruang Eksekusi --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($ruangCards as $key => $card)
            @php $isActive = $ruangFilter === $key; @endphp
            <a href="{{ $isActive ? $ruangUrl(null) : $ruangUrl($key) }}"
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

        {{-- Kesiapan ruang eksekusi (informasi, bukan filter) --}}
        <div class="relative bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full bg-emerald-500 opacity-30"></div>
            <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="door-open" class="w-5 h-5"></i>
                </div>
                <span class="text-2xl font-black {{ $ruangPct >= 100 ? 'text-emerald-600' : 'text-slate-900' }}">{{ $ruangPct }}%</span>
            </div>
            <div class="flex items-baseline gap-2 mt-3">
                <span class="text-3xl font-black text-slate-900">{{ $stats['ruang']['created'] }}<span class="text-lg text-slate-400 font-bold">/{{ $stats['ruang']['count'] }}</span></span>
                <span class="text-xs font-bold text-slate-600">Ruang Eksekusi</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">Paket yang ruangnya sudah dibuat</p>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full" style="width: {{ min(100, $ruangPct) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <x-ui.card padding="none" class="mb-6">
        <form method="GET" action="{{ route('kabid.swakelola.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            @if($ruangFilter)
                <input type="hidden" name="ruang" value="{{ $ruangFilter }}">
            @endif
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama paket atau ID RUP..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="search" class="w-4 h-4"></i> Cari
                </button>
                <a href="{{ route('kabid.swakelola.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
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
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Sub Kegiatan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pagu</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($packages as $package)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $package->id_rup ?? '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900 leading-snug">{{ $package->nama_paket }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $package->subActivity->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-emerald-700 tabular-nums whitespace-nowrap">Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap">{{ $package->metode_pengadaan ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($package->status === 'needs_review')
                                    <x-ui.badge variant="danger">Needs Review</x-ui.badge>
                                @elseif($package->status === 'draft')
                                    <x-ui.badge variant="warning">Draft</x-ui.badge>
                                @elseif($package->status === 'approved')
                                    <x-ui.badge variant="success">Approved</x-ui.badge>
                                @else
                                    <x-ui.badge variant="draft">{{ $package->status }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('kabid.packages.show', $package) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors" title="Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10">
                                <x-ui.empty-state icon="handshake" title="Belum Ada Data" description="Data paket swakelola belum tersedia." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($packages->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $packages->firstItem() }}–{{ $packages->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $packages->total() }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    @if($packages->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                    @else
                        <a href="{{ $packages->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                    @endif
                    @foreach($packages->getUrlRange(max(1, $packages->currentPage() - 2), min($packages->lastPage(), $packages->currentPage() + 2)) as $page => $url)
                        @if($page == $packages->currentPage())
                            <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-bold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($packages->hasMorePages())
                        <a href="{{ $packages->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
