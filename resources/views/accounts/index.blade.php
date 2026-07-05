@component('layouts.kdmp')
@section('title', 'Master Rekening Belanja')

<x-ui.toast />

<x-ui.workspace title="Master Rekening Belanja" description="Kelola daftar kode rekening belanja.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="hash" class="w-4 h-4 text-emerald-500"></i>
            {{ $accounts->total() }} Rekening
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('accounts.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Rekening
        </x-ui.button>
    </x-slot:actions>

    {{-- Filter --}}
    <x-ui.card padding="none" class="mb-6">
        <form method="GET" action="{{ route('accounts.index') }}" class="p-4 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode / nama rekening belanja..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            </div>
            <select name="status" class="px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all sm:w-40">
                <option value="">Semua Status</option>
                <option value="1" @selected($status === '1')>Aktif</option>
                <option value="0" @selected($status === '0')>Nonaktif</option>
            </select>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="search" class="w-4 h-4"></i> Filter
                </button>
                <a href="{{ route('accounts.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
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
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-12 text-center">No</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-56">Kode</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Rekening Belanja</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-32">Status</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">{{ $accounts->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 font-mono">
                                    {{ $account->kode }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-900 leading-snug">{{ $account->nama }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($account->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        <i data-lucide="circle-slash" class="w-3.5 h-3.5"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('accounts.edit', $account) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Edit">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10">
                                <x-ui.empty-state icon="hash" title="Belum Ada Rekening Belanja" description="Klik tombol Tambah Rekening untuk menambahkan data pertama.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('accounts.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Rekening
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($accounts->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $accounts->firstItem() }}–{{ $accounts->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $accounts->total() }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    @if($accounts->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </span>
                    @else
                        <a href="{{ $accounts->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                    @endif

                    @foreach($accounts->getUrlRange(max(1, $accounts->currentPage() - 2), min($accounts->lastPage(), $accounts->currentPage() + 2)) as $page => $url)
                        @if($page == $accounts->currentPage())
                            <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-bold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($accounts->hasMorePages())
                        <a href="{{ $accounts->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
