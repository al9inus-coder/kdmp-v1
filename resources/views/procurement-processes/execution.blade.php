@component('layouts.kdmp')
@section('title', 'Pelaksanaan Kontrak')

@php
    $package = $procurementPackage->package;

    $mulai = $process->tanggal_surat_pesanan?->copy()->startOfDay();
    $akhir = $process->tanggal_barang_diterima?->copy()->startOfDay();
    $hariIni = now()->startOfDay();

    $addendums = $procurementPackage->addendums->sortBy('created_at')->values();
    if ($addendums->isNotEmpty()) {
        $akhir = \Illuminate\Support\Carbon::parse($addendums->last()->tanggal_akhir_baru)->startOfDay();
    }

    $totalHari = ($mulai && $akhir) ? $mulai->diffInDays($akhir) + 1 : 1;

    $finished = in_array($procurementPackage->workflow_status, [
        \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
        \App\Models\ProcurementPackage::WORKFLOW_COMPLETED,
    ]);

    $berjalan = false;
    if ($finished) {
        $progress = 100;
        $statusText = 'Pekerjaan Selesai';
        $statusTone = 'emerald';
    } elseif (!$mulai || !$akhir) {
        $progress = 0;
        $statusText = 'Jadwal belum lengkap';
        $statusTone = 'slate';
    } elseif ($hariIni->lt($mulai)) {
        $progress = 0;
        $statusText = 'Belum Dimulai';
        $statusTone = 'slate';
    } elseif ($hariIni->gt($akhir)) {
        $progress = 100;
        $statusText = 'Melewati Batas Waktu';
        $statusTone = 'rose';
    } else {
        $berjalan = true;
        $hariKe = $mulai->diffInDays($hariIni) + 1;
        $progress = (int) round($hariKe / $totalHari * 100);
        $statusText = "Hari ke-{$hariKe} dari {$totalHari} hari";
        $statusTone = 'emerald';
    }

    $sisaHari = ($akhir && !$finished) ? $hariIni->diffInDays($akhir, false) : null;

    $toneText = ['emerald' => 'text-emerald-600', 'rose' => 'text-rose-600', 'slate' => 'text-slate-400'][$statusTone];
    $toneBar  = ['emerald' => 'from-emerald-400 to-emerald-600', 'rose' => 'from-rose-400 to-rose-600', 'slate' => 'from-slate-300 to-slate-400'][$statusTone];

    $kodeProgram = $package->program?->kode ?? '2.11.04';

    $adendumDates = $addendums->map(fn($a) => \Illuminate\Support\Carbon::parse($a->tanggal_akhir_baru)->format('Y-m-d'))->all();

    // ===== Alur Pelaksanaan (timeline) =====
    $nodes = [];

    $nodes[] = [
        'icon' => 'play', 'title' => 'Kontrak Dimulai',
        'sub' => $mulai?->locale('id')->translatedFormat('d F Y') ?? 'Jadwal belum diisi',
        'state' => ($mulai && $hariIni->gte($mulai)) ? 'done' : 'wait',
        'goto' => $mulai?->format('Y-m-d'),
    ];

    foreach ($addendums as $i => $add) {
        $nodes[] = [
            'icon' => 'file-clock', 'title' => 'Adendum ' . $add->nomor,
            'sub' => 'Batas akhir digeser ke ' . \Illuminate\Support\Carbon::parse($add->tanggal_akhir_baru)->locale('id')->translatedFormat('d F Y'),
            'note' => $add->alasan,
            'state' => 'adendum',
            'goto' => \Illuminate\Support\Carbon::parse($add->tanggal_akhir_baru)->format('Y-m-d'),
        ];
    }

    if ($berjalan) {
        $nodes[] = [
            'icon' => 'activity', 'title' => 'Hari Ini',
            'sub' => $statusText,
            'state' => 'running',
            'progress' => $progress,
            'goto' => $hariIni->format('Y-m-d'),
        ];
    }

    $nodes[] = [
        'icon' => 'flag', 'title' => 'Batas Akhir Kontrak',
        'sub' => ($akhir?->locale('id')->translatedFormat('d F Y') ?? '-')
            . ($sisaHari !== null ? ($sisaHari < 0 ? ' — terlambat ' . abs($sisaHari) . ' hari' : ($sisaHari === 0 ? ' — hari ini!' : ' — sisa ' . $sisaHari . ' hari')) : ''),
        'state' => $finished ? 'done' : (($akhir && $hariIni->gt($akhir)) ? 'late' : 'wait'),
        'goto' => $akhir?->format('Y-m-d'),
    ];

    $nodes[] = [
        'icon' => 'check-circle-2', 'title' => 'Pekerjaan Selesai',
        'sub' => $finished ? ('BAST No. ' . ($payment->nomor_bast ?? '-')) : 'Menunggu BAST & tagihan',
        'state' => $finished ? 'done' : 'wait',
        'goto' => $finished ? optional($payment?->tanggal_bast)->format('Y-m-d') : null,
    ];

    $pptkPrefill = $pptkPrefill ?? [
        'nama_pptk' => $procurementPackage->nama_pptk ?? '',
        'nip_pptk' => $procurementPackage->nip_pptk ?? '',
        'pangkat_golongan_pptk' => $procurementPackage->pangkat_golongan_pptk ?? '',
    ];
