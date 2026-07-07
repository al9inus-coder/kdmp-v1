@component('layouts.kdmp')
@section('title', 'Dashboard Kabid')

@php
    $money   = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $formatM = fn ($v) => rupiahSingkat($v);

    $totalInbox = $submittedCount + $pendingSppdCount;

    $workflowMeta = [
        \App\Models\ProcurementPackage::WORKFLOW_DRAFT              => ['label' => 'Persiapan',   'hex' => '#94a3b8', 'dot' => 'bg-slate-400',   'status' => 'draft'],
        \App\Models\ProcurementPackage::WORKFLOW_PROVIDER_SELECTION => ['label' => 'Pemilihan',   'hex' => '#3b82f6', 'dot' => 'bg-blue-500',    'status' => 'persiapan'],
        \App\Models\ProcurementPackage::WORKFLOW_EXECUTION          => ['label' => 'Pelaksanaan', 'hex' => '#f97316', 'dot' => 'bg-orange-500',  'status' => 'diproses'],
        \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS    => ['label' => 'Pembayaran',  'hex' => '#f59e0b', 'dot' => 'bg-amber-500',   'status' => 'diproses'],
        \App\Models\ProcurementPackage::WORKFLOW_COMPLETED          => ['label' => 'Selesai',     'hex' => '#10b981', 'dot' => 'bg-emerald-500', 'status' => 'selesai'],
    ];
    $workflowTotal = array_sum($workflowStats);

    $activityStyles = [
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
        'sky'     => ['bg' => 'bg-sky-50',     'text' => 'text-sky-600'],
        'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600'],
    ];

    $reminderStyles = [
        'rose'   => ['bg' => 'bg-rose-50',   'text' => 'text-rose-600',   'border' => 'border-rose-100 hover:border-rose-200'],
        'amber'  => ['bg' => 'bg-amber-50',  'text' => 'text-amber-600',  'border' => 'border-amber-100 hover:border-amber-200'],
        'sky'    => ['bg' => 'bg-sky-50',    'text' => 'text-sky-600',    'border' => 'border-sky-100 hover:border-sky-200'],
        'violet' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'border' => 'border-violet-100 hover:border-violet-200'],
    ];
@endphp

