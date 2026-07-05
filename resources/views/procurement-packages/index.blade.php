@component('layouts.kdmp')
@section('title', 'Paket Pengadaan')

@php
    // Data Aggregation for Stat Cards (Inline since we can't touch Controller)
    $type = request('type', 'penyedia');
    $qStats = \App\Models\ProcurementPackage::with('package');
    
    if ($type === 'dikecualikan') {
        $qStats->whereNotNull('dikecualikan_type');
    } elseif ($type === 'swakelola') {
        $qStats->whereNull('dikecualikan_type')
              ->whereHas('package', function ($q) {
                  $q->where('jenis_pengadaan', 'like', '%swakelola%');
              });
    } else {
        $qStats->whereNull('dikecualikan_type')
              ->whereHas('package', function ($q) {
                  $q->where('jenis_pengadaan', 'not like', '%swakelola%');
              });
    }
    
    $allPackages = $qStats->get();
    
    $stats = [
        'draft' => ['count' => 0, 'total' => 0],
        'persiapan' => ['count' => 0, 'total' => 0],
        'diproses' => ['count' => 0, 'total' => 0],
        'selesai' => ['count' => 0, 'total' => 0],
    ];

    foreach($allPackages as $p) {
        $budget = $p->package->pagu ?? 0;
        if($p->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_DRAFT) {
            $stats['draft']['count']++;
            $stats['draft']['total'] += $budget;
        } elseif($p->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION) {
            $stats['persiapan']['count']++;
            $stats['persiapan']['total'] += $budget;
        } elseif(in_array($p->workflow_status, [\App\Models\ProcurementPackage::WORKFLOW_EXECUTION, \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS])) {
            $stats['diproses']['count']++;
            $stats['diproses']['total'] += $budget;
        } elseif($p->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED) {
            $stats['selesai']['count']++;
            $stats['selesai']['total'] += $budget;
        } else {
            $stats['draft']['count']++;
            $stats['draft']['total'] += $budget;
        }
    }
    
    $formatM = function($num) {
        return 'Rp ' . number_format($num / 1000000, 2, ',', '.') . ' M';
    };
@endphp

