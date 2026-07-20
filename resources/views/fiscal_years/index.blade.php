@component('layouts.kdmp')
@section('title', 'Tahun Anggaran')

<x-ui.toast />

<x-ui.workspace title="Tahun Anggaran" description="Kelola tahun anggaran dan tentukan tahun aktif sistem.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="calendar" class="w-4 h-4 text-emerald-500"></i>
            {{ $years->count() }} Tahun
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('admin.fiscal-years.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Tahun
        </x-ui.button>
    </x-slot:actions>

    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider w-16 text-center">ID</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tahun</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-32">Status</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($years as $year)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">{{ $year->id }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 font-bold text-slate-900 text-base">
                                    <i data-lucide="calendar-days" class="w-4 h-4 text-slate-400"></i> {{ $year->tahun }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($year->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        <i data-lucide="circle-slash" class="w-3.5 h-3.5"></i> Non Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    @if(!$year->is_active)
                                        <form action="{{ route('admin.fiscal-years.activate', $year->id) }}" method="POST"
                                            onsubmit="return confirm('Jadikan tahun {{ $year->tahun }} sebagai tahun aktif?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                                                <i data-lucide="power" class="w-3.5 h-3.5"></i> Aktifkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tahun aktif</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10">
                                <x-ui.empty-state icon="calendar" title="Belum Ada Tahun Anggaran" description="Klik tombol Tambah Tahun untuk menambahkan data pertama.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('admin.fiscal-years.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Tahun
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
