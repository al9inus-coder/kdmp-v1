@component('layouts.kdmp')
@section('title', 'Master Program')

<x-ui.toast />

<x-ui.workspace title="Master Program" description="Kelola data program anggaran.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="folder" class="w-4 h-4 text-emerald-500"></i>
            {{ $programs->total() }} program
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('programs.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Program
        </x-ui.button>
    </x-slot:actions>

    <x-slot:toolbar>
        <form action="{{ route('programs.index') }}" method="GET" class="w-full">
            <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="flex items-center gap-2 flex-1 max-w-xl">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                        </div>
                        <x-ui.input type="text" name="q" class="pl-9" placeholder="Cari kode / nama program" value="{{ $search }}" />
                    </div>
                    <x-ui.select name="status" class="w-40 shrink-0">
                        <option value="">Semua Status</option>
                        <option value="1" @selected($status === '1')>Aktif</option>
                        <option value="0" @selected($status === '0')>Nonaktif</option>
                    </x-ui.select>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-white bg-slate-800 rounded-md hover:bg-slate-900 transition-colors shrink-0">
                        Cari
                    </button>
                    @if($search || ($status !== null && $status !== ''))
                        <a href="{{ route('programs.index') }}" class="inline-flex items-center p-2 text-slate-400 hover:text-rose-500 transition-colors shrink-0" title="Reset filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </x-slot:toolbar>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">No</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-36">Kode</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Program</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-32">Status</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($programs as $program)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">{{ $programs->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    {{ $program->kode }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $program->nama }}</td>
                            <td class="px-6 py-4">
                                @if($program->is_active)
                                    <x-ui.badge variant="success">Aktif</x-ui.badge>
                                @else
                                    <x-ui.badge variant="draft">Nonaktif</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('programs.edit', $program) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors"
                                        title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10">
                                <x-ui.empty-state icon="folder-open" title="Belum Ada Data Program" description="Klik tombol Tambah Program untuk membuat data pertama.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('programs.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Program
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($programs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $programs->links() }}
            </div>
        @elseif($programs->isNotEmpty())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $programs->count() }}</span> program
                </p>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