<x-ui.workspace title="Paket Pengadaan" description="Kelola dan pantau seluruh paket pengadaan dalam satu tampilan terpadu.">
    
    <x-slot:actions>
        <div class="flex items-center gap-3 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium mr-2 border border-slate-100 shadow-sm">
            {{ $procurementPackages->total() }} paket
        </div>
        <x-ui.button variant="outline" size="md" href="{{ route('packages.import.index') }}">
            <i data-lucide="upload" class="w-4 h-4 mr-2"></i> Impor
        </x-ui.button>
        <x-ui.button variant="primary" size="md" href="{{ route('packages.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Paket
        </x-ui.button>
    </x-slot:actions>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 mt-2 xl:w-[70%] 2xl:w-[100%]">
        
        <!-- Card Draft -->
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
                <span class="text-sm text-slate-500 font-medium">paket pengadaan</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-slate-900">{{ $formatM($stats['draft']['total']) }}</span> <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>

        <!-- Card Persiapan -->
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
                <span class="text-sm text-slate-500 font-medium">paket pengadaan</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-blue-600">{{ $formatM($stats['persiapan']['total']) }}</span> <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>

        <!-- Card Diproses -->
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
                <span class="text-sm text-slate-500 font-medium">paket pengadaan</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-orange-600">{{ $formatM($stats['diproses']['total']) }}</span> <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>

        <!-- Card Selesai -->
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
                <span class="text-sm text-slate-500 font-medium">paket pengadaan</span>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                <span class="font-bold text-emerald-600">{{ $formatM($stats['selesai']['total']) }}</span> <span class="text-slate-500">total anggaran</span>
            </div>
        </x-ui.card>
    </div>

    <x-ui.card padding="none">
        <!-- Toolbar -->
        <div class="px-6 py-4 border-b border-slate-100">
            @php
                $tabUrl = fn($status) => route('procurement-packages.index', array_filter([
                    'type'   => request('type'),
                    'status' => $status,
                    'search' => request('search'),
                ], fn($v) => $v !== null && $v !== ''));
            @endphp
            <form action="{{ route('procurement-packages.index') }}" method="GET" class="w-full">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('type'))
                    <input type="hidden" name="type" value="{{ request('type') }}">
                @endif
                <x-ui.toolbar search="true" searchPlaceholder="Cari nama paket atau ID RUP...">
                    <x-slot:filters>
                        <div class="flex items-center gap-1 bg-slate-100/70 p-1 rounded-xl border border-slate-200/60">
                            <a href="{{ $tabUrl('') }}" class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ request('status') == '' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">Semua</a>
                            <a href="{{ $tabUrl('draft') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-2 {{ request('status') == 'draft' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">
                                Draft <span class="{{ request('status') == 'draft' ? 'bg-white/20' : 'bg-slate-200' }} text-current px-1.5 py-0.5 rounded-md">{{ $stats['draft']['count'] }}</span>
                            </a>
                            <a href="{{ $tabUrl('persiapan') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-2 {{ request('status') == 'persiapan' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">
                                Persiapan <span class="{{ request('status') == 'persiapan' ? 'bg-white/20' : 'bg-slate-200' }} text-current px-1.5 py-0.5 rounded-md">{{ $stats['persiapan']['count'] }}</span>
                            </a>
                            <a href="{{ $tabUrl('diproses') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-2 {{ request('status') == 'diproses' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">
                                Diproses <span class="{{ request('status') == 'diproses' ? 'bg-white/20' : 'bg-slate-200' }} text-current px-1.5 py-0.5 rounded-md">{{ $stats['diproses']['count'] }}</span>
                            </a>
                            <a href="{{ $tabUrl('selesai') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-2 {{ request('status') == 'selesai' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200' }}">
                                Selesai <span class="{{ request('status') == 'selesai' ? 'bg-white/20' : 'bg-slate-200' }} text-current px-1.5 py-0.5 rounded-md">{{ $stats['selesai']['count'] }}</span>
                            </a>
                        </div>
                    </x-slot:filters>
                    
                    <x-ui.button variant="outline" size="sm" type="button">
                        <i data-lucide="download" class="w-4 h-4 mr-2"></i> Ekspor Excel
                    </x-ui.button>
                </x-ui.toolbar>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-24">ID RUP</th>
                        <th class="px-6 py-4 w-64 whitespace-normal">NAMA PAKET PENGADAAN</th>
                        <th class="px-6 py-4">PAGU</th>
                        <th class="px-6 py-4">JENIS PENGADAAN</th>
                        <th class="px-6 py-4">METODE</th>
                        <th class="px-6 py-4">STATUS</th>
                        <th class="px-6 py-4">PROGRES TAHAPAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($procurementPackages as $index => $p)
                        @php
                            $st = $p->workflow_status;
                            
                            $badgeVariant = 'draft';
                            $badgeLabel = 'Draft';
                            $badgeDot = 'bg-slate-400';
                            $badgeColorClass = 'bg-slate-100 text-slate-700';
                            $progressLabel = 'Belum Dimulai';
                            
                            // Visual Stepper States: 0=gray, 1=blue (active), 2=green (done)
                            $s1 = 0; $s2 = 0; $s3 = 0; $s4 = 0;

                            if($st === \App\Models\ProcurementPackage::WORKFLOW_DRAFT) {
                                $badgeVariant = 'draft';
                                $badgeLabel = 'Draft';
                                $badgeDot = 'bg-slate-400';
                                $badgeColorClass = 'bg-slate-100 text-slate-700';
                                $progressLabel = 'Belum Dimulai';
                            } elseif($st === \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION) {
                                $badgeVariant = 'info';
                                $badgeLabel = 'Persiapan';
                                $badgeDot = 'bg-blue-500';
                                $badgeColorClass = 'bg-blue-50 text-blue-700';
                                $progressLabel = 'Persiapan';
                                $s1 = 1;
                            } elseif(in_array($st, [\App\Models\ProcurementPackage::WORKFLOW_EXECUTION, \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS])) {
                                $badgeVariant = 'warning';
                                $badgeLabel = 'Diproses';
                                $badgeDot = 'bg-orange-500';
                                $badgeColorClass = 'bg-orange-50 text-orange-700';
                                $progressLabel = 'Proses Pengadaan';
                                $s1 = 2; $s2 = 1;
                            } elseif($st === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED) {
                                $badgeVariant = 'success';
                                $badgeLabel = 'Selesai';
                                $badgeDot = 'bg-emerald-500';
                                $badgeColorClass = 'bg-emerald-50 text-emerald-700';
                                $progressLabel = 'Selesai';
                                $s1 = 2; $s2 = 2; $s3 = 2; $s4 = 2;
                            }
                            
                            // Generate dot color classes
                            $dc = fn($state) => $state === 2 ? 'bg-emerald-500' : ($state === 1 ? 'bg-blue-500 ring-2 ring-blue-400/50 animate-pulse' : 'bg-slate-200');
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location='{{ route('procurement-packages.show', $p->package ?? $p->id) }}'">
                            <td class="px-6 py-4 text-slate-400 font-semibold tracking-wide">
                                {{ $p->package->id_rup ?? '-' }}
                            </td>
                            <td class="px-6 py-4 max-w-[16rem] whitespace-normal">
                                <div class="font-bold text-slate-900 text-sm group-hover:text-blue-600 transition-colors leading-snug">{{ $p->package->nama_paket }}</div>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">Rp {{ number_format($p->package->pagu ?? 0, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ $p->package->jenis_pengadaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ $p->package->metode_pengadaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full text-xs font-semibold capitalize tracking-wide {{ $badgeColorClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badgeDot }}"></span> {{ $badgeLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <!-- Stepper UI -->
                                <div class="flex items-center w-56 mb-2">
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dc($s1) }}"></div>
                                    <div class="flex-1 h-[1.5px] {{ $s1 === 2 ? 'bg-emerald-500/50' : 'bg-slate-200' }}"></div>
                                    
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dc($s2) }}"></div>
                                    <div class="flex-1 h-[1.5px] {{ $s2 === 2 ? 'bg-emerald-500/50' : 'bg-slate-200' }}"></div>
                                    
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dc($s3) }}"></div>
                                    <div class="flex-1 h-[1.5px] {{ $s3 === 2 ? 'bg-emerald-500/50' : 'bg-slate-200' }}"></div>
                                    
                                    <div class="w-1.5 h-1.5 rounded-full {{ $dc($s4) }}"></div>
                                </div>
                                <div class="text-xs font-semibold text-slate-500">{{ $progressLabel }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="package-x" title="Tidak Ada Paket" description="Belum ada data paket pengadaan yang dapat ditampilkan." />
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
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
