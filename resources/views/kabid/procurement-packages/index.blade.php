@component('layouts.kdmp')
@section('title', 'Paket Pengadaan')

@php
    $formatM = fn($num) => rupiahSingkat($num);
    $money = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

    $kategoriCards = [
        'all' => [
            'label' => 'Semua Paket', 'desc' => 'Seluruh kategori pengadaan',
            'icon' => 'package', 'iconBg' => 'bg-slate-100 text-slate-500',
            'bar' => 'bg-slate-400', 'ring' => 'ring-slate-300',
        ],
        'penyedia' => [
            'label' => 'Penyedia', 'desc' => 'Mengikuti tahapan workflow',
            'icon' => 'briefcase-business', 'iconBg' => 'bg-sky-50 text-sky-500',
            'bar' => 'bg-sky-500', 'ring' => 'ring-sky-300',
        ],
        'swakelola' => [
            'label' => 'Swakelola', 'desc' => 'Ruang eksekusi mandiri',
            'icon' => 'handshake', 'iconBg' => 'bg-teal-50 text-teal-500',
            'bar' => 'bg-teal-500', 'ring' => 'ring-teal-300',
        ],
        'dikecualikan' => [
            'label' => 'Dikecualikan', 'desc' => 'Tanpa tahapan, berbasis dokumen',
            'icon' => 'file-warning', 'iconBg' => 'bg-violet-50 text-violet-500',
            'bar' => 'bg-violet-500', 'ring' => 'ring-violet-300',
        ],
    ];

    $kategoriBadges = [
        'penyedia'     => 'bg-sky-50 text-sky-700 border-sky-200',
        'swakelola'    => 'bg-teal-50 text-teal-700 border-teal-200',
        'dikecualikan' => 'bg-violet-50 text-violet-700 border-violet-200',
    ];

    $statusTabs = [
        '' => ['label' => 'Semua', 'count' => array_sum(array_column($stats, 'count'))],
        'draft' => ['label' => 'Draft', 'count' => $stats['draft']['count']],
        'persiapan' => ['label' => 'Pemilihan', 'count' => $stats['persiapan']['count']],
        'diproses' => ['label' => 'Diproses', 'count' => $stats['diproses']['count']],
        'selesai' => ['label' => 'Selesai', 'count' => $stats['selesai']['count']],
    ];

    $tabUrl = fn($status) => route('kabid.procurement-packages.index', array_filter([
        'type' => $type !== 'all' ? $type : null,
        'status' => $status,
        'program_id' => request('program_id'),
        'search' => request('search'),
    ], fn($value) => $value !== null && $value !== ''));

    $typeUrl = fn($value) => route('kabid.procurement-packages.index', array_filter([
        'type' => $value !== 'all' ? $value : null,
        'status' => request('status'),
        'program_id' => request('program_id'),
        'search' => request('search'),
    ], fn($item) => $item !== null && $item !== ''));
@endphp

<x-ui.toast />

