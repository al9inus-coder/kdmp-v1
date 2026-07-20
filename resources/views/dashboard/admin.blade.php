@component('layouts.kdmp')
@section('title', 'Admin Dashboard')

@php
    $workflowSegments = [
        'draft'              => ['label' => 'Draft',               'bar' => 'bg-slate-400',   'dot' => 'bg-slate-400',   'text' => 'text-slate-600'],
        'provider_selection' => ['label' => 'Pemilihan Penyedia',  'bar' => 'bg-amber-400',   'dot' => 'bg-amber-500',   'text' => 'text-amber-600'],
        'execution'          => ['label' => 'Pelaksanaan',         'bar' => 'bg-blue-400',    'dot' => 'bg-blue-500',    'text' => 'text-blue-600'],
        'payment_process'    => ['label' => 'Proses Pembayaran',   'bar' => 'bg-violet-400',  'dot' => 'bg-violet-500',  'text' => 'text-violet-600'],
        'completed'          => ['label' => 'Selesai',             'bar' => 'bg-emerald-400', 'dot' => 'bg-emerald-500', 'text' => 'text-emerald-600'],
    ];
    $workflowTotal = max(array_sum($statusDistribution), 1);
@endphp

<div class="space-y-6">
    <x-ui.toast />

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-amber-500 via-amber-600 to-orange-600 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-16 right-24 w-40 h-40 bg-white/10 rounded-full"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <p class="text-xs font-semibold text-amber-100 uppercase tracking-wider">
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                </p>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mt-1.5">
                    Dashboard Admin
                </h1>
                <p class="text-sm text-amber-100 mt-2">
                    Pantau seluruh aktivitas pengadaan dan realisasi anggaran secara real-time.
                </p>
            </div>

            {{-- Selector Tahun Anggaran (glass) --}}
            <form method="GET" action="{{ route('dashboard.admin') }}" class="flex items-center gap-3">
                <label class="text-sm font-semibold text-amber-100 hidden sm:block">Tahun Anggaran</label>
                <div class="relative">
                    <select name="fiscal_year_id" onchange="this.form.submit()"
                        class="w-full sm:w-44 pl-4 pr-10 py-2.5 rounded-xl bg-white/20 backdrop-blur-md border border-white/30 text-sm font-bold text-white focus:ring-2 focus:ring-white/60 focus:border-white/60 shadow-sm transition-shadow appearance-none [&>option]:text-slate-800">
                        @foreach($fiscalYears as $year)
                            <option value="{{ $year->id }}" {{ $fiscalYearId == $year->id ? 'selected' : '' }}>
                                {{ $year->tahun }} {{ $year->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i data-lucide="chevron-down" class="w-4 h-4 text-white/80"></i>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bento Grid Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Total Pagu -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 rounded-full blur-3xl group-hover:bg-blue-100 transition-colors"></div>

            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100">
                        <i data-lucide="wallet" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-500 tracking-wider uppercase">Total Pagu</span>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 mb-2">Rp {{ number_format($totalPagu, 0, ',', '.') }}</h2>
                    <p class="text-sm font-medium text-slate-500 flex items-center gap-1.5">
                        <span class="inline-flex items-center justify-center bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-xs font-bold">{{ $totalPackages }}</span> Paket Pengadaan
                    </p>
                </div>
            </div>
        </div>

        <!-- Realisasi -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-emerald-50 rounded-full blur-3xl group-hover:bg-emerald-100 transition-colors"></div>

            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center border border-emerald-100">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-500 tracking-wider uppercase">Realisasi</span>
                </div>
                <div>
                    <h3 class="text-3xl font-black text-slate-800 mb-2">Rp {{ number_format($realizedBudget, 0, ',', '.') }}</h3>
                    <p class="text-sm font-medium text-slate-500 flex items-center gap-1.5">
                        <span class="inline-flex items-center justify-center bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-xs font-bold">{{ $completedCount }}</span> Paket Selesai
                    </p>
                </div>
            </div>
        </div>

        <!-- Persentase Serapan -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
            <div class="absolute bottom-0 left-0 w-full h-1 bg-slate-100">
                <div class="h-full bg-amber-500" style="width: {{ $absorptionPercentage }}%"></div>
            </div>
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-amber-50 rounded-full blur-3xl group-hover:bg-amber-100 transition-colors"></div>

            <div class="relative z-10 flex flex-col h-full justify-between">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center border border-amber-100">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <span class="text-sm font-semibold text-slate-500 tracking-wider uppercase">Penyerapan</span>
                </div>
                <div>
                    <div class="flex items-end gap-2 mb-2">
                        <h3 class="text-4xl font-black text-slate-800">{{ $absorptionPercentage }}</h3>
                        <span class="text-lg font-bold text-slate-500 mb-1.5">%</span>
                    </div>
                    <p class="text-sm font-medium text-slate-500 flex items-center gap-1.5">
                        Realisasi dari total pagu anggaran
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Distribusi Workflow & Jenis Pengadaan -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Workflow stacked bar --}}
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="git-branch" class="w-5 h-5 text-slate-400"></i>
                        Progres Workflow Pengadaan
                    </h3>
                    <span class="text-xs text-slate-400">{{ array_sum($statusDistribution) }} paket</span>
                </div>

                @if(array_sum($statusDistribution) > 0)
                    <div class="flex h-3 rounded-full overflow-hidden bg-slate-100">
                        @foreach($workflowSegments as $status => $seg)
                            @php $count = $statusDistribution[$status] ?? 0; @endphp
                            @if($count > 0)
                                <div class="{{ $seg['bar'] }} first:rounded-l-full last:rounded-r-full"
                                    style="width: {{ $count / $workflowTotal * 100 }}%"
                                    title="{{ $seg['label'] }}: {{ $count }} paket"></div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="h-3 rounded-full bg-slate-100"></div>
                @endif

                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-4">
                    @foreach($workflowSegments as $status => $seg)
                        @php $count = $statusDistribution[$status] ?? 0; @endphp
                        <span class="flex items-center gap-1.5 text-xs font-semibold {{ $seg['text'] }}">
                            <span class="w-2 h-2 rounded-full {{ $seg['dot'] }}"></span>
                            {{ $seg['label'] }}
                            <span class="text-slate-400 font-medium">&middot; {{ $count }}</span>
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Jenis pengadaan --}}
            <div class="lg:border-l lg:border-slate-100 lg:pl-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="shapes" class="w-5 h-5 text-slate-400"></i>
                        Jenis Pengadaan
                    </h3>
                </div>
                @if(count($jenisPengadaanDistribution))
                    <div class="flex flex-wrap gap-2">
                        @foreach($jenisPengadaanDistribution as $jenis => $count)
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-200">
                                {{ $jenis ?: 'Tidak diisi' }}
                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">{{ $count }}</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400">Belum ada data jenis pengadaan.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Secondary Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Warning System (Late Packages) -->
        <div class="lg:col-span-2 flex flex-col">
            <x-ui.card padding="none" class="h-full flex flex-col">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-white rounded-t-xl">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-500"></i>
                        Perhatian: Paket Terlambat / Belum Mulai
                    </h3>
                </div>
                <div class="p-0 flex-1">
                    @if($latePackages->isEmpty())
                        <div class="p-8 h-full flex flex-col items-center justify-center text-slate-500">
                            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500"></i>
                            </div>
                            <p class="font-medium">Semua paket berjalan sesuai jadwal target.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 max-h-[400px] overflow-y-auto">
                            @foreach($latePackages as $p)
                                <div class="p-4 hover:bg-slate-50 transition-colors flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0 mt-1">
                                        <i data-lucide="clock" class="w-5 h-5 text-rose-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-4">
                                            <p class="text-sm font-bold text-slate-800 truncate">{{ $p->nama_paket }}</p>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600 whitespace-nowrap">
                                                Target: Bulan {{ $p->pemilihan_mulai_bulan }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $p->subActivity->nama ?? '-' }}</p>
                                        <div class="flex items-center gap-3 mt-2">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $p->procurementPackage?->workflow_status === 'draft' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700' }}">
                                                {{ ucfirst($p->procurementPackage?->workflow_status ?? 'draft') }}
                                            </span>
                                            <a href="{{ $p->procurementPackage ? route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.show', $p) : route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Tindak Lanjut &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <!-- Recent Activities -->
        <div class="lg:col-span-1 flex flex-col">
            <x-ui.card padding="none" class="h-full flex flex-col">
                <div class="p-5 border-b border-slate-100 bg-white rounded-t-xl">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="activity" class="w-5 h-5 text-amber-500"></i>
                        Aktivitas Terbaru
                    </h3>
                </div>
                <div class="p-5 flex-1">
                    @if($recentActivities->isEmpty())
                        <p class="text-sm text-slate-500 text-center py-8">Belum ada aktivitas.</p>
                    @else
                        <div class="relative border-l-2 border-slate-100 ml-3 space-y-6">
                            @foreach($recentActivities as $activity)
                                <div class="relative pl-6">
                                    <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full bg-white border-2 border-amber-500 shadow-sm"></div>
                                    <p class="text-xs font-bold text-slate-400 mb-1">{{ $activity->updated_at->locale('id')->diffForHumans() }}</p>
                                    <p class="text-sm font-semibold text-slate-800 line-clamp-2">{{ $activity->package->nama_paket }}</p>
                                    <p class="text-xs text-slate-500 mt-1">Status diperbarui ke <span class="font-bold text-slate-700">{{ strtoupper($activity->workflow_status) }}</span></p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

    </div>
</div>
@endcomponent
