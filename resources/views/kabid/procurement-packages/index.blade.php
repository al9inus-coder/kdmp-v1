@component('layouts.kdmp')
@section('title', 'Paket Pengadaan')

@php
    $formatM = fn($num) => 'Rp ' . number_format(((float) $num) / 1000000, 2, ',', '.') . ' M';

    $typeTabs = [
        'penyedia' => ['label' => 'Penyedia', 'icon' => 'briefcase-business'],
        'swakelola' => ['label' => 'Swakelola', 'icon' => 'users-round'],
        'dikecualikan' => ['label' => 'Dikecualikan', 'icon' => 'shield-alert'],
    ];

    $statusTabs = [
        '' => ['label' => 'Semua', 'count' => array_sum(array_column($stats, 'count'))],
        'draft' => ['label' => 'Draft', 'count' => $stats['draft']['count']],
        'persiapan' => ['label' => 'Persiapan', 'count' => $stats['persiapan']['count']],
        'diproses' => ['label' => 'Diproses', 'count' => $stats['diproses']['count']],
        'selesai' => ['label' => 'Selesai', 'count' => $stats['selesai']['count']],
    ];

    $tabUrl = fn($status) => route('kabid.procurement-packages.index', array_filter([
        'type' => request('type', 'penyedia'),
        'status' => $status,
        'program_id' => request('program_id'),
        'search' => request('search'),
    ], fn($value) => $value !== null && $value !== ''));

    $typeUrl = fn($value) => route('kabid.procurement-packages.index', array_filter([
        'type' => $value,
        'status' => request('status'),
        'program_id' => request('program_id'),
        'search' => request('search'),
    ], fn($item) => $item !== null && $item !== ''));
@endphp