<div class="space-y-6">
    <x-ui.toast />

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-emerald-500 via-emerald-600 to-teal-600 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-16 right-24 w-40 h-40 bg-white/10 rounded-full"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1.5">
                    Selamat datang, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-sm text-emerald-100 mt-2 flex items-center gap-2 flex-wrap">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    Tahun Anggaran
                    <span class="px-2.5 py-0.5 bg-white/20 backdrop-blur-md border border-white/30 rounded-full text-white font-bold text-xs shadow-sm">
                        {{ $activeFiscalYear?->tahun ?? '-' }}
                    </span>
                    @if($totalInbox > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-amber-400/90 rounded-full text-amber-950 font-bold text-xs shadow-sm">
                            <i data-lucide="bell-ring" class="w-3 h-3"></i>
                            {{ $totalInbox }} menunggu tindakan Anda
                        </span>
                    @endif
                </p>
            </div>

            {{-- Panel statistik glass --}}
            <div class="flex gap-5 sm:gap-8 bg-white/15 backdrop-blur-md border border-white/25 rounded-2xl px-6 py-4">
                <div>
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wide">Paket Menunggu</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($submittedCount) }}</p>
                    <p class="text-[11px] text-emerald-100/80 mt-0.5">{{ $money($submittedPagu) }}</p>
                </div>
                <div class="border-l border-white/25 pl-5 sm:pl-8">
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wide">SPPD Menunggu</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($pendingSppdCount) }}</p>
                    <p class="text-[11px] text-emerald-100/80 mt-0.5">perlu review</p>
                </div>
                <div class="border-l border-white/25 pl-5 sm:pl-8">
                    <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wide">Total Paket</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($totalPaket) }}</p>
                    <p class="text-[11px] text-emerald-100/80 mt-0.5">{{ $money($totalPagu) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Anggaran --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="relative bg-white border border-slate-200 rounded-2xl p-5 shadow-sm overflow-hidden">
            <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full bg-slate-400 opacity-30"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Pagu</p>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-3">{{ $formatM($totalPagu) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $money($totalPagu) }}</p>
        </div>

        <div class="relative bg-white border border-slate-200 rounded-2xl p-5 shadow-sm overflow-hidden">
            <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full bg-emerald-500 opacity-30"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Realisasi</p>
            </div>
            <p class="text-2xl font-black text-emerald-700 mt-3">{{ $formatM($realisasi) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $money($realisasi) }}</p>
        </div>

        <div class="relative bg-white border border-slate-200 rounded-2xl p-5 shadow-sm overflow-hidden">
            <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full bg-blue-500 opacity-30"></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center shrink-0">
                    <i data-lucide="piggy-bank" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Sisa Anggaran</p>
            </div>
            <p class="text-2xl font-black text-blue-700 mt-3">{{ $formatM($sisaAnggaran) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $money($sisaAnggaran) }}</p>
        </div>

        <div class="relative bg-white border border-slate-200 rounded-2xl p-5 shadow-sm overflow-hidden flex items-center gap-4">
            <div class="absolute top-0 left-5 right-5 h-1 rounded-b-full bg-teal-500 opacity-30"></div>
            <div class="relative w-20 h-20 shrink-0">
                <canvas id="serapanChart"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-sm font-black {{ $serapanPct >= 100 ? 'text-emerald-600' : 'text-slate-800' }}">{{ $serapanPct }}%</span>
                </div>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Serapan</p>
                <p class="text-sm font-bold text-slate-800 mt-1">{{ $formatM($realisasi) }}</p>
                <p class="text-[11px] text-slate-400">dari {{ $formatM($totalPagu) }}</p>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri (2/3) --}}
        <div class="lg:col-span-2 flex flex-col gap-6">

            {{-- Proses Pengadaan Penyedia --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="briefcase-business" class="w-4 h-4 text-sky-500"></i>
                        Proses Pengadaan Penyedia
                    </h3>
                    <a href="{{ route('kabid.penyedia.index') }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">
                        Buka halaman →
                    </a>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-[180px_minmax(0,1fr)] gap-6 items-center">
                    <div class="relative w-40 h-40 mx-auto">
                        <canvas id="workflowChart"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-black text-slate-800">{{ $workflowTotal }}</span>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase">paket</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @foreach($workflowMeta as $key => $meta)
                            @php
                                $count = $workflowStats[$key] ?? 0;
                                $pct = $workflowTotal > 0 ? round($count / $workflowTotal * 100) : 0;
                            @endphp
                            <a href="{{ route('kabid.penyedia.index', ['status' => $meta['status']]) }}"
                                class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors group">
                                <span class="w-2.5 h-2.5 rounded-full {{ $meta['dot'] }} shrink-0"></span>
                                <span class="text-sm font-semibold text-slate-700 group-hover:text-slate-900 flex-1">{{ $meta['label'] }}</span>
                                <div class="hidden md:block w-32 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $meta['dot'] }} rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-sm font-black text-slate-800 w-8 text-right">{{ $count }}</span>
                                <span class="text-[11px] text-slate-400 w-10 text-right">{{ $pct }}%</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Kotak Masuk --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 flex-1">
                {{-- Paket menunggu persetujuan --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 bg-blue-50/50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                            <i data-lucide="inbox" class="w-4 h-4 text-blue-500"></i>
                            Paket RUP
                            @if($submittedCount > 0)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-600 text-white">{{ $submittedCount }}</span>
                            @endif
                        </h3>
                        <a href="{{ route('kabid.packages.index', ['status' => 'submitted']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                            Semua →
                        </a>
                    </div>
                    <div class="divide-y divide-slate-100 flex-1">
                        @forelse($pendingPackages->take(4) as $pkg)
                            <a href="{{ route('kabid.packages.show', $pkg) }}" class="block px-4 py-3 hover:bg-slate-50 transition-colors group">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-blue-600 truncate transition-colors">{{ $pkg->nama_paket }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 flex items-center justify-between gap-2">
                                    <span class="truncate">
                                        {{ $pkg->submitter?->name ?? '-' }}
                                        @if($pkg->submitted_at) &bull; {{ $pkg->submitted_at->locale('id')->diffForHumans(null, true) }} @endif
                                    </span>
                                    <span class="font-bold text-slate-600 whitespace-nowrap">{{ $money($pkg->pagu ?? 0) }}</span>
                                </p>
                            </a>
                        @empty
                            <div class="h-full px-4 py-8 flex flex-col items-center justify-center text-center text-slate-400">
                                <i data-lucide="check-circle" class="w-7 h-7 mb-2 text-emerald-400"></i>
                                <p class="text-xs font-medium text-slate-500">Tidak ada pengajuan paket.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- SPPD menunggu review --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 border-b border-slate-100 bg-sky-50/50 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                            <i data-lucide="plane" class="w-4 h-4 text-sky-500"></i>
                            Pengajuan SPPD
                            @if($pendingSppdCount > 0)
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-sky-600 text-white">{{ $pendingSppdCount }}</span>
                            @endif
                        </h3>
                        <a href="{{ route('kabid.sppd.index', ['status' => 'submitted']) }}" class="text-xs font-semibold text-sky-600 hover:text-sky-800">
                            Semua →
                        </a>
                    </div>
                    <div class="divide-y divide-slate-100 flex-1">
                        @forelse($pendingSppd->take(4) as $to)
                            <a href="{{ route('kabid.packages.travel-orders.show', [$to->package, $to]) }}" class="block px-4 py-3 hover:bg-slate-50 transition-colors group">
                                <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-600 truncate transition-colors">{{ $to->maksud_perjalanan }}</p>
                                <p class="text-xs text-slate-400 mt-0.5 flex items-center justify-between gap-2">
                                    <span class="truncate flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>
                                        {{ $to->tempat_tujuan }}
                                    </span>
                                    @if($to->tanggal_berangkat)
                                        <span class="font-bold text-slate-600 whitespace-nowrap">{{ $to->tanggal_berangkat->locale('id')->translatedFormat('d M') }}</span>
                                    @endif
                                </p>
                            </a>
                        @empty
                            <div class="h-full px-4 py-8 flex flex-col items-center justify-center text-center text-slate-400">
                                <i data-lucide="check-circle" class="w-7 h-7 mb-2 text-emerald-400"></i>
                                <p class="text-xs font-medium text-slate-500">Tidak ada pengajuan SPPD.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (1/3) --}}
        <div class="flex flex-col gap-6">

            {{-- Pengingat --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 bg-amber-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="bell-ring" class="w-4 h-4 text-amber-500"></i>
                        Pengingat
                    </h3>
                    @if(count($reminders))
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500 text-white">{{ count($reminders) }}</span>
                    @endif
                </div>
                <div class="p-3 space-y-2">
                    @forelse($reminders as $reminder)
                        @php $style = $reminderStyles[$reminder['color']] ?? $reminderStyles['amber']; @endphp
                        <a href="{{ $reminder['url'] }}" class="flex items-start gap-3 p-3 rounded-xl border {{ $style['border'] }} {{ $style['bg'] }} transition-colors group">
                            <div class="p-2 bg-white rounded-lg shadow-sm shrink-0">
                                <i data-lucide="{{ $reminder['icon'] }}" class="w-4 h-4 {{ $style['text'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-800 leading-snug">{{ $reminder['title'] }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $reminder['desc'] }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="px-4 py-8 text-center text-slate-400">
                            <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="shield-check" class="w-6 h-6 text-emerald-500"></i>
                            </div>
                            <p class="text-sm font-medium text-slate-600">Tidak ada pengingat.</p>
                            <p class="text-xs mt-1">Semua pekerjaan terkendali 👍</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Riwayat Aktivitas --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-500"></i>
                        Riwayat Aktivitas
                    </h3>
                </div>
                <div class="p-4 flex-1">
                    @if($recentActivities->isNotEmpty())
                        <ol class="relative border-l-2 border-slate-100 ml-4 space-y-5">
                            @foreach($recentActivities as $activity)
                                @php $style = $activityStyles[$activity['color']] ?? $activityStyles['indigo']; @endphp
                                <li class="relative pl-6">
                                    <span class="absolute -left-[17px] top-0 w-8 h-8 rounded-full {{ $style['bg'] }} ring-4 ring-white flex items-center justify-center">
                                        <i data-lucide="{{ $activity['icon'] }}" class="w-3.5 h-3.5 {{ $style['text'] }}"></i>
                                    </span>
                                    <a href="{{ $activity['url'] }}" class="block group">
                                        <p class="text-sm font-semibold text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-2">
                                            {{ $activity['title'] }}
                                        </p>
                                        <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $activity['desc'] }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            {{ $activity['time']?->locale('id')->diffForHumans() }}
                                        </p>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <div class="h-full py-8 flex flex-col items-center justify-center text-center text-slate-400">
                            <i data-lucide="inbox" class="w-8 h-8 mb-2 text-slate-300"></i>
                            <p class="text-sm font-medium text-slate-500">Belum ada aktivitas tercatat.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Donut serapan anggaran
        const realisasi = {{ (float) $realisasi }};
        const sisa = Math.max({{ (float) $sisaAnggaran }}, 0);
        const adaAnggaran = (realisasi + sisa) > 0;

        new Chart(document.getElementById('serapanChart'), {
            type: 'doughnut',
            data: {
                labels: ['Realisasi', 'Sisa'],
                datasets: [{
                    data: adaAnggaran ? [realisasi, sisa] : [1],
                    backgroundColor: adaAnggaran ? ['#10b981', '#e2e8f0'] : ['#e2e8f0'],
                    borderWidth: 0,
                }]
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: adaAnggaran,
                        callbacks: {
                            label: (ctx) => ctx.label + ': Rp ' + Number(ctx.raw).toLocaleString('id-ID'),
                        }
                    }
                }
            }
        });

        // Donut tahapan pengadaan penyedia
        const workflowData = @json(array_values($workflowStats));
        const adaWorkflow = workflowData.reduce((a, b) => a + b, 0) > 0;

        new Chart(document.getElementById('workflowChart'), {
            type: 'doughnut',
            data: {
                labels: {{ Js::from(array_column($workflowMeta, 'label')) }},
                datasets: [{
                    data: adaWorkflow ? workflowData : [1],
                    backgroundColor: adaWorkflow ? {{ Js::from(array_column($workflowMeta, 'hex')) }} : ['#e2e8f0'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: adaWorkflow }
                }
            }
        });
    });
</script>
@endcomponent
