@component('layouts.kdmp')
@section('title', 'Master Pegawai')

<x-ui.toast />

<x-ui.workspace title="Master Pegawai" description="Kelola data pegawai untuk perjalanan dinas dan penugasan.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="users" class="w-4 h-4 text-emerald-500"></i>
            {{ $employees->total() }} Pegawai
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('admin.employees.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Pegawai
        </x-ui.button>
    </x-slot:actions>

    {{-- Filter --}}
    <x-ui.card padding="none" class="mb-6">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIP..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="search" class="w-4 h-4"></i> Cari
                </button>
                <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
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
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Golongan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori Biaya</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tgl Lahir</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900 leading-snug">{{ $emp->nama }}</p>
                                <p class="text-xs text-slate-500 flex items-center gap-1"><i data-lucide="id-card" class="w-3 h-3"></i> {{ $emp->nip ?? '-' }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $emp->golongan ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $emp->jabatan ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($emp->kategori_biaya)
                                    <x-ui.badge variant="info">{{ $emp->kategori_biaya }}</x-ui.badge>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">Otomatis</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $emp->tanggal_lahir ? $emp->tanggal_lahir->format('d-m-Y') : '-' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.employees.edit', $emp) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form action="{{ route('admin.employees.destroy', $emp) }}" method="POST" onsubmit="return confirm('Hapus pegawai {{ $emp->nama }}?');">
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
                                <x-ui.empty-state icon="users" title="Belum Ada Pegawai" description="Klik tombol Tambah Pegawai untuk menambahkan data pertama.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('admin.employees.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Pegawai
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $employees->firstItem() }}–{{ $employees->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $employees->total() }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    @if($employees->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                    @else
                        <a href="{{ $employees->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                    @endif
                    @foreach($employees->getUrlRange(max(1, $employees->currentPage() - 2), min($employees->lastPage(), $employees->currentPage() + 2)) as $page => $url)
                        @if($page == $employees->currentPage())
                            <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-bold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($employees->hasMorePages())
                        <a href="{{ $employees->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