<x-ui.workspace title="Paket Pengadaan" description="Pantau dan lanjutkan proses paket pengadaan sesuai tahapan kerja Kabid.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="package-check" class="w-4 h-4 text-emerald-500"></i>
            {{ $procurementPackages->total() }} paket
        </div>
        <x-ui.button variant="outline" size="md" href="{{ route('dashboard.kabid') }}">
            <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Dashboard
        </x-ui.button>
    </x-slot:actions>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <x-ui.card padding="md" class="relative hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-4xl font-bold text-slate-900">{{ $stats['draft']['count'] }}</h2>
                <span class="text-sm text-slate-500 font-medium">paket</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-slate-900">{{ $formatM($stats['draft']['total']) }}</span>
                <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>

        <x-ui.card padding="md" class="relative hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Persiapan
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-4xl font-bold text-slate-900">{{ $stats['persiapan']['count'] }}</h2>
                <span class="text-sm text-slate-500 font-medium">paket</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-blue-600">{{ $formatM($stats['persiapan']['total']) }}</span>
                <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>

        <x-ui.card padding="md" class="relative hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center">
                    <i data-lucide="sliders" class="w-5 h-5"></i>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-50 text-orange-600 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span> Diproses
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-4xl font-bold text-slate-900">{{ $stats['diproses']['count'] }}</h2>
                <span class="text-sm text-slate-500 font-medium">paket</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-orange-600">{{ $formatM($stats['diproses']['total']) }}</span>
                <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>

        <x-ui.card padding="md" class="relative hover:-translate-y-1 transition-transform duration-300">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Selesai
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h2 class="text-4xl font-bold text-slate-900">{{ $stats['selesai']['count'] }}</h2>
                <span class="text-sm text-slate-500 font-medium">paket</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-emerald-600">{{ $formatM($stats['selesai']['total']) }}</span>
                <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card padding="none">
        <div class="px-6 py-4 border-b border-slate-100 space-y-4">
            <div class="flex flex-wrap items-center gap-2">
                @foreach($typeTabs as $value => $tab)
                    <a href="{{ $typeUrl($value) }}"
                        class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl border transition-colors
                            {{ $type === $value ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50 hover:text-slate-700' }}">
                        <i data-lucide="{{ $tab['icon'] }}" class="w-3.5 h-3.5"></i>
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>

            <form action="{{ route('kabid.procurement-packages.index') }}" method="GET" class="w-full">
                <input type="hidden" name="type" value="{{ $type }}">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="flex items-center gap-1 bg-slate-100/70 p-1 rounded-xl border border-slate-200/60 overflow-x-auto">
                    @foreach($statusTabs as $value => $tab)
                        <a href="{{ $tabUrl($value) }}"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-2 whitespace-nowrap
                                {{ request('status', '') === $value ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">
                            {{ $tab['label'] }}
                            <span class="{{ request('status', '') === $value ? 'bg-white/20' : 'bg-slate-200' }} text-current px-1.5 py-0.5 rounded-md">{{ $tab['count'] }}</span>
                        </a>
                    @endforeach
                </div>

                <x-ui.toolbar search="true" searchPlaceholder="Cari nama paket atau ID RUP...">
                    <x-slot:filters>
                        <select name="program_id" onchange="this.form.submit()"
                            class="px-3 py-2 text-sm border border-slate-200 rounded-xl bg-white text-slate-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                                    {{ $program->kode }} - {{ Str::limit($program->nama, 42) }}
                                </option>
                            @endforeach
                        </select>
                    </x-slot:filters>

                    @if(request()->hasAny(['search', 'status', 'program_id']) || $type !== 'penyedia')
                        <x-ui.button variant="ghost" size="sm" href="{{ route('kabid.procurement-packages.index') }}">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Reset
                        </x-ui.button>
                    @endif
                </x-ui.toolbar>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-24">ID RUP</th>
                        <th class="px-6 py-4 min-w-72">Nama Paket Pengadaan</th>
                        <th class="px-6 py-4">Pagu</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Progres Tahapan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($procurementPackages as $p)
                        @php
                            $st = $p->workflow_status;
                            $package = $p->package;

                            $badgeLabel = 'Draft';
                            $badgeDot = 'bg-slate-400';
                            $badgeColorClass = 'bg-slate-100 text-slate-700';
                            $progressLabel = 'Persiapan Pengadaan';
                            $actionLabel = 'Lanjutkan Persiapan';
                            $actionIcon = 'arrow-right';
                            $actionUrl = $package ? route('kabid.procurement-packages.show', $package) : '#';
                            $s1 = 1; $s2 = 0; $s3 = 0; $s4 = 0;

                            if($st === \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION) {
                                $badgeLabel = 'Pemilihan';
                                $badgeDot = 'bg-blue-500';
                                $badgeColorClass = 'bg-blue-50 text-blue-700';
                                $progressLabel = 'Pemilihan Penyedia';
                                $actionLabel = 'Buka Pemilihan';
                                $actionUrl = $package ? route('kabid.procurement-packages.procurement-process.show', $package) : '#';
                                $s1 = 2; $s2 = 1;
                            } elseif($st === \App\Models\ProcurementPackage::WORKFLOW_EXECUTION) {
                                $badgeLabel = 'Pelaksanaan';
                                $badgeDot = 'bg-orange-500';
                                $badgeColorClass = 'bg-orange-50 text-orange-700';
                                $progressLabel = 'Pelaksanaan Kontrak';
                                $actionLabel = 'Buka Pelaksanaan';
                                $actionUrl = $package ? route('kabid.procurement-packages.execution.show', $package) : '#';
                                $s1 = 2; $s2 = 2; $s3 = 1;
                            } elseif($st === \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS) {
                                $badgeLabel = 'Pembayaran';
                                $badgeDot = 'bg-amber-500';
                                $badgeColorClass = 'bg-amber-50 text-amber-700';
                                $progressLabel = 'Pembayaran';
                                $actionLabel = 'Buka Pembayaran';
                                $actionUrl = $package ? route('kabid.procurement-packages.payment.show', $package) : '#';
                                $s1 = 2; $s2 = 2; $s3 = 2; $s4 = 1;
                            } elseif($st === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED) {
                                $badgeLabel = 'Selesai';
                                $badgeDot = 'bg-emerald-500';
                                $badgeColorClass = 'bg-emerald-50 text-emerald-700';
                                $progressLabel = 'Selesai';
                                $actionLabel = 'Lihat Dokumen';
                                $actionIcon = 'eye';
                                $actionUrl = $package ? route('kabid.procurement-packages.payment.show', $package) : '#';
                                $s1 = 2; $s2 = 2; $s3 = 2; $s4 = 2;
                            }

                            if ($p->dikecualikan_type) {
                                $actionLabel = 'Lihat Paket';
                                $actionIcon = 'eye';
                                $actionUrl = $package ? route('kabid.procurement-packages.show', $package) : '#';
                            }

                            $dotClass = fn($state) => $state === 2 ? 'bg-emerald-500' : ($state === 1 ? 'bg-blue-500 ring-2 ring-blue-400/50 animate-pulse' : 'bg-slate-200');
                        @endphp

                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location='{{ $actionUrl }}'">
                            <td class="px-6 py-4 text-slate-400 font-semibold tracking-wide whitespace-nowrap">
                                {{ $package?->id_rup ?? '-' }}
                            </td>
                            <td class="px-6 py-4 max-w-md">
                                <div class="font-bold text-slate-900 text-sm group-hover:text-emerald-600 transition-colors leading-snug">
                                    {{ $package?->nama_paket ?? '-' }}
                                </div>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ $package?->program?->kode ?? '-' }}
                                    @if($package?->program?->nama)
                                        &bull; {{ Str::limit($package->program->nama, 58) }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($package?->pagu ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                                {{ $package?->jenis_pengadaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                                {{ $package?->metode_pengadaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-semibold tracking-wide {{ $badgeColorClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badgeDot }}"></span>
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center w-56 mb-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dotClass($s1) }}"></div>
                                    <div class="flex-1 h-[1.5px] {{ $s1 === 2 ? 'bg-emerald-500/50' : 'bg-slate-200' }}"></div>
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dotClass($s2) }}"></div>
                                    <div class="flex-1 h-[1.5px] {{ $s2 === 2 ? 'bg-emerald-500/50' : 'bg-slate-200' }}"></div>
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dotClass($s3) }}"></div>
                                    <div class="flex-1 h-[1.5px] {{ $s3 === 2 ? 'bg-emerald-500/50' : 'bg-slate-200' }}"></div>
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dotClass($s4) }}"></div>
                                </div>
                                <div class="text-xs font-semibold text-slate-500">{{ $progressLabel }}</div>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ $actionUrl }}" onclick="event.stopPropagation()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                                    <i data-lucide="{{ $actionIcon }}" class="w-3.5 h-3.5"></i>
                                    {{ $actionLabel }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10">
                                <x-ui.empty-state icon="package-x" title="Tidak Ada Paket" description="Belum ada paket pengadaan yang sesuai dengan filter saat ini." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($procurementPackages->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $procurementPackages->links() }}
            </div>
        @else
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $procurementPackages->count() }}</span> paket
                </p>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
