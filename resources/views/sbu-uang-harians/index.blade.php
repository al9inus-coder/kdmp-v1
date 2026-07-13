@component('layouts.kdmp')
@section('title', 'Master SBU Uang Harian')

<x-ui.toast />

<x-ui.workspace title="SBU Uang Harian" description="Standar biaya uang harian perjalanan dinas luar daerah.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="wallet" class="w-4 h-4 text-emerald-500"></i>
            {{ $rates->total() }} Provinsi
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('admin.sbu-uang-harians.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Standar Biaya
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Provinsi</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Uang Harian Luar Kota</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Uang Harian Diklat</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rates as $rate)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $rate->provinsi }}</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">{{ $rate->satuan }}</span></td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($rate->luar_kota, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($rate->diklat, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.sbu-uang-harians.edit', $rate) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="{{ route('admin.sbu-uang-harians.destroy', $rate) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
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
                            <td colspan="5" class="px-6 py-10">
                                <x-ui.empty-state icon="wallet" title="Belum Ada Data" description="Klik tombol Tambah Standar Biaya untuk menambahkan data uang harian.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('admin.sbu-uang-harians.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Standar Biaya
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
