@component('layouts.kdmp')
@section('title', 'SPJ SPD')

@php
    $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $isLuarDaerah = in_array(strtolower($travelOrder->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
    $days = $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1;
    $nights = max(0, $days - 1);

    $spjStatus = $travelOrder->spjStatus();
    $spjMeta = $travelOrder->spjStatusMeta();
    $spjEditable = $travelOrder->isSpjEditable();
    $spjSubmitted = $spjStatus === \App\Models\TravelOrder::SPJ_SUBMITTED;
    $spjApproved = $spjStatus === \App\Models\TravelOrder::SPJ_APPROVED;
    $spjRevision = $spjStatus === \App\Models\TravelOrder::SPJ_REVISION;

    $spjRows = $travelOrder->personnels->map(function ($p) use ($estimates, $isLuarDaerah) {
        $est = $estimates[$p->id] ?? [];
        return [
            'id' => $p->id,
            'uang_harian' => (int) $p->uang_harian,
            'biaya_transport' => (int) $p->biaya_transport,
            'biaya_taksi' => (int) ($p->biaya_taksi ?? 0),
            'biaya_penginapan' => (int) $p->biaya_penginapan,
            'biaya_representasi' => (int) $p->biaya_representasi,
        ];
    })->keyBy('id');
@endphp

<div class="space-y-6"
    x-data="{
        rows: @js($spjRows),
        rowTotal(r) {
            return (Number(r.uang_harian)||0)+(Number(r.biaya_transport)||0)+(Number(r.biaya_taksi)||0)+(Number(r.biaya_penginapan)||0)+(Number(r.biaya_representasi)||0);
        },
        get grandTotal() { return Object.values(this.rows).reduce((s, r) => s + this.rowTotal(r), 0); },
        rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(n || 0); },
    }">
    <x-ui.toast />

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i>
                {{ $travelOrder->tempat_tujuan }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                <i data-lucide="calendar-days" class="w-3.5 h-3.5 text-emerald-500"></i>
                {{ $days }} hari
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border {{ $spjMeta['badge'] }}">
                <i data-lucide="{{ $spjMeta['icon'] }}" class="w-3.5 h-3.5"></i>
                SPJ: {{ $spjMeta['label'] }}
            </span>
        </div>
        <a href="{{ route('staf.packages.travel-orders.show', [$package, $travelOrder]) }}"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke SPPD
        </a>
    </div>

    {{-- Judul --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Surat Pertanggungjawaban (SPJ SPD)</p>
                <h1 class="mt-2 text-xl font-bold text-slate-900 leading-tight">{{ $travelOrder->maksud_perjalanan }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $package->nama_paket }}</p>
            </div>
            {{-- Buat Laporan (AI) — fitur berikutnya --}}
            <button type="button" disabled
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed shrink-0"
                title="Segera hadir">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                Buat Laporan (AI)
                <span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-slate-200 text-slate-500">Segera</span>
            </button>
        </div>
    </div>

    {{-- Banner status SPJ --}}
    @if($spjApproved)
        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50/80 to-teal-50/50 p-5 flex items-start gap-3">
            <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="font-bold text-slate-800 text-sm">SPJ Disetujui</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $travelOrder->spjReviewer ? 'Disetujui oleh ' . $travelOrder->spjReviewer->name : 'Disetujui' }}
                    {{ $travelOrder->spj_reviewed_at ? '· ' . $travelOrder->spj_reviewed_at->locale('id')->translatedFormat('d F Y') : '' }}.
                    Kwitansi tiap pelaksana siap dicetak di bawah.
                </p>
            </div>
        </div>
    @elseif($spjSubmitted)
        <div class="rounded-2xl border border-blue-200 bg-blue-50/60 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="relative flex w-5 h-5 items-center justify-center">
                        <span class="animate-ping absolute inline-flex h-4 w-4 rounded-full bg-blue-400 opacity-60"></span>
                        <i data-lucide="send" class="relative w-4 h-4"></i>
                    </span>
                </span>
                <div>
                    <p class="font-bold text-slate-800 text-sm">SPJ Menunggu Persetujuan</p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Diajukan {{ $travelOrder->spj_submitted_at?->locale('id')->diffForHumans() }}. Anda dapat menariknya kembali selama belum ditinjau.
                    </p>
                </div>
            </div>
            <form method="POST" action="{{ route('staf.packages.travel-orders.spj.withdraw', [$package, $travelOrder]) }}"
                onsubmit="return confirm('Tarik kembali pengajuan SPJ ke Draf?');" class="shrink-0">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                    <i data-lucide="undo-2" class="w-4 h-4"></i> Tarik Pengajuan
                </button>
            </form>
        </div>
    @elseif($spjRevision)
        <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5 flex items-start gap-3">
            <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="file-warning" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="font-bold text-slate-800 text-sm">SPJ Perlu Revisi</p>
                <p class="text-sm text-slate-600 mt-0.5 leading-relaxed">{{ $travelOrder->spj_catatan ?: 'Perbaiki biaya rampung lalu ajukan ulang.' }}</p>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5 flex items-start gap-3">
            <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                <i data-lucide="receipt-text" class="w-5 h-5"></i>
            </span>
            <div>
                <p class="font-bold text-slate-800 text-sm">Input Biaya Rampung</p>
                <p class="text-xs text-slate-500 mt-0.5">Isi realisasi biaya tiap pelaksana sesuai bukti pengeluaran, lalu ajukan untuk disetujui.</p>
            </div>
        </div>
    @endif

    {{-- Form / tampilan biaya rampung --}}
    <form method="POST" action="{{ route('staf.packages.travel-orders.spj.store', [$package, $travelOrder]) }}" class="space-y-4">
        @csrf

        @error('personnels')
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $message }}</div>
        @enderror

        <fieldset @unless($spjEditable) disabled @endunless class="space-y-4 {{ $spjEditable ? '' : 'opacity-90' }}">
            @foreach($travelOrder->personnels as $i => $personnel)
                @php
                    $est = $estimates[$personnel->id] ?? [];
                    $isEselon2 = ($personnel->employee?->kategori_biaya === 'Eselon II')
                        || str_contains(strtolower($personnel->employee?->jabatan ?? ''), 'kepala dinas');
                @endphp
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $i === 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }}">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="font-bold {{ $i === 0 ? 'text-amber-700' : 'text-slate-900' }} leading-snug truncate">{{ $personnel->employee?->nama ?? 'Pegawai' }}</p>
                                <p class="text-xs text-slate-400">{{ $personnel->employee?->jabatan ?? '-' }} &bull; Gol. {{ $personnel->employee?->golongan ?? '-' }} &bull; {{ ucfirst($personnel->jenis_kendaraan ?? 'mobil') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Rampung</p>
                                <p class="text-lg font-extrabold text-emerald-700" x-text="rp(rowTotal(rows[{{ $personnel->id }}]))"></p>
                            </div>
                            @if($spjApproved)
                                <a href="{{ route('packages.travel-orders.personnels.print-kuitansi', [$package, $travelOrder, $personnel]) }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-slate-900 rounded-lg hover:bg-black shadow-sm shrink-0">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Kwitansi
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Uang Harian</label>
                            <input type="number" min="0" name="personnels[{{ $personnel->id }}][uang_harian]" x-model.number="rows[{{ $personnel->id }}].uang_harian"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                            <p class="text-[11px] text-slate-400 mt-1">Perkiraan: {{ $money($est['uang_harian'] ?? 0) }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Transport</label>
                            <input type="number" min="0" name="personnels[{{ $personnel->id }}][biaya_transport]" x-model.number="rows[{{ $personnel->id }}].biaya_transport"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                            <p class="text-[11px] text-slate-400 mt-1">Perkiraan: {{ $money($est['biaya_transport'] ?? 0) }}</p>
                        </div>
                        @if($isLuarDaerah)
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Taksi</label>
                                <input type="number" min="0" name="personnels[{{ $personnel->id }}][biaya_taksi]" x-model.number="rows[{{ $personnel->id }}].biaya_taksi"
                                    class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                                <p class="text-[11px] text-slate-400 mt-1">Perkiraan: {{ $money($est['biaya_taksi'] ?? 0) }}</p>
                            </div>
                        @else
                            <input type="hidden" name="personnels[{{ $personnel->id }}][biaya_taksi]" value="0">
                        @endif
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Penginapan</label>
                            <input type="number" min="0" name="personnels[{{ $personnel->id }}][biaya_penginapan]" x-model.number="rows[{{ $personnel->id }}].biaya_penginapan"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                            <p class="text-[11px] text-slate-400 mt-1">Perkiraan: {{ $money($est['biaya_penginapan'] ?? 0) }} &bull; {{ $est['nights'] ?? $nights }} malam</p>
                        </div>
                        <div class="{{ $isLuarDaerah ? 'sm:col-span-2 lg:col-span-1' : '' }}">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                Representasi @unless($isEselon2)<span class="font-normal text-slate-400">(Eselon II)</span>@endunless
                            </label>
                            <input type="number" min="0" name="personnels[{{ $personnel->id }}][biaya_representasi]" x-model.number="rows[{{ $personnel->id }}].biaya_representasi"
                                class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                            <p class="text-[11px] text-slate-400 mt-1">Perkiraan: {{ $money($est['biaya_representasi'] ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </fieldset>

        {{-- Footer total + aksi --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Realisasi (SPJ)</p>
                <p class="text-2xl font-extrabold text-emerald-700" x-text="rp(grandTotal)"></p>
            </div>
            @if($spjEditable)
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan
                    </button>
                    <button type="submit" formaction="{{ route('staf.packages.travel-orders.spj.store', [$package, $travelOrder]) }}"
                        onclick="this.form.querySelector('#spj-then-submit').value='1'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                        <i data-lucide="send" class="w-4 h-4"></i> Simpan &amp; Ajukan
                    </button>
                    <input type="hidden" name="then_submit" id="spj-then-submit" value="0">
                </div>
            @elseif($spjApproved)
                <span class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <i data-lucide="lock" class="w-4 h-4"></i> Terkunci — sudah disetujui
                </span>
            @endif
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endcomponent
