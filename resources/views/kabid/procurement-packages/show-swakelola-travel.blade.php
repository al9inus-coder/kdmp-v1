@component('layouts.kdmp')
@section('title', 'Swakelola Perjalanan Dinas')

@php
    $package = $procurementPackage->package;
    // Hanya perjalanan dinas dengan SPJ (biaya rampung) yang sudah disetujui yang muncul di sini.
    // Pengajuan/tinjauan SPPD & SPJ dilakukan di menu Pengajuan SPPD.
    $travelOrders = $package->travelOrders
        ->filter(fn ($to) => $to->spjStatus() === \App\Models\TravelOrder::SPJ_APPROVED)
        ->values();
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $statusBudgetClass = $travelStats['sisa_anggaran'] < 0 ? 'text-rose-700 bg-rose-50 border-rose-100' : 'text-emerald-700 bg-emerald-50 border-emerald-100';
@endphp

<div class="space-y-6">
    <x-ui.toast />

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
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg">
                <i data-lucide="plane" class="w-3.5 h-3.5"></i>
                Perjalanan Dinas
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('kabid.procurement-packages.index', ['type' => 'swakelola']) }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>
    </div>

    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 lg:p-7 border-b border-slate-100 bg-slate-50/60">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">Paket Swakelola</p>
                    <h1 class="text-2xl font-black text-slate-900 leading-tight">
                        {{ $package->nama_paket ?? '-' }}
                    </h1>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-start gap-2 text-slate-600">
                            <i data-lucide="landmark" class="w-4 h-4 mt-0.5 text-slate-400 shrink-0"></i>
                            <span>{{ $package->program?->nama ?? 'Program belum tersedia' }}</span>
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
                        <span>{{ number_format($travelStats['percentage'], 1, ',', '.') }}%</span>
                    </div>
                    <div class="h-3 bg-white border border-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-slate-900 rounded-full" style="width: {{ $travelStats['percentage'] }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        Realisasi {{ $money($travelStats['total_realisasi']) }} dari pagu {{ $money($travelStats['pagu']) }}.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Pagu</p>
                <p class="mt-2 text-xl font-black text-slate-900">{{ $money($travelStats['pagu']) }}</p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Realisasi</p>
                <p class="mt-2 text-xl font-black text-slate-900">{{ $money($travelStats['total_realisasi']) }}</p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Sisa Anggaran</p>
                <p class="mt-2 inline-flex px-2.5 py-1 rounded-lg border text-lg font-black {{ $statusBudgetClass }}">
                    {{ $money($travelStats['sisa_anggaran']) }}
                </p>
            </div>
            <div class="p-5">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Dokumen Perjalanan</p>
                <p class="mt-2 text-xl font-black text-slate-900">
                    {{ $travelStats['total_orders'] }} <span class="text-sm font-semibold text-slate-500">SPT</span>
                    <span class="mx-1 text-slate-300">/</span>
                    {{ $travelStats['total_personnels'] }} <span class="text-sm font-semibold text-slate-500">pegawai</span>
                </p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="route" class="w-4 h-4 text-blue-500"></i>
                        Daftar Perjalanan Dinas
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">Pantau tujuan, periode, personel, dan biaya rampung.</p>
                </div>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1.5 rounded-lg">
                    {{ $travelOrders->count() }} perjalanan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Tujuan</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-wide text-slate-500">Personel</th>
                            <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-wide text-slate-500">Biaya Rampung</th>
                            <th class="px-5 py-3 text-center text-xs font-black uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($travelOrders as $travelOrder)
                            @php
                                $days = ($travelOrder->tanggal_berangkat && $travelOrder->tanggal_kembali)
                                    ? $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1
                                    : 0;
                                $rowTotal = $travelOrder->personnels->sum(function ($personnel) {
                                    return (float) $personnel->uang_harian
                                        + (float) $personnel->biaya_transport
                                        + (float) ($personnel->biaya_taksi ?? 0)
                                        + (float) $personnel->biaya_penginapan
                                        + (float) $personnel->biaya_representasi;
                                });
                            @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-4 align-top">
                                    <div class="font-black text-slate-900">{{ $travelOrder->tempat_tujuan }}</div>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-md bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $travelOrder->tipe_perjalanan }}
                                        </span>
                                        @if($days > 0)
                                            <span class="text-xs font-semibold text-slate-500">{{ $days }} hari</span>
                                        @endif
                                    </div>
                                    @if($travelOrder->maksud_perjalanan)
                                        <p class="mt-2 text-xs text-slate-500 leading-relaxed max-w-md">{{ \Illuminate\Support\Str::limit($travelOrder->maksud_perjalanan, 130) }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-top whitespace-nowrap text-sm font-semibold text-slate-700">
                                    <div>{{ $travelOrder->tanggal_berangkat?->format('d/m/Y') ?? '-' }}</div>
                                    <div class="text-xs text-slate-400 font-bold">s.d. {{ $travelOrder->tanggal_kembali?->format('d/m/Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="flex flex-wrap gap-1.5 max-w-sm">
                                        @forelse($travelOrder->personnels as $personnel)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">
                                                <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                                                {{ $personnel->employee?->nama ?? 'Pegawai' }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400 font-semibold">Belum ada personel</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-4 align-top text-right whitespace-nowrap">
                                    <div class="font-black text-slate-900">{{ $money($rowTotal) }}</div>
                                    <div class="text-xs text-slate-400 font-bold">{{ $travelOrder->personnels->count() }} orang</div>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('kabid.packages.travel-orders.show', [$package, $travelOrder]) }}"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                                            title="Lihat detail">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div class="mx-auto w-14 h-14 rounded-2xl border border-dashed border-slate-300 flex items-center justify-center text-slate-300">
                                        <i data-lucide="plane" class="w-7 h-7"></i>
                                    </div>
                                    <h3 class="mt-4 text-sm font-black text-slate-800">Belum ada perjalanan dinas</h3>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan perjalanan untuk mulai menyusun SPT, SPPD, dan kuitansi.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="space-y-6 xl:sticky xl:top-20">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-emerald-500"></i>
                        Informasi Paket
                    </h2>
                </div>
                <div class="p-5 space-y-4 text-sm">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Sub Kegiatan</p>
                        <p class="mt-1 font-bold text-slate-800 leading-snug">{{ $package->subActivity?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Kegiatan</p>
                        <p class="mt-1 font-bold text-slate-800 leading-snug">{{ $package->activity?->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Tahun Anggaran</p>
                        <p class="mt-1 font-bold text-slate-800">{{ $package->fiscalYear?->tahun ?? '-' }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-output" class="w-4 h-4 text-amber-500"></i>
                        Dokumen yang Dikelola
                    </h2>
                </div>
                <div class="p-5 space-y-3">
                    @foreach(['Nota Dinas', 'Surat Tugas', 'SPPD', 'Kuitansi'] as $document)
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
                            <span class="text-sm font-bold text-slate-700">{{ $document }}</span>
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
@endcomponent
