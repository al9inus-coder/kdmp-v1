@component('layouts.kdmp')
@section('title', 'Master SKPD')

<x-ui.toast />

<x-ui.workspace title="Master SKPD" description="Kelola data Satuan Kerja Perangkat Daerah beserta pejabat terkait.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="building-2" class="w-4 h-4 text-emerald-500"></i>
            {{ $skpds->count() }} SKPD
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('skpds.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah SKPD
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-12 text-center">No</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Informasi SKPD</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">PA / Kepala Dinas</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">PPK</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">PPTK</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Bendahara</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($skpds as $index => $skpd)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 mb-1.5">
                                    {{ $skpd->kode }}
                                </span>
                                <p class="font-semibold text-slate-900 leading-snug">{{ $skpd->nama }}</p>
                                @if($skpd->singkatan)
                                    <x-ui.badge variant="info" class="mt-1.5">{{ $skpd->singkatan }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-2 mb-1">
                                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-slate-700">{{ $skpd->kepala_skpd ?? '-' }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <i data-lucide="id-card" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-xs text-slate-500">{{ $skpd->nip_kepala ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-2 mb-1">
                                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-slate-700">{{ $skpd->nama_ppk ?? '-' }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <i data-lucide="id-card" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-xs text-slate-500">{{ $skpd->nip_ppk ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-2 mb-1">
                                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-slate-700">{{ $skpd->nama_pptk ?? '-' }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <i data-lucide="id-card" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-xs text-slate-500">{{ $skpd->nip_pptk ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-2 mb-1">
                                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-slate-700">{{ $skpd->nama_bendahara ?? '-' }}</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <i data-lucide="id-card" class="w-3.5 h-3.5 text-slate-400 mt-0.5 shrink-0"></i>
                                    <span class="text-xs text-slate-500">{{ $skpd->nip_bendahara ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('skpds.edit', $skpd) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors"
                                        title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="{{ route('skpds.destroy', $skpd) }}" method="POST"
                                        onsubmit="return confirm('Peringatan: Menghapus SKPD juga akan menghapus data Program, Kegiatan, Sub Kegiatan, dan Paket di bawahnya! Yakin ingin melanjutkan?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors"
                                            title="Hapus">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10">
                                <x-ui.empty-state icon="building-2" title="Belum Ada Data SKPD" description="Klik tombol Tambah SKPD untuk menambahkan data perangkat daerah pertama.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('skpds.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah SKPD
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($skpds->isNotEmpty())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $skpds->count() }}</span> SKPD
                </p>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