<x-ui.workspace title="Paket Pengadaan" description="Ikhtisar seluruh paket pengadaan — penyedia, swakelola, dan dikecualikan. Klik kartu kategori untuk memfilter.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="package-check" class="w-4 h-4 text-emerald-500"></i>
            {{ $procurementPackages->total() }} paket
        </div>
        <x-ui.button variant="outline" size="md" href="{{ route('dashboard.kabid') }}">
            <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Dashboard
        </x-ui.button>
    </x-slot:actions>

    {{-- Kartu kategori (sekaligus filter) --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($kategoriCards as $key => $card)
            @php $isActive = $type === $key; @endphp
            <a href="{{ $typeUrl($key) }}"
                class="group relative bg-white rounded-2xl border shadow-sm p-5 transition-all hover:-translate-y-0.5 hover:shadow-md
                    {{ $isActive ? 'border-transparent ring-2 '.$card['ring'] : 'border-slate-200' }}">
                <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full {{ $card['bar'] }} {{ $isActive ? 'opacity-100' : 'opacity-30 group-hover:opacity-70' }} transition-opacity"></div>

                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl {{ $card['iconBg'] }} flex items-center justify-center">
                        <i data-lucide="{{ $card['icon'] }}" class="w-5 h-5"></i>
                    </div>
                    @if($isActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white">
                            <i data-lucide="filter" class="w-2.5 h-2.5"></i> Aktif
                        </span>
                    @endif
                </div>

                <div class="flex items-baseline gap-2 mt-3">
                    <span class="text-3xl font-black text-slate-900">{{ $kategoriStats[$key]['count'] }}</span>
                    <span class="text-xs font-bold text-slate-600">{{ $card['label'] }}</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $card['desc'] }}</p>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                    <span class="font-bold text-slate-800">{{ $formatM($kategoriStats[$key]['total']) }}</span>
                    <span class="text-slate-400">anggaran</span>
                </div>
            </a>
        @endforeach
    </div>

    <x-ui.card padding="none">
        <div class="px-6 py-4 border-b border-slate-100 space-y-4">
            <form action="{{ route('kabid.procurement-packages.index') }}" method="GET" class="w-full space-y-4">
                @if($type !== 'all')
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
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
                    <span class="ml-1 text-[10px] text-slate-400 whitespace-nowrap hidden lg:block">Tahapan berlaku untuk kategori Penyedia</span>
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

                    @if(request()->hasAny(['search', 'status', 'program_id']) || $type !== 'all')
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
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4 min-w-56">Status / Keterangan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($procurementPackages as $p)
                        @php
                            $package = $p->package;
                            $st = $p->workflow_status;

                            $kategori = $p->dikecualikan_type
                                ? 'dikecualikan'
                                : (str_contains(strtolower($package?->jenis_pengadaan ?? ''), 'swakelola') ? 'swakelola' : 'penyedia');

                            $actionLabel = 'Lihat Paket';
                            $actionIcon = 'eye';
                            $actionUrl = $package ? route('kabid.procurement-packages.show', $package) : '#';

                            if ($kategori === 'penyedia') {
                                $stages = [
                                    \App\Models\ProcurementPackage::WORKFLOW_DRAFT => ['Draft', 'bg-slate-400', 'bg-slate-100 text-slate-700', 'Persiapan Pengadaan'],
                                    \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION => ['Pemilihan', 'bg-blue-500', 'bg-blue-50 text-blue-700', 'Pemilihan Penyedia'],
                                    \App\Models\ProcurementPackage::WORKFLOW_EXECUTION => ['Pelaksanaan', 'bg-orange-500', 'bg-orange-50 text-orange-700', 'Pelaksanaan Kontrak'],
                                    \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS => ['Pembayaran', 'bg-amber-500', 'bg-amber-50 text-amber-700', 'Pembayaran'],
                                    \App\Models\ProcurementPackage::WORKFLOW_COMPLETED => ['Selesai', 'bg-emerald-500', 'bg-emerald-50 text-emerald-700', 'Selesai'],
                                ];
                                [$badgeLabel, $badgeDot, $badgeColorClass, $progressLabel] = $stages[$st] ?? $stages[\App\Models\ProcurementPackage::WORKFLOW_DRAFT];

                                if ($st === \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION) {
                                    $actionLabel = 'Buka Pemilihan'; $actionIcon = 'arrow-right';
                                    $actionUrl = $package ? route('kabid.procurement-packages.procurement-process.show', $package) : '#';
                                } elseif ($st === \App\Models\ProcurementPackage::WORKFLOW_EXECUTION) {
                                    $actionLabel = 'Buka Pelaksanaan'; $actionIcon = 'arrow-right';
                                    $actionUrl = $package ? route('kabid.procurement-packages.execution.show', $package) : '#';
                                } elseif ($st === \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS) {
                                    $actionLabel = 'Buka Pembayaran'; $actionIcon = 'arrow-right';
                                    $actionUrl = $package ? route('kabid.procurement-packages.payment.show', $package) : '#';
                                } elseif ($st === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED) {
                                    $actionLabel = 'Lihat Dokumen';
                                    $actionUrl = $package ? route('kabid.procurement-packages.payment.show', $package) : '#';
                                } else {
                                    $actionLabel = 'Lanjutkan Persiapan'; $actionIcon = 'arrow-right';
                                }
                            } elseif ($kategori === 'swakelola') {
                                $accountName = strtolower($package?->account?->nama ?? '');
                                $ruangLabel = 'Ruang Swakelola';
                                if (str_contains($accountName, 'perjalanan dinas')) $ruangLabel = 'Ruang Perjalanan Dinas';
                                elseif (str_contains($accountName, 'lembur')) $ruangLabel = 'Ruang Lembur';
                                $actionLabel = 'Masuk Ruang'; $actionIcon = 'arrow-right';
                            } else {
                                $jenisDikecualikan = $p->dikecualikan_type === 'di_dalam_sistem' ? 'Di Dalam Sistem' : 'Di Luar Sistem';
                                $realisasi = (float) ($p->realisasi_sum ?? 0);
                            }
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $kategoriBadges[$kategori] }}">
                                    <i data-lucide="{{ $kategoriCards[$kategori]['icon'] }}" class="w-3 h-3"></i>
                                    {{ $kategoriCards[$kategori]['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium whitespace-nowrap">
                                {{ $package?->metode_pengadaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($kategori === 'penyedia')
                                    {{-- Penyedia: tahapan workflow --}}
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide {{ $badgeColorClass }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $badgeDot }}"></span>
                                        {{ $badgeLabel }}
                                    </span>
                                    <p class="text-[11px] text-slate-400 mt-1.5">Tahapan: {{ $progressLabel }}</p>
                                @elseif($kategori === 'swakelola')
                                    {{-- Swakelola: jenis ruang eksekusi --}}
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-teal-50 text-teal-700 border border-teal-200">
                                        <i data-lucide="door-open" class="w-3 h-3"></i>
                                        {{ $ruangLabel }}
                                    </span>
                                    <p class="text-[11px] text-slate-400 mt-1.5">Eksekusi mandiri, tanpa tahapan workflow</p>
                                @else
                                    {{-- Dikecualikan: jenis + dokumen + realisasi --}}
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                                            {{ $jenisDikecualikan }}
                                        </span>
                                        @if(($p->external_records_count ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i data-lucide="file-check-2" class="w-3 h-3"></i> {{ $p->external_records_count }} dokumen
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                <i data-lucide="file-x" class="w-3 h-3"></i> Belum ada dokumen
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] {{ $realisasi > 0 ? 'text-emerald-600 font-semibold' : 'text-slate-400' }} mt-1.5">
                                        Realisasi: {{ $money($realisasi) }}
                                    </p>
                                @endif
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
                            <td colspan="7" class="px-6 py-10">
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