@endphp

<div class="space-y-6" x-data="kabidPelaksanaan({
        start: @js($mulai?->format('Y-m-d')),
        end: @js($akhir?->format('Y-m-d')),
        finished: {{ $finished ? 'true' : 'false' }},
        nonPkp: {{ old('is_non_pkp', $payment->is_non_pkp ?? false) ? 'true' : 'false' }},
        adendumDates: @js($adendumDates),
    })">
    <x-ui.toast />

    {{-- Identitas Paket --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-blue-500"></i>
                {{ $package->id_rup ?? '-' }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
                <i data-lucide="package" class="w-3.5 h-3.5 text-blue-500"></i>
                {{ $package->nama_paket }}
            </span>
        </div>
        <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-process.show', $package) }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Pemilihan
        </a>
    </div>

    {{-- Progress Workflow Pengadaan --}}
    <x-kabid.workflow-progress :procurement-package="$procurementPackage" />

    {{-- Strip status pelaksanaan --}}
    <div class="bg-white border {{ $finished ? 'border-emerald-200' : 'border-slate-200' }} shadow-sm rounded-2xl px-5 py-4 flex flex-col lg:flex-row items-center gap-5">
        <div class="flex items-center gap-4 shrink-0">
            <p class="text-4xl font-extrabold {{ $toneText }}">{{ $progress }}%</p>
            <div>
                <p class="text-sm font-bold text-slate-700">{{ $statusText }}</p>
                <p class="text-[11px] font-semibold text-slate-400">
                    {{ $mulai?->locale('id')->translatedFormat('d M Y') ?? '-' }} &rarr; {{ $akhir?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                    &bull; {{ $totalHari }} hari
                </p>
            </div>
        </div>
        <div class="flex-1 w-full">
            <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r {{ $toneBar }} {{ $berjalan ? 'kdmp-shimmer-exec' : '' }} transition-all duration-700"
                    style="width: {{ $progress }}%;"></div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if(!$finished && $sisaHari !== null)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border
                    {{ $sisaHari < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : ($sisaHari <= 3 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                    <i data-lucide="{{ $sisaHari < 0 ? 'alarm-clock-off' : 'alarm-clock' }}" class="w-3.5 h-3.5"></i>
                    {{ $sisaHari < 0 ? 'Terlambat ' . abs($sisaHari) . ' hari' : ($sisaHari === 0 ? 'Batas akhir hari ini!' : 'Sisa ' . $sisaHari . ' hari') }}
                </span>
            @endif
            @if(!$finished)
                @hasanyrole('Kabid')
                    <button type="button" @click="showAdendum = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition-colors">
                        <i data-lucide="file-clock" class="w-3.5 h-3.5"></i> Adendum
                    </button>
                    <button type="button" @click="showSelesai = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-lg shadow-sm shadow-emerald-200 transition-colors">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Pekerjaan Selesai
                    </button>
                @else
                    <span class="text-xs text-slate-400 font-semibold italic">Melihat Tahap Pelaksanaan Kontrak</span>
                @endhasanyrole
            @else
                <a href="{{ auth()->user()->hasRole(['Admin', 'Super Admin']) ? route('admin.procurement-packages.payment', $package) : route('kabid.procurement-packages.payment.show', $package) }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-lg shadow-sm shadow-emerald-200 transition-colors">
                    Lanjut ke Pembayaran <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </div>
    </div>

    {{-- Kalender besar (kiri) + Alur Pelaksanaan (kanan) --}}
    <div class="flex flex-col-reverse lg:flex-row gap-6 items-start">

        {{-- Kolom kiri: kalender + ringkasan kontrak --}}
        <div class="flex-1 w-full min-w-0 space-y-6">

        {{-- Kalender pelaksanaan --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-2">
                <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                    <i data-lucide="calendar-days" class="w-4 h-4 text-blue-500"></i>
                    Kalender Pelaksanaan
                </h3>
                {{-- Lompat cepat --}}
                <div class="flex items-center gap-1.5">
                    @if($mulai)
                        <button type="button" @click="gotoDate('{{ $mulai->format('Y-m-d') }}')"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors">Mulai</button>
                    @endif
                    <button type="button" @click="gotoDate(todayIso)"
                        class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-colors">Hari Ini</button>
                    @if($akhir)
                        <button type="button" @click="gotoDate('{{ $akhir->format('Y-m-d') }}')"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition-colors">Akhir</button>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-6 select-none">
                {{-- Navigasi --}}
                <div class="flex items-center justify-between mb-3 gap-2">
                    <button type="button" @click="prevMonth()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors shrink-0">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-8 text-center">
                        <p class="text-lg font-extrabold text-slate-800" x-text="monthLabel"></p>
                        <p class="text-lg font-extrabold text-slate-800 hidden md:block" x-text="monthLabelNext"></p>
                    </div>
                    <button type="button" @click="nextMonth()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors shrink-0">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    {{-- Bulan berjalan --}}
                    <div>
                        <div class="grid grid-cols-7 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">
                            <template x-for="h in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']"><span x-text="h" class="py-1.5"></span></template>
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            <template x-for="(cell, idx) in cells" :key="'a' + idx">
                                <div>
                                    <button type="button" x-show="cell.d" @click="pickDate(cell.iso)"
                                        class="w-full h-11 sm:h-12 rounded-xl text-sm font-semibold transition-all flex flex-col items-center justify-center leading-none gap-1"
                                        :class="cellClass(cell.iso)">
                                        <span x-text="cell.d"></span>
                                        <span class="flex items-center gap-0.5">
                                            <span x-show="isAdendum(cell.iso)" class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        </span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Bulan berikutnya --}}
                    <div class="hidden md:block">
                        <div class="grid grid-cols-7 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wide mb-1">
                            <template x-for="h in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']"><span x-text="h" class="py-1.5"></span></template>
                        </div>
                        <div class="grid grid-cols-7 gap-1">
                            <template x-for="(cell, idx) in cellsNext" :key="'b' + idx">
                                <div>
                                    <button type="button" x-show="cell.d" @click="pickDate(cell.iso)"
                                        class="w-full h-11 sm:h-12 rounded-xl text-sm font-semibold transition-all flex flex-col items-center justify-center leading-none gap-1"
                                        :class="cellClass(cell.iso)">
                                        <span x-text="cell.d"></span>
                                        <span class="flex items-center gap-0.5">
                                            <span x-show="isAdendum(cell.iso)" class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        </span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 mt-4 pt-4 border-t border-dashed border-slate-200">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full ring-2 ring-inset ring-amber-400 bg-amber-50"></span> Hari ini
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-emerald-600"></span> Mulai kontrak
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span> Akhir kontrak
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-emerald-100"></span> Masa pelaksanaan
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Adendum
                    </span>
                    @hasanyrole('Kabid')
                        @if(!$finished)
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-400">
                                <i data-lucide="mouse-pointer-click" class="w-3 h-3"></i> Klik tanggal untuk aksi
                            </span>
                        @endif
                    @endhasanyrole
                </div>
            </div>
        </div>

        {{-- Ringkasan kontrak --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 divide-x divide-y sm:divide-y xl:divide-y-0 divide-slate-100">
                <div class="p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Penyedia</p>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                            <i data-lucide="store" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $process->nama_penyedia ?? '-' }}</p>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nilai Kontrak</p>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-sm font-bold text-emerald-600 truncate">Rp {{ number_format((float) $process->nilai_kontrak, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">No. Surat Pesanan</p>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded bg-violet-100 flex items-center justify-center text-violet-600 shrink-0">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-xs font-semibold text-slate-700 font-mono truncate" title="{{ $process->nomor_surat_pesanan }}">{{ $process->nomor_surat_pesanan ?? '-' }}</p>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jenis Kontrak</p>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                            <i data-lucide="file-signature" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $procurementPackage->jenis_kontrak ?? '-' }}</p>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">PPK</p>
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-6 h-6 rounded bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0">
                            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $procurementPackage->nama_ppk ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        </div>

        {{-- Alur Pelaksanaan --}}
        <aside class="w-full lg:w-72 shrink-0 lg:sticky lg:top-20">
            <div class="flex items-center justify-between mb-1 px-1">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Alur Pelaksanaan</p>
                <span class="text-[11px] font-bold {{ $finished ? 'text-emerald-600' : $toneText }}">{{ $progress }}%</span>
            </div>
            <div class="h-1 rounded-full bg-slate-200/70 overflow-hidden mb-4 mx-1">
                <div class="h-full rounded-full bg-gradient-to-r {{ $toneBar }} transition-all duration-700" style="width: {{ $progress }}%;"></div>
            </div>

            <ol class="relative space-y-0.5">
                @foreach($nodes as $index => $node)
                    @php
                        $state = $node['state'];
                        $circle = match($state) {
                            'done'    => 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm shadow-emerald-200',
                            'running' => 'bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-md shadow-amber-200 ring-2 ring-amber-200',
                            'adendum' => 'bg-gradient-to-br from-amber-300 to-orange-400 text-white shadow-sm shadow-amber-200',
                            'late'    => 'bg-gradient-to-br from-rose-400 to-rose-600 text-white shadow-sm shadow-rose-200',
                            default   => 'bg-white border-2 border-slate-200 text-slate-400',
                        };
                        $titleColor = match($state) {
                            'done' => 'text-emerald-700', 'running' => 'text-slate-900',
                            'adendum' => 'text-amber-700', 'late' => 'text-rose-700',
                            default => 'text-slate-500',
                        };
                        $lineColor = in_array($state, ['done','adendum']) ? 'bg-emerald-300' : 'bg-slate-200';
                    @endphp
                    <li class="relative">
                        @if($index < count($nodes) - 1)
                            <span class="absolute left-[21px] top-11 h-[calc(100%-2.25rem)] w-px z-0 {{ $lineColor }}"></span>
                        @endif
                        <button type="button" {{ $node['goto'] ? '' : 'disabled' }}
                            @if($node['goto']) @click="gotoDate('{{ $node['goto'] }}')" @endif
                            class="relative w-full flex items-start gap-3 py-2 pl-1.5 pr-3 rounded-xl text-left border border-transparent transition-all duration-200
                                {{ $node['goto'] ? 'hover:bg-white/70 hover:translate-x-0.5 cursor-pointer' : 'cursor-default' }}"
                            @if($node['goto']) title="Lihat di kalender" @endif>
                            <span class="relative shrink-0 mt-0.5">
                                @if($state === 'running')
                                    <span class="absolute inset-0 rounded-full bg-amber-400 opacity-30 animate-ping"></span>
                                @endif
                                <span class="relative flex items-center justify-center w-8 h-8 rounded-full transition-all duration-200 {{ $circle }}">
                                    @if($state === 'done')
                                        <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                                    @else
                                        <i data-lucide="{{ $node['icon'] }}" class="w-3.5 h-3.5"></i>
                                    @endif
                                </span>
                            </span>
                            <span class="leading-tight min-w-0 flex-1">
                                <span class="block text-sm font-bold {{ $titleColor }} truncate">{{ $node['title'] }}</span>
                                <span class="block text-[11px] font-semibold text-slate-400 mt-0.5 leading-snug">{{ $node['sub'] }}</span>
                                @if(isset($node['note']))
                                    <span class="block text-[10px] text-slate-400 italic mt-0.5 leading-snug">{{ Str::limit($node['note'], 60) }}</span>
                                @endif
                                @if($state === 'running')
                                    <span class="block h-1 rounded-full bg-amber-100 overflow-hidden mt-1.5">
                                        <span class="block h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500" style="width: {{ $node['progress'] }}%;"></span>
                                    </span>
                                @endif
                            </span>
                            @if($node['goto'])
                                <i data-lucide="calendar-search" class="w-3.5 h-3.5 text-slate-300 shrink-0 mt-1"></i>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ol>

            @if($finished)
                <div class="mt-3 mx-1 flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100">
                    <i data-lucide="party-popper" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                    <p class="text-xs font-semibold text-emerald-700">Pelaksanaan kontrak selesai.</p>
                </div>
            @endif
        </aside>
    </div>

    {{-- Modals only render if user has Kabid role to prevent unauthorized access and keep DOM light --}}
    @hasanyrole('Kabid')
        {{-- ============ MODAL PILIH AKSI ============ --}}
        <div x-show="showAksi" style="display: none;"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            @keydown.escape.window="showAksi = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAksi = false"></div>
            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden text-center p-6"
                x-transition:enter="transition ease-out duration-200 delay-75"
                x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                <p class="text-sm font-bold text-slate-800">Pilih tindakan untuk tanggal</p>
                <p class="text-lg font-extrabold text-emerald-600 mt-1" x-text="displayDate(selectedDate)"></p>
                <div class="grid grid-cols-2 gap-3 mt-5">
                    <button type="button" @click="showAksi = false; showAdendum = true"
                        class="flex flex-col items-center gap-2 p-4 rounded-xl border border-amber-200 bg-amber-50/60 hover:bg-amber-50 text-amber-700 font-bold text-sm transition-colors">
                        <i data-lucide="file-clock" class="w-7 h-7"></i>
                        Adendum Kontrak
                    </button>
                    <button type="button" @click="showAksi = false; showSelesai = true"
                        class="flex flex-col items-center gap-2 p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 hover:bg-emerald-50 text-emerald-700 font-bold text-sm transition-colors">
                        <i data-lucide="check-circle-2" class="w-7 h-7"></i>
                        Pekerjaan Selesai
                    </button>
                </div>
            </div>
        </div>

        {{-- ============ MODAL ADENDUM ============ --}}
        <div x-show="showAdendum" style="display: none;"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            @keydown.escape.window="showAdendum = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showAdendum = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                x-transition:enter="transition ease-out duration-200 delay-75"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <form method="POST" action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.execution.addendum', $package) }}">
                    @csrf
                    <div class="px-5 py-4 border-b border-slate-100 bg-amber-50/60 flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-600">
                                <i data-lucide="file-clock" class="w-4 h-4"></i>
                            </span>
                            Adendum Kontrak
                        </h3>
                        <button type="button" @click="showAdendum = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor Adendum</label>
                            <input type="text" name="nomor" required placeholder="Contoh: ADD-01/SP/2026"
                                class="w-full rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Akhir Kontrak (Baru)</label>
                            <input type="date" name="tanggal_akhir_baru" required x-model="selectedDate"
                                class="w-full rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                            <p class="text-[11px] text-slate-400 mt-1">Batas akhir saat ini: <strong>{{ $akhir?->locale('id')->translatedFormat('d F Y') ?? '-' }}</strong></p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alasan Adendum</label>
                            <textarea name="alasan" rows="3" required placeholder="Masukkan alasan adendum..."
                                class="w-full rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm"></textarea>
                        </div>
                    </div>
                    <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-stretch justify-end gap-2">
                        <button type="button" @click="showAdendum = false"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl shadow-md shadow-amber-200 transition-all whitespace-nowrap">
                            <i data-lucide="save" class="w-4 h-4 shrink-0"></i>
                            Simpan Adendum
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============ MODAL PEKERJAAN SELESAI ============ --}}
        <div x-show="showSelesai" style="display: none;"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            @keydown.escape.window="showSelesai = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showSelesai = false"></div>
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col" style="max-height: 90vh;"
                x-transition:enter="transition ease-out duration-200 delay-75"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <form method="POST" action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.execution.finish', $package) }}" class="flex flex-col min-h-0">
                    @csrf
                    <div class="px-5 py-4 border-b border-slate-100 bg-emerald-50/60 flex items-center justify-between shrink-0">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-100 border border-emerald-200 flex items-center justify-center text-emerald-600">
                                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            </span>
                            Penyelesaian Pekerjaan &amp; Tagihan
                        </h3>
                        <button type="button" @click="showSelesai = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-5 overflow-y-auto min-h-0">
                        <div class="flex items-start gap-2.5 px-3.5 py-2.5 rounded-xl bg-blue-50/60 border border-blue-100">
                            <i data-lucide="info" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5"></i>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Menyimpan form ini akan memindahkan paket ke tahap <strong>Pembayaran</strong> dan mengunci data pelaksanaan.
                            </p>
                        </div>

                        {{-- BAST & Invoice --}}
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5">BAST &amp; Invoice</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor BAST</label>
                                    <input type="text" name="nomor_bast" required value="{{ old('nomor_bast', $payment->nomor_bast ?? '') }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal BAST</label>
                                    <input type="date" name="tanggal_bast" required x-model="selectedDate"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Invoice</label>
                                    <input type="text" name="nomor_invoice" required value="{{ old('nomor_invoice', $payment->nomor_invoice ?? '') }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Invoice</label>
                                    <input type="date" name="tanggal_invoice" required value="{{ old('tanggal_invoice', optional($payment?->tanggal_invoice)->format('Y-m-d')) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- BAP & Kwitansi --}}
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5">BAP &amp; Kwitansi</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor BAP <span class="font-normal text-slate-400">(angka saja)</span></label>
                                    <div class="flex">
                                        <input type="number" name="nomor_bap" required value="{{ old('nomor_bap', $payment->nomor_bap ?? '') }}"
                                            class="w-24 rounded-l-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                        <span class="inline-flex items-center px-2.5 rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 text-[10px] font-bold text-slate-500 whitespace-nowrap">/BAP/{{ $kodeProgram }}/PERKIMPLH-C</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal BAP</label>
                                    <input type="date" name="tanggal_bap" required value="{{ old('tanggal_bap', optional($payment?->tanggal_bap)->format('Y-m-d')) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Kwitansi <span class="font-normal text-slate-400">(angka saja)</span></label>
                                    <div class="flex">
                                        <input type="number" name="nomor_kwitansi" required value="{{ old('nomor_kwitansi', $payment->nomor_kwitansi ?? '') }}"
                                            class="w-24 rounded-l-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                        <span class="inline-flex items-center px-2.5 rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 text-[10px] font-bold text-slate-500 whitespace-nowrap">/KWT/{{ $kodeProgram }}/PERKIMPLH-C</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Kwitansi</label>
                                    <input type="date" name="tanggal_kwitansi" required value="{{ old('tanggal_kwitansi', optional($payment?->tanggal_kwitansi)->format('Y-m-d')) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- PPTK --}}
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5">Data PPTK <span class="font-normal normal-case text-slate-400">(untuk BAP — terisi dari master)</span></p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama PPTK</label>
                                    <input type="text" name="nama_pptk" required value="{{ old('nama_pptk', $pptkPrefill['nama_pptk']) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">NIP PPTK</label>
                                    <input type="text" name="nip_pptk" required value="{{ old('nip_pptk', $pptkPrefill['nip_pptk']) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pangkat / Golongan</label>
                                    <input type="text" name="pangkat_golongan_pptk" required value="{{ old('pangkat_golongan_pptk', $pptkPrefill['pangkat_golongan_pptk']) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Dokumen tambahan --}}
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2.5">Dokumen Tambahan</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-end">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Ringkasan Kontrak</label>
                                    <input type="date" name="tanggal_ringkasan_kontrak" required value="{{ old('tanggal_ringkasan_kontrak', optional($payment?->tanggal_ringkasan_kontrak)->format('Y-m-d')) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                                <div>
                                    <label class="flex items-center gap-2 px-3 py-2.5 rounded-lg border cursor-pointer text-sm font-semibold transition-colors"
                                        :class="nonPkp ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
                                        <input type="checkbox" name="is_non_pkp" value="1" x-model="nonPkp"
                                            class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                        Lampirkan Surat Non-PKP
                                    </label>
                                </div>
                                <div x-show="nonPkp" x-transition.opacity style="display: none;">
                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Surat Non-PKP</label>
                                    <input type="date" name="tanggal_non_pkp" :required="nonPkp" :disabled="!nonPkp"
                                        value="{{ old('tanggal_non_pkp', optional($payment?->tanggal_non_pkp)->format('Y-m-d')) }}"
                                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-stretch justify-end gap-2 shrink-0">
                        <button type="button" @click="showSelesai = false"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all whitespace-nowrap">
                            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
                            Simpan &amp; Lanjut Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endhasanyrole
</div>

<style>
    @keyframes kdmp-shimmer-exec-kf { 0% { filter: brightness(1); } 50% { filter: brightness(1.25); } 100% { filter: brightness(1); } }
    .kdmp-shimmer-exec { animation: kdmp-shimmer-exec-kf 2s ease-in-out infinite; }
</style>

<script>
    function kabidPelaksanaan(config) {
        const toIso = (d) => {
            const p = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
        };
        const fromIso = (s) => new Date(s + 'T00:00:00');
        const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        const anchor = config.start ? fromIso(config.start) : new Date();

        return {
            start: config.start,
            end: config.end,
            finished: config.finished,
            nonPkp: config.nonPkp,
            adendumDates: config.adendumDates || [],
            showAksi: false,
            showAdendum: false,
            showSelesai: false,
            selectedDate: toIso(new Date()),
            viewYear: anchor.getFullYear(),
            viewMonth: anchor.getMonth(),
            todayIso: toIso(new Date()),

            buildCells(year, month) {
                const first = new Date(year, month, 1);
                const total = new Date(year, month + 1, 0).getDate();
                const cells = [];
                for (let i = 0; i < first.getDay(); i++) cells.push({ d: null, iso: null });
                for (let d = 1; d <= total; d++) {
                    cells.push({ d, iso: toIso(new Date(year, month, d)) });
                }
                return cells;
            },
            get monthLabel() {
                return bulan[this.viewMonth] + ' ' + this.viewYear;
            },
            get cells() {
                return this.buildCells(this.viewYear, this.viewMonth);
            },
            // Bulan kedua (jendela 2 bulan): bulan setelah viewMonth.
            get nextYearMonth() {
                return this.viewMonth === 11
                    ? { y: this.viewYear + 1, m: 0 }
                    : { y: this.viewYear, m: this.viewMonth + 1 };
            },
            get monthLabelNext() {
                const n = this.nextYearMonth;
                return bulan[n.m] + ' ' + n.y;
            },
            get cellsNext() {
                const n = this.nextYearMonth;
                return this.buildCells(n.y, n.m);
            },

            prevMonth() {
                if (--this.viewMonth < 0) { this.viewMonth = 11; this.viewYear--; }
            },
            nextMonth() {
                if (++this.viewMonth > 11) { this.viewMonth = 0; this.viewYear++; }
            },
            gotoDate(iso) {
                if (!iso) return;
                const d = fromIso(iso);
                const target = d.getFullYear() * 12 + d.getMonth();
                const current = this.viewYear * 12 + this.viewMonth;
                // Sudah terlihat di salah satu dari 2 bulan — tidak perlu pindah.
                if (target === current || target === current + 1) return;
                this.viewYear = d.getFullYear();
                this.viewMonth = d.getMonth();
            },

            isAdendum(iso) {
                return this.adendumDates.includes(iso);
            },

            pickDate(iso) {
                if (this.finished || !{{ auth()->user()->hasRole('Kabid') ? 'true' : 'false' }}) return;
                this.selectedDate = iso;
                this.showAksi = true;
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            },

            cellClass(iso) {
                const isToday = iso === this.todayIso;

                if (iso === this.start) {
                    return 'bg-emerald-600 text-white shadow-sm shadow-emerald-200 font-bold' +
                        (isToday ? ' ring-2 ring-offset-1 ring-amber-400' : '');
                }
                if (iso === this.end) {
                    return 'bg-indigo-600 text-white shadow-sm shadow-indigo-200 font-bold' +
                        (isToday ? ' ring-2 ring-offset-1 ring-amber-400' : '');
                }
                if (this.start && this.end && iso > this.start && iso < this.end) {
                    return 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' +
                        (isToday ? ' ring-2 ring-inset ring-amber-400 font-bold' : '');
                }
                if (isToday) {
                    return 'ring-2 ring-inset ring-amber-400 bg-amber-50 text-amber-700 font-bold hover:bg-amber-100';
                }
                return 'text-slate-600 hover:bg-slate-100';
            },

            displayDate(iso) {
                if (!iso) return '-';
                const d = fromIso(iso);
                return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
            },
        };
    }
</script>
@endcomponent
