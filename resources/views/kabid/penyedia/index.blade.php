@component('layouts.kdmp')
@section('title', 'Pengadaan Penyedia')

@php
    $formatM = fn($num) => rupiahSingkat($num);

    $pipeline = [
        'draft' => [
            'label' => 'Draft', 'desc' => 'Persiapan dokumen',
            'icon' => 'file-text', 'iconBg' => 'bg-slate-100 text-slate-500',
            'dot' => 'bg-slate-400', 'bar' => 'bg-slate-400', 'ring' => 'ring-slate-300',
        ],
        'persiapan' => [
            'label' => 'Pemilihan', 'desc' => 'Pemilihan penyedia',
            'icon' => 'clipboard-list', 'iconBg' => 'bg-blue-50 text-blue-500',
            'dot' => 'bg-blue-500', 'bar' => 'bg-blue-500', 'ring' => 'ring-blue-300',
        ],
        'diproses' => [
            'label' => 'Diproses', 'desc' => 'Pelaksanaan & pembayaran',
            'icon' => 'sliders', 'iconBg' => 'bg-orange-50 text-orange-500',
            'dot' => 'bg-orange-500', 'bar' => 'bg-orange-500', 'ring' => 'ring-orange-300',
        ],
        'selesai' => [
            'label' => 'Selesai', 'desc' => 'Pengadaan tuntas',
            'icon' => 'check-circle', 'iconBg' => 'bg-emerald-50 text-emerald-500',
            'dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-500', 'ring' => 'ring-emerald-300',
        ],
    ];

    $filterUrl = fn($s) => route('kabid.penyedia.index', array_filter([
        'status'     => $s,
        'search'     => request('search'),
        'program_id' => request('program_id'),
    ], fn($v) => $v !== null && $v !== ''));

    $totalCount = array_sum(array_column($stats, 'count'));
    $totalPagu  = array_sum(array_column($stats, 'total'));
@endphp

<x-ui.toast />

<x-ui.workspace title="Pengadaan Penyedia" description="Ikuti tahapan workflow: persiapan, pemilihan penyedia, pelaksanaan, hingga pembayaran. Klik kartu tahapan untuk memfilter.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="briefcase-business" class="w-4 h-4 text-emerald-500"></i>
            {{ $totalCount }} paket &bull; {{ $formatM($totalPagu) }}
        </div>
    </x-slot:actions>

    {{-- Pipeline: kartu tahapan sekaligus filter --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($pipeline as $key => $stage)
            @php $isActive = $status === $key; @endphp
            <a href="{{ $isActive ? $filterUrl(null) : $filterUrl($key) }}"
                class="group relative bg-white rounded-2xl border shadow-sm p-5 transition-all hover:-translate-y-0.5 hover:shadow-md
                    {{ $isActive ? 'border-transparent ring-2 '.$stage['ring'] : 'border-slate-200' }}">
                <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full {{ $stage['bar'] }} {{ $isActive ? 'opacity-100' : 'opacity-30 group-hover:opacity-70' }} transition-opacity"></div>

                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 rounded-xl {{ $stage['iconBg'] }} flex items-center justify-center">
                        <i data-lucide="{{ $stage['icon'] }}" class="w-5 h-5"></i>
                    </div>
                    @if($isActive)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-white">
                            <i data-lucide="filter" class="w-2.5 h-2.5"></i> Aktif
                        </span>
                    @else
                        <span class="text-[10px] font-semibold text-slate-300 group-hover:text-slate-400 transition-colors">Tahap {{ $loop->iteration }}</span>
                    @endif
                </div>

                <div class="flex items-baseline gap-2 mt-3">
                    <span class="text-3xl font-black text-slate-900">{{ $stats[$key]['count'] }}</span>
                    <span class="flex items-center gap-1.5 text-xs font-bold text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full {{ $stage['dot'] }}"></span>{{ $stage['label'] }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ $stage['desc'] }}</p>
                <div class="mt-3 pt-3 border-t border-slate-100 text-xs">
                    <span class="font-bold text-slate-800">{{ $formatM($stats[$key]['total']) }}</span>
                    <span class="text-slate-400">anggaran</span>
                </div>
            </a>
        @endforeach
    </div>

    <x-ui.card padding="none">
        {{-- Toolbar --}}
        <div class="px-6 py-4 border-b border-slate-100">
            <form action="{{ route('kabid.penyedia.index') }}" method="GET" class="w-full">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
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

                    @if(request()->hasAny(['search', 'status', 'program_id']))
                        <x-ui.button variant="ghost" size="sm" href="{{ route('kabid.penyedia.index') }}">
                            <i data-lucide="rotate-ccw" class="w-4 h-4 mr-2"></i> Reset
                        </x-ui.button>
                    @endif
                </x-ui.toolbar>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-24">ID RUP</th>
                        <th class="px-6 py-4 min-w-72">Nama Paket Pengadaan</th>
                        <th class="px-6 py-4">Pagu</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Progres Tahapan</th>
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
                                {{ $package?->metode_pengadaan ?? '-' }}
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10">
                                <x-ui.empty-state icon="package-x" title="Tidak Ada Paket" description="Belum ada paket penyedia yang sesuai dengan filter saat ini." />
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
