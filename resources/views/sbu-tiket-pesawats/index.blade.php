@component('layouts.kdmp')
@section('title', 'Master SBU Tiket Pesawat')

<x-ui.toast />

<x-ui.workspace title="SBU Tiket Pesawat" description="Standar biaya tiket pesawat berdasarkan tujuan.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="plane" class="w-4 h-4 text-emerald-500"></i>
            {{ $rates->count() }} Tujuan
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('sbu-tiket-pesawats.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Standar Biaya
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tujuan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Satuan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Bisnis</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Ekonomi</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rates as $rate)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $rate->tujuan }}</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">{{ $rate->satuan }}</span></td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($rate->bisnis, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-800 tabular-nums">Rp {{ number_format($rate->ekonomi, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('sbu-tiket-pesawats.edit', $rate) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="{{ route('sbu-tiket-pesawats.destroy', $rate) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
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
                                <x-ui.empty-state icon="plane" title="Belum Ada Data" description="Belum ada data standar biaya tiket pesawat.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('sbu-tiket-pesawats.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Standar Biaya
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-ui.workspace>
@endcomponent
