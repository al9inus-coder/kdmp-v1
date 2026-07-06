@component('layouts.kdmp')
@section('title', 'Paket Program')

<x-ui.toast />

<x-ui.workspace title="Paket Pekerjaan" description="{{ $program->kode }}{{ $program->nama ? ' - '.$program->nama : '' }}">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="package" class="w-4 h-4 text-emerald-500"></i>
            {{ $packages->total() }} Paket
        </div>
    </x-slot:actions>

    {{-- Filter --}}
    <x-ui.card padding="none" class="mb-6">
        <form method="GET" action="{{ route('packages.program', $program) }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <select name="fiscal_year_id" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all sm:w-52">
                <option value="">Semua Tahun Anggaran</option>
                @foreach($fiscalYears as $fiscalYear)
                    <option value="{{ $fiscalYear->id }}" @selected((string) $fiscalYearId === (string) $fiscalYear->id)>{{ $fiscalYear->tahun }}</option>
                @endforeach
            </select>
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none"><i data-lucide="search" class="w-4 h-4 text-slate-400"></i></div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari ID RUP / Nama Paket..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            </div>
            <select name="status" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all sm:w-44">
                <option value="">Semua Status</option>
                <option value="needs_review" @selected($status === 'needs_review')>Needs Review</option>
                <option value="draft" @selected($status === 'draft')>Draft</option>
                <option value="approved" @selected($status === 'approved')>Approved</option>
            </select>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="search" class="w-4 h-4"></i> Filter
                </button>
                <a href="{{ route('packages.program', $program) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
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
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">ID RUP</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Paket</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Sub Kegiatan</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pagu</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Metode</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($packages as $package)
                        <tr class="hover:bg-slate-50/80 transition-colors align-top">
                            <td class="px-5 py-4 font-mono text-xs text-slate-500">{{ $package->id_rup ?? '-' }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-900 leading-snug">{{ $package->nama_paket }}</td>
                            <td class="px-5 py-4 text-slate-600"><span class="font-mono text-xs text-slate-400">{{ $package->activity?->kode }}</span> {{ $package->activity?->nama }}</td>
                            <td class="px-5 py-4 text-slate-600"><span class="font-mono text-xs text-slate-400">{{ $package->subActivity?->kode }}</span> {{ $package->subActivity?->nama }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-emerald-700 tabular-nums whitespace-nowrap">Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">{{ $package->jenis_pengadaan ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-600 whitespace-nowrap">{{ $package->metode_pengadaan ?? '-' }}</td>
                            <td class="px-5 py-4 text-center">
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
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('packages.show', $package) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-600 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors" title="Detail">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10">
                                <x-ui.empty-state icon="package" title="Belum Ada Paket" description="Data paket pekerjaan belum tersedia." />
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
