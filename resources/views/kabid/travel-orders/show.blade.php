@component('layouts.kdmp')
@section('title', 'Detail Perjalanan Dinas')

@php
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $isLuarDaerah = in_array(strtolower($travelOrder->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
    $days = $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1;
    // Ambil jumlah malam dari salah satu estimasi pegawai (karena malam bisa diubah jadi 0 untuk tujuan tertentu misal Bengkayang)
    $nights = max(0, $days - 1);
    if (!empty($estimates)) {
        $firstEst = reset($estimates);
        if (isset($firstEst['nights'])) {
            $nights = $firstEst['nights'];
        }
    }
    
    $totalPerkiraan = 0;
    $totalRampung = 0;

    // Konteks review pengajuan SPPD dari staf.
    $isSppdSubmission = filled($travelOrder->created_by);
    $meta = $travelOrder->statusMeta();
    $isDiajukan = $travelOrder->status === \App\Models\TravelOrder::STATUS_SUBMITTED;
    $isApproved = $travelOrder->status === \App\Models\TravelOrder::STATUS_APPROVED;

    // Konteks review SPJ (biaya rampung).
    $spjMeta = $travelOrder->spjStatusMeta();
    $isSpjDiajukan = $travelOrder->spjStatus() === \App\Models\TravelOrder::SPJ_SUBMITTED;
    $isSpjReviewed = filled($travelOrder->spj_status);

    // Anggaran perjalanan dinas paket ini: pagu dikurangi realisasi (hanya SPJ yang sudah disetujui).
    $paguPaket = (float) $package->pagu;
    $realisasiPD = 0;
    foreach ($package->travelOrders()->with('personnels')->get() as $toBudget) {
        if ($toBudget->spjStatus() !== \App\Models\TravelOrder::SPJ_APPROVED) { continue; }
        foreach ($toBudget->personnels as $pBudget) {
            $realisasiPD += (float) $pBudget->uang_harian
                + (float) $pBudget->biaya_penginapan
                + (float) $pBudget->biaya_representasi
                + (float) $pBudget->biaya_transport
                + (float) ($pBudget->biaya_taksi ?? 0);
        }
    }
    $sisaAnggaran = $paguPaket - $realisasiPD;
    $persenTerpakai = $paguPaket > 0 ? min(100, $realisasiPD / $paguPaket * 100) : 0;

    // Perkiraan biaya pengajuan ini (dihitung dari estimasi standar biaya).
    $estimasiPengajuan = 0;
    foreach ($travelOrder->personnels as $pEst) {
        $eEst = $estimates[$pEst->id] ?? [];
        $estimasiPengajuan += ($eEst['uang_harian'] ?? 0)
            + ($eEst['biaya_transport'] ?? 0)
            + ($eEst['biaya_penginapan'] ?? 0)
            + ($eEst['biaya_representasi'] ?? 0)
            + ($isLuarDaerah ? ($eEst['biaya_taksi'] ?? 0) : 0);
    }
    $anggaranCukup = $sisaAnggaran >= $estimasiPengajuan;

    // Biaya rampung SPJ hanya terisi setelah staf mengajukan SPJ.
    $spjFilled = $travelOrder->spjStatus() !== \App\Models\TravelOrder::SPJ_DRAFT;
    $totalRampungSpj = 0;
    foreach ($travelOrder->personnels as $pR) {
        $totalRampungSpj += (float) $pR->uang_harian
            + (float) $pR->biaya_penginapan
            + (float) $pR->biaya_representasi
            + (float) $pR->biaya_transport
            + ($isLuarDaerah ? (float) ($pR->biaya_taksi ?? 0) : 0);
    }
    // Sisa anggaran bila SPJ ini disetujui (realisasiPD belum termasuk SPJ yang sedang diajukan).
    $sisaSetelahSpj = $sisaAnggaran - $totalRampungSpj;
    $spjTekor = $sisaSetelahSpj < 0;
@endphp

<div class="space-y-6" x-data="{
        showRevisiSpj: false,
        review: { open: false, mode: 'revisi', action: '', tujuan: '' },
        openReview(mode, action, tujuan) { this.review = { open: true, mode, action, tujuan }; },
    }">
    <x-ui.toast />

    {{-- Strip konteks --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
            <i data-lucide="hash" class="w-3.5 h-3.5 text-sky-500"></i>{{ $package->id_rup ?? '-' }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-100 rounded-lg">
            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ $travelOrder->tempat_tujuan }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg">
            <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>{{ $days }} hari {{ $nights > 0 ? $nights . ' malam' : '' }}
        </span>
        @if($isSppdSubmission)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border {{ $meta['badge'] }}">
                <i data-lucide="{{ $meta['icon'] }}" class="w-3.5 h-3.5"></i>{{ $meta['label'] }}
            </span>
        @endif
    </div>

    {{-- Baris atas: Informasi (kiri) + Aksi (kanan) --}}
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">
        {{-- Informasi Perjalanan --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i data-lucide="plane" class="w-4 h-4"></i></div>
                <h2 class="text-sm font-bold text-slate-900">Informasi Perjalanan</h2>
            </div>
            <dl class="divide-y divide-slate-100">
                @php
                    $infoRows = [
                        ['Sub Kegiatan', trim(($package->subActivity?->kode ?? '') . ' ' . ($package->subActivity?->nama ?? '')) ?: '-'],
                        ['Tipe Perjalanan', ucwords(str_replace('_', ' ', $travelOrder->tipe_perjalanan))],
                        ['Dasar Pelaksanaan', $travelOrder->dasar_pelaksanaan ?: '-'],
                        ['Maksud Perjalanan', $travelOrder->maksud_perjalanan],
                        ['Tempat Tujuan', $travelOrder->tempat_tujuan],
                        ['Tanggal Berangkat', $travelOrder->tanggal_berangkat->format('d-m-Y')],
                        ['Tanggal Kembali', $travelOrder->tanggal_kembali->format('d-m-Y')],
                        ['Lama Perjalanan', $days . ' Hari' . ($nights > 0 ? ' ' . $nights . ' Malam' : '')],
                        ['Tanggal Surat', $travelOrder->tanggal_surat ? $travelOrder->tanggal_surat->format('d-m-Y') : '-'],
                    ];
                @endphp
                @foreach($infoRows as [$label, $value])
                    <div class="px-6 py-3 flex flex-col sm:flex-row sm:gap-4">
                        <dt class="w-full sm:w-52 text-sm font-semibold text-slate-500 shrink-0">{{ $label }}</dt>
                        <dd class="text-sm text-slate-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Kolom kanan: Aksi + Anggaran (span 2 baris) --}}
        <div class="space-y-6 xl:row-span-2">
        {{-- Aksi --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="gavel" class="w-4 h-4"></i></div>
                <h2 class="text-sm font-bold text-slate-900">Aksi</h2>
            </div>
            <div class="p-5 space-y-4">
                {{-- Review pengajuan SPPD --}}
                @if($isDiajukan)
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Tinjau Pengajuan SPPD</p>
                        <div class="space-y-2">
                            <form method="POST" action="{{ route('kabid.packages.travel-orders.approve', [$package, $travelOrder]) }}" onsubmit="return confirm('Setujui pengajuan SPPD ini?');">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm shadow-emerald-200 transition-colors">
                                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> Setujui
                                </button>
                            </form>
                            <button type="button"
                                @click="openReview('revisi', '{{ route('kabid.packages.travel-orders.revise', [$package, $travelOrder]) }}', '{{ addslashes($travelOrder->tempat_tujuan) }}')"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-amber-700 bg-white border border-amber-200 rounded-xl hover:bg-amber-50 transition-colors">
                                <i data-lucide="file-warning" class="w-4 h-4"></i> Minta Revisi
                            </button>
                            <button type="button"
                                @click="openReview('tolak', '{{ route('kabid.packages.travel-orders.reject', [$package, $travelOrder]) }}', '{{ addslashes($travelOrder->tempat_tujuan) }}')"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-rose-700 bg-white border border-rose-200 rounded-xl hover:bg-rose-50 transition-colors">
                                <i data-lucide="x-circle" class="w-4 h-4"></i> Tolak
                            </button>
                        </div>
                    </div>
                @elseif($isSppdSubmission && $travelOrder->catatan_review)
                    <div class="rounded-xl border p-3 {{ $travelOrder->status === \App\Models\TravelOrder::STATUS_REJECTED ? 'border-rose-200 bg-rose-50/50' : 'border-amber-200 bg-amber-50/50' }}">
                        <p class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i data-lucide="{{ $meta['icon'] }}" class="w-3.5 h-3.5 {{ $travelOrder->status === \App\Models\TravelOrder::STATUS_REJECTED ? 'text-rose-500' : 'text-amber-500' }}"></i>
                            {{ $meta['label'] }}
                        </p>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $travelOrder->catatan_review }}</p>
                    </div>
                @endif

                {{-- Review SPJ (biaya rampung) — setelah SPPD disetujui & SPJ diajukan --}}
                @if($isSpjDiajukan)
                    <div class="{{ $isDiajukan ? 'pt-4 border-t border-slate-100' : '' }}">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Tinjau SPJ (Biaya Rampung)</p>
                        <div class="space-y-2">
                            <form method="POST" action="{{ route('kabid.packages.travel-orders.spj.approve', [$package, $travelOrder]) }}" onsubmit="return confirm('Setujui SPJ (biaya rampung) ini?');">
                                @csrf
                                <button type="submit" @disabled($spjTekor)
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold rounded-xl shadow-sm transition-colors {{ $spjTekor ? 'text-slate-400 bg-slate-100 cursor-not-allowed' : 'text-white bg-emerald-600 hover:bg-emerald-700 shadow-emerald-200' }}">
                                    <i data-lucide="receipt-text" class="w-4 h-4"></i> Setujui SPJ
                                </button>
                            </form>
                            @if($spjTekor)
                                <p class="text-[11px] font-semibold text-rose-600 flex items-start gap-1.5">
                                    <i data-lucide="ban" class="w-3.5 h-3.5 shrink-0 mt-0.5"></i>
                                    Anggaran tekor — SPJ tidak dapat disetujui. Minta revisi biaya rampung.
                                </p>
                            @endif
                            <button type="button" @click="showRevisiSpj = true"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-amber-700 bg-white border border-amber-200 rounded-xl hover:bg-amber-50 transition-colors">
                                <i data-lucide="file-warning" class="w-4 h-4"></i> Minta Revisi SPJ
                            </button>
                        </div>
                    </div>
                @elseif($isSpjReviewed)
                    <div class="rounded-xl border p-3 {{ $spjMeta['badge'] }}">
                        <p class="text-xs font-bold flex items-center gap-1.5"><i data-lucide="{{ $spjMeta['icon'] }}" class="w-3.5 h-3.5"></i> SPJ: {{ $spjMeta['label'] }}</p>
                        @if($travelOrder->spjStatus() === \App\Models\TravelOrder::SPJ_REVISION && $travelOrder->spj_catatan)
                            <p class="text-xs mt-1 opacity-90 leading-relaxed">{{ $travelOrder->spj_catatan }}</p>
                        @endif
                    </div>
                @endif

                <a href="{{ route('kabid.sppd.index') }}"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
            </div>
        </section>

        {{-- Anggaran Perjalanan Dinas --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i data-lucide="wallet" class="w-4 h-4"></i></div>
                <h2 class="text-sm font-bold text-slate-900">Anggaran Perjalanan Dinas</h2>
            </div>
            <div class="p-5 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500 font-semibold">Pagu Paket</span>
                    <span class="font-bold text-slate-800">{{ $money($paguPaket) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500 font-semibold">Terealisasi <span class="font-normal text-slate-400">(SPJ disetujui)</span></span>
                    <span class="font-bold text-slate-800">{{ $money($realisasiPD) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm pt-2 border-t border-slate-100">
                    <span class="text-slate-600 font-bold">Sisa Anggaran</span>
                    <span class="font-black {{ $sisaAnggaran < 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $money($sisaAnggaran) }}</span>
                </div>
                <div>
                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full {{ $persenTerpakai >= 90 ? 'bg-rose-500' : ($persenTerpakai >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $persenTerpakai }}%;"></div>
                    </div>
                    <p class="mt-1.5 text-[11px] font-bold text-slate-400 text-right">{{ number_format($persenTerpakai, 1, ',', '.') }}% terpakai</p>
                </div>

                @if($isDiajukan)
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 font-semibold">Sisa setelah disetujui</span>
                            <span class="font-bold {{ ($sisaAnggaran - $estimasiPengajuan) < 0 ? 'text-rose-600' : 'text-slate-800' }}">{{ $money($sisaAnggaran - $estimasiPengajuan) }}</span>
                        </div>
                        @if($anggaranCukup)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 flex items-start gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                <p class="text-xs font-bold text-emerald-700">Anggaran mencukupi untuk pengajuan ini.</p>
                            </div>
                        @else
                            <div class="rounded-xl border border-rose-200 bg-rose-50/60 px-3 py-2.5 flex items-start gap-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                <p class="text-xs font-bold text-rose-700">Perkiraan biaya melebihi sisa anggaran. Pertimbangkan revisi atau penolakan.</p>
                            </div>
                        @endif
                    </div>
                @elseif($isSpjDiajukan)
                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 font-semibold">Biaya Rampung SPJ</span>
                            <span class="font-bold text-slate-800">{{ $money($totalRampungSpj) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 font-semibold">Sisa setelah disetujui</span>
                            <span class="font-bold {{ $spjTekor ? 'text-rose-600' : 'text-slate-800' }}">{{ $money($sisaSetelahSpj) }}</span>
                        </div>
                        @if($spjTekor)
                            <div class="rounded-xl border border-rose-200 bg-rose-50/60 px-3 py-2.5 flex items-start gap-2">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                                <p class="text-xs font-bold text-rose-700">Biaya rampung melebihi sisa anggaran (tekor). SPJ tidak dapat disetujui — minta revisi.</p>
                            </div>
                        @else
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 px-3 py-2.5 flex items-start gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                <p class="text-xs font-bold text-emerald-700">Anggaran mencukupi untuk menyetujui SPJ ini.</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </section>
        </div>

        {{-- Pelaksana / Biaya (kolom kiri, di bawah Informasi Perjalanan) --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden xl:col-start-1 min-w-0">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-emerald-500"></i> Pelaksana Perjalanan Dinas
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    @if($isApproved)
                        Rincian standar biaya, perkiraan, dan biaya rampung (SPJ) dari staf.
                    @else
                        Menampilkan biaya perkiraan. Biaya rampung muncul setelah SPPD disetujui.
                    @endif
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-bold rounded-lg bg-slate-100 text-slate-600">{{ $travelOrder->personnels->count() }} pegawai</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Detail Pegawai</th>
                        <th class="px-4 py-3 text-left">Standar Biaya</th>
                        <th class="px-4 py-3 text-center">Koefisien</th>
                        <th class="px-4 py-3 text-right">Biaya Perkiraan</th>
                        @if($isApproved)
                            <th class="px-4 py-3 text-right">Biaya Rampung (SPJ)</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($travelOrder->personnels as $index => $personnel)
                        @php
                            $est = $estimates[$personnel->id] ?? [];
                            $isEselon2 = ($personnel->employee?->kategori_biaya === 'Eselon II') || str_contains(strtolower($personnel->employee?->jabatan ?? ''), 'kepala dinas');
                            $perkiraan = ($est['uang_harian'] ?? 0) + ($est['biaya_transport'] ?? 0) + ($est['biaya_penginapan'] ?? 0) + ($est['biaya_representasi'] ?? 0) + ($isLuarDaerah ? ($est['biaya_taksi'] ?? 0) : 0);
                            $rampung = (float) $personnel->uang_harian + (float) $personnel->biaya_transport + (float) $personnel->biaya_penginapan + (float) $personnel->biaya_representasi + ($isLuarDaerah ? (float) ($personnel->biaya_taksi ?? 0) : 0);
                            $totalPerkiraan += $perkiraan;
                            $totalRampung += $rampung;
                        @endphp
                        <tr class="align-top">
                            <td class="px-4 py-4 text-center text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-4 min-w-56">
                                <p class="font-black text-slate-900 leading-snug">{{ $personnel->employee?->nama ?? 'Pegawai' }}</p>
                                <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                                    {{ $personnel->employee?->jabatan ?? '-' }}<br>
                                    NIP {{ $personnel->employee?->nip ?? '-' }}<br>
                                    Pangkat/Gol. {{ $personnel->employee?->golongan ?? '-' }}
                                </p>
                                <span class="mt-2 inline-flex items-center gap-1 px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-bold">
                                    <i data-lucide="car" class="w-3 h-3"></i>{{ ucfirst($personnel->jenis_kendaraan ?? 'mobil') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-700 space-y-1 whitespace-nowrap">
                                <div class="flex justify-between gap-3"><span class="text-slate-500">U. Harian</span><span class="font-bold">{{ $money($est['base_uang_harian'] ?? 0) }}</span></div>
                                <div class="flex justify-between gap-3"><span class="text-slate-500">Transport</span><span class="font-bold">{{ $money($est['biaya_transport'] ?? 0) }}</span></div>
                                @if($isLuarDaerah)<div class="flex justify-between gap-3"><span class="text-slate-500">Taksi</span><span class="font-bold">{{ $money($est['biaya_taksi'] ?? 0) }}</span></div>@endif
                                <div class="flex justify-between gap-3"><span class="text-slate-500">Penginapan</span><span class="font-bold">{{ $money($est['base_penginapan'] ?? 0) }}</span></div>
                                @if($isEselon2)<div class="flex justify-between gap-3"><span class="text-slate-500">Representasi</span><span class="font-bold">{{ $money($est['base_representasi'] ?? 0) }}</span></div>@endif
                            </td>
                            <td class="px-4 py-4 text-xs text-center text-slate-600 space-y-1 whitespace-nowrap">
                                <div>{{ $est['days'] ?? $days }} Hari</div>
                                <div>1 Kali</div>
                                @if($isLuarDaerah)<div>1 Kali</div>@endif
                                <div>{{ $est['nights'] ?? $nights }} Malam</div>
                                @if($isEselon2)<div>{{ $est['days'] ?? $days }} Hari</div>@endif
                            </td>
                            <td class="px-4 py-4 text-xs text-right text-slate-700 space-y-1 whitespace-nowrap">
                                <div>{{ $money($est['uang_harian'] ?? 0) }}</div>
                                <div>{{ $money($est['biaya_transport'] ?? 0) }}</div>
                                @if($isLuarDaerah)<div>{{ $money($est['biaya_taksi'] ?? 0) }}</div>@endif
                                <div>{{ $money($est['biaya_penginapan'] ?? 0) }}</div>
                                @if($isEselon2)<div>{{ $money($est['biaya_representasi'] ?? 0) }}</div>@endif
                                <div class="pt-1 mt-1 border-t border-slate-100 font-black text-slate-900">{{ $money($perkiraan) }}</div>
                            </td>
                            @if($isApproved)
                                <td class="px-4 py-4 text-xs text-right space-y-1 whitespace-nowrap text-emerald-700">
                                    @if($spjFilled)
                                        <div>{{ $money($personnel->uang_harian) }}</div>
                                        <div>{{ $money($personnel->biaya_transport) }}</div>
                                        @if($isLuarDaerah)<div>{{ $money($personnel->biaya_taksi ?? 0) }}</div>@endif
                                        <div>{{ $money($personnel->biaya_penginapan) }}</div>
                                        @if($isEselon2)<div>{{ $money($personnel->biaya_representasi) }}</div>@endif
                                        <div class="pt-1 mt-1 border-t border-emerald-100 font-black">{{ $money($rampung) }}</div>
                                    @else
                                        <div class="text-slate-300 font-bold text-base">—</div>
                                        <p class="text-[10px] text-slate-400 font-semibold leading-tight">Menunggu SPJ staf</p>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <th colspan="4" class="px-4 py-4 text-right text-sm font-black text-slate-700">Total</th>
                        <th class="px-4 py-4 text-right text-lg font-black text-slate-900">{{ $money($totalPerkiraan) }}</th>
                        @if($isApproved)
                            <th class="px-4 py-4 text-right text-lg font-black text-emerald-700">{{ $spjFilled ? $money($totalRampung) : '—' }}</th>
                        @endif
                    </tr>
                </tfoot>
            </table>
        </div>
        </section>
    </div>

    {{-- Modal Revisi SPJ --}}
    @if($isSpjDiajukan)
        <div x-show="showRevisiSpj" style="display:none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            @keydown.escape.window="showRevisiSpj = false">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showRevisiSpj = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <form method="POST" action="{{ route('kabid.packages.travel-orders.spj.revise', [$package, $travelOrder]) }}">
                    @csrf
                    <div class="px-5 py-4 border-b border-slate-100 bg-amber-50/60 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-600"><i data-lucide="file-warning" class="w-4 h-4"></i></span>
                        <h3 class="font-bold text-slate-800">Minta Revisi SPJ</h3>
                    </div>
                    <div class="p-5">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan Revisi <span class="text-rose-500">*</span></label>
                        <textarea name="spj_catatan" rows="4" required placeholder="Jelaskan biaya rampung yang perlu diperbaiki..."
                            class="w-full rounded-lg border-slate-300 focus:border-amber-500 focus:ring-amber-500 sm:text-sm"></textarea>
                    </div>
                    <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-stretch justify-end gap-2">
                        <button type="button" @click="showRevisiSpj = false" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl shadow-sm shadow-amber-200 transition-colors whitespace-nowrap">
                            <i data-lucide="send" class="w-4 h-4 shrink-0"></i> Kirim Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Revisi / Tolak SPPD (bersama) --}}
    <div x-show="review.open" style="display:none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        @keydown.escape.window="review.open = false">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="review.open = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <form method="POST" :action="review.action">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2"
                    :class="review.mode === 'tolak' ? 'bg-rose-50/60' : 'bg-amber-50/60'">
                    <span class="w-7 h-7 rounded-lg border flex items-center justify-center"
                        :class="review.mode === 'tolak' ? 'bg-rose-100 border-rose-200 text-rose-600' : 'bg-amber-100 border-amber-200 text-amber-600'">
                        <i x-show="review.mode === 'tolak'" data-lucide="x-circle" class="w-4 h-4"></i>
                        <i x-show="review.mode !== 'tolak'" data-lucide="file-warning" class="w-4 h-4"></i>
                    </span>
                    <h3 class="font-bold text-slate-800" x-text="review.mode === 'tolak' ? 'Tolak Pengajuan SPPD' : 'Minta Revisi SPPD'"></h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-slate-400 mb-2">Tujuan: <span class="font-semibold text-slate-600" x-text="review.tujuan"></span></p>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                        <span x-text="review.mode === 'tolak' ? 'Alasan Penolakan' : 'Catatan Revisi'"></span> <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="catatan_review" rows="4" required
                        :placeholder="review.mode === 'tolak' ? 'Jelaskan alasan penolakan...' : 'Jelaskan apa yang perlu diperbaiki staf...'"
                        class="w-full rounded-lg border-slate-300 sm:text-sm"
                        :class="review.mode === 'tolak' ? 'focus:border-rose-500 focus:ring-rose-500' : 'focus:border-amber-500 focus:ring-amber-500'"></textarea>
                    <p x-show="review.mode === 'tolak'" class="text-[11px] text-slate-400 mt-1.5">Penolakan bersifat final — staf tidak dapat mengajukan ulang.</p>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-stretch justify-end gap-2">
                    <button type="button" @click="review.open = false" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap">Batal</button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white rounded-xl shadow-sm transition-colors whitespace-nowrap"
                        :class="review.mode === 'tolak' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-200' : 'bg-amber-500 hover:bg-amber-600 shadow-amber-200'">
                        <i data-lucide="send" class="w-4 h-4 shrink-0"></i>
                        <span x-text="review.mode === 'tolak' ? 'Tolak' : 'Kirim Revisi'"></span>
                    </button>
                </div>
            </form>
        </div>
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
