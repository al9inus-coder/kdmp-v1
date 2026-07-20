@component('layouts.kdmp')
@section('title', 'Standar Biaya Umum - Transportasi')

<x-ui.toast />

<x-ui.workspace title="SBU Transportasi" description="Standar biaya transportasi darat berdasarkan tempat tujuan.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="car" class="w-4 h-4 text-emerald-500"></i>
            {{ $rates->total() }} Rute
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('admin.sbu-transport-rates.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Biaya
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tempat Kedudukan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tempat Tujuan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Biaya Mobil</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Biaya Motor</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rates as $rate)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $rate->tempat_kedudukan }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $rate->tempat_tujuan }}</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">{{ $rate->satuan }}</span></td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($rate->biaya_mobil, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($rate->biaya_motor, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.sbu-transport-rates.edit', $rate) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="{{ route('admin.sbu-transport-rates.destroy', $rate) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors" title="Hapus">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10">
                                <x-ui.empty-state icon="car" title="Belum Ada Data" description="Klik tombol Tambah Biaya untuk menambahkan standar biaya transportasi.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('admin.sbu-transport-rates.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Biaya
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rates->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $rates->firstItem() }}–{{ $rates->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $rates->total() }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    @if($rates->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                    @else
                        <a href="{{ $rates->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                    @endif
                    @foreach($rates->getUrlRange(max(1, $rates->currentPage() - 2), min($rates->lastPage(), $rates->currentPage() + 2)) as $page => $url)
                        @if($page == $rates->currentPage())
                            <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-bold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($rates->hasMorePages())
                        <a href="{{ $rates->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
