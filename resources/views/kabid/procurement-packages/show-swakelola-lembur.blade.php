@component('layouts.kdmp')
@section('title', 'Swakelola Lembur')

@php
    $package = $procurementPackage->package;
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $statusBudgetClass = $lemburStats['sisa_anggaran'] < 0 ? 'text-rose-700 bg-rose-50 border-rose-100' : 'text-emerald-700 bg-emerald-50 border-emerald-100';
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $currentMonth = (int) now()->format('n');
    // Tahun lembur mengikuti tahun pembuatan paket (sama seperti OvertimeController).
    $overtimeYear = (int) ($package->created_at ? $package->created_at->format('Y') : now()->format('Y'));
    $currentYear = (int) now()->format('Y');
@endphp

<div class="space-y-6">
    <x-ui.toast />

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 min-w-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-sky-500"></i>
                {{ $package->id_rup ?? '-' }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg">
                <i data-lucide="handshake" class="w-3.5 h-3.5"></i>
                Swakelola
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-lg">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                Lembur
            </span>
        </div>
        <a href="{{ route('kabid.swakelola.index') }}"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    {{-- Ringkasan paket + penyerapan --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 lg:p-7 border-b border-slate-100 bg-slate-50/60">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">Paket Swakelola</p>
                    <h1 class="text-2xl font-black text-slate-900 leading-tight">{{ $package->nama_paket ?? '-' }}</h1>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-start gap-2 text-slate-600">
                            <i data-lucide="landmark" class="w-4 h-4 mt-0.5 text-slate-400 shrink-0"></i>
                            <span>{{ $package->program?->kode }} {{ $package->program?->nama ?? 'Program belum tersedia' }}</span>
                        </div>
                        <div class="flex items-start gap-2 text-slate-600">
                            <i data-lucide="briefcase" class="w-4 h-4 mt-0.5 text-slate-400 shrink-0"></i>
                            <span>{{ $package->activity?->kode }} {{ $package->activity ? '- ' . $package->activity->nama : 'Kegiatan belum tersedia' }}</span>
                        </div>
                        <div class="flex items-start gap-2 text-slate-600">
                            <i data-lucide="layers" class="w-4 h-4 mt-0.5 text-slate-400 shrink-0"></i>
                            <span>{{ $package->subActivity?->kode }} {{ $package->subActivity ? '- ' . $package->subActivity->nama : 'Sub kegiatan belum tersedia' }}</span>
                        </div>
                        <div class="flex items-start gap-2 text-slate-600">
                            <i data-lucide="wallet-cards" class="w-4 h-4 mt-0.5 text-slate-400 shrink-0"></i>
                            <span>{{ $package->account?->kode ?? '-' }} {{ $package->account?->nama ?? '' }}</span>
                        </div>
                    </div>
                </div>
                <div class="w-full lg:w-72">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-2">
                        <span>Penyerapan Anggaran</span>
                        <span class="{{ $lemburStats['percentage'] > 90 ? 'text-rose-600' : 'text-slate-700' }}">{{ number_format($lemburStats['percentage'], 1, ',', '.') }}%</span>
                    </div>
                    <div class="h-3 bg-white border border-slate-200 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $lemburStats['percentage'] > 90 ? 'bg-rose-500' : 'bg-slate-900' }}" style="width: {{ $lemburStats['percentage'] }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Realisasi {{ $money($lemburStats['total_realisasi']) }} dari pagu {{ $money($lemburStats['pagu']) }}.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Pagu</p>
                <p class="mt-2 text-xl font-black text-slate-900">{{ $money($lemburStats['pagu']) }}</p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Realisasi</p>
                <p class="mt-2 text-xl font-black text-slate-900">{{ $money($lemburStats['total_realisasi']) }}</p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Sisa Anggaran</p>
                <p class="mt-2 inline-flex px-2.5 py-1 rounded-lg border text-lg font-black {{ $statusBudgetClass }}">
                    {{ $money($lemburStats['sisa_anggaran']) }}
                </p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Bulan Terisi</p>
                <p class="mt-2 text-xl font-black text-slate-900">
                    {{ $lemburStats['bulan_terisi'] }} <span class="text-sm font-semibold text-slate-500">/ 12 bulan</span>
                </p>
            </div>
        </div>
    </section>

    {{-- Grid bulan lembur --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar-clock" class="w-4 h-4 text-amber-500"></i>
                    Pengelolaan Lembur Bulanan
                </h2>
                <p class="text-sm text-slate-500 mt-1">Pilih bulan untuk menginput jam lembur, rekap, dan cetak kwitansi.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Draft</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Terkunci</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span> Kosong</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-slate-200"></span> Belum dilewati</span>
            </div>
        </div>

        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
            @foreach($months as $num => $name)
                @php
                    $data = $lemburStats['months'][$num];
                    $exists = $data['exists'];
                    $locked = $data['is_locked'];
                    $isCurrent = $num === $currentMonth && $overtimeYear === $currentYear;
                    // Bulan belum dilewati = di masa depan (tahun berjalan & bulan > sekarang, atau tahun berikutnya).
                    $isFuture = $overtimeYear > $currentYear
                        || ($overtimeYear === $currentYear && $num > $currentMonth);
                @endphp
                <a @if(!$isFuture) href="{{ route('kabid.packages.overtimes.show', [$package, $num]) }}" @endif
                    @if($isFuture) aria-disabled="true" title="Bulan belum berjalan" @endif
                    class="group relative flex flex-col rounded-2xl border p-4 transition-all
                        {{ $isFuture
                            ? 'border-slate-200 bg-slate-50/70 opacity-60 cursor-not-allowed pointer-events-none'
                            : 'hover:-translate-y-0.5 hover:shadow-md ' . ($exists
                                ? ($locked ? 'border-emerald-200 bg-emerald-50/40 hover:border-emerald-300' : 'border-amber-200 bg-amber-50/40 hover:border-amber-300')
                                : 'border-slate-200 bg-white hover:border-slate-300') }}">

                    @if($isCurrent)
                        <span class="absolute top-2.5 right-2.5 inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wide bg-blue-100 text-blue-700 border border-blue-200">Bulan ini</span>
                    @endif

                    <div class="flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ $isFuture
                                ? 'bg-slate-100 text-slate-300'
                                : ($exists
                                    ? ($locked ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm shadow-emerald-200' : 'bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-sm shadow-amber-200')
                                    : 'bg-slate-100 text-slate-400') }}">
                            <i data-lucide="{{ $isFuture ? 'calendar-off' : ($exists ? ($locked ? 'lock' : 'calendar-check') : 'calendar') }}" class="w-5 h-5"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="font-black {{ $isFuture ? 'text-slate-400' : 'text-slate-900' }} leading-tight">{{ $name }}</p>
                            @if($isFuture)
                                <span class="text-[10px] font-bold uppercase tracking-wide text-slate-300">Belum dilewati</span>
                            @elseif($exists)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide {{ $locked ? 'text-emerald-600' : 'text-amber-600' }}">
                                    <i data-lucide="{{ $locked ? 'check-circle-2' : 'loader' }}" class="w-2.5 h-2.5"></i>
                                    {{ $locked ? 'Terkunci' : 'Draft' }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Belum ada</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-t {{ $isFuture ? 'border-slate-100' : ($exists ? ($locked ? 'border-emerald-100' : 'border-amber-100') : 'border-slate-100') }}">
                        @if($isFuture)
                            <p class="text-xs font-semibold text-slate-300 flex items-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i> Menunggu bulan berjalan
                            </p>
                        @elseif($exists)
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Realisasi</p>
                            <p class="text-sm font-black {{ $locked ? 'text-emerald-700' : 'text-amber-700' }}">{{ $money($data['total']) }}</p>
                        @else
                            <p class="text-xs font-semibold text-slate-400 flex items-center gap-1">
                                <i data-lucide="plus" class="w-3 h-3"></i> Input lembur
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endcomponent
