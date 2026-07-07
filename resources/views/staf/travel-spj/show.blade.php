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

    // Komponen biaya per pelaksana: koefisien tetap (dari data perjalanan),
    // harga satuan = nilai yang bisa diedit (default tarif standar), jumlah = koef x harga.
    $mk = function ($label, $koef, $satuan, $stored, $estAmt) {
        $koef = $koef > 0 ? $koef : 1;
        $amount = (int) ($stored > 0 ? $stored : ($estAmt ?? 0));
        return [
            'label' => $label, 'koef' => $koef, 'satuan' => $satuan,
            'rate' => $amount / $koef,
            'std'  => ($estAmt ?? 0) / $koef,
        ];
    };

    $spjTable = $travelOrder->personnels->mapWithKeys(function ($p) use ($estimates, $days, $nights, $isLuarDaerah, $mk) {
        $est = $estimates[$p->id] ?? [];
        $n = (int) ($est['nights'] ?? $nights);
        $comp = [];
        $comp['uang_harian'] = $mk('Uang harian', $days, 'hari', $p->uang_harian, $est['uang_harian'] ?? 0);
        $comp['biaya_transport'] = $mk('Transport', 1, 'kali', $p->biaya_transport, $est['biaya_transport'] ?? 0);
        if ($isLuarDaerah) {
            $comp['biaya_taksi'] = $mk('Taksi', 1, 'kali', $p->biaya_taksi ?? 0, $est['biaya_taksi'] ?? 0);
        }
        $comp['biaya_penginapan'] = $mk('Penginapan', $n, 'malam', $p->biaya_penginapan, $est['biaya_penginapan'] ?? 0);
        $comp['biaya_representasi'] = $mk('Representasi', $days, 'hari', $p->biaya_representasi, $est['biaya_representasi'] ?? 0);
        return [$p->id => $comp];
    });

    $alpineRows = $spjTable->map(fn ($comp) => collect($comp)->map(fn ($c) => ['koef' => $c['koef'], 'rate' => $c['rate']]))->toArray();
@endphp

<div class="space-y-6"
    x-data="{
        tab: 'laporan',
        rows: @js($alpineRows),
        compTotal(pid, key) { const c = this.rows[pid][key]; return (Number(c.koef)||0) * (Number(c.rate)||0); },
        rowTotal(pid) { let s = 0; for (const k in this.rows[pid]) s += this.compTotal(pid, k); return s; },
        get grandTotal() { let s = 0; for (const pid in this.rows) s += this.rowTotal(pid); return s; },
        rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n) || 0); },
        fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n)) || 0); },
        parseNum(s) { return Number(String(s).replace(/[^\d]/g, '')) || 0; },
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

    {{-- Stepper lifecycle --}}
    <x-travel.stepper :travel-order="$travelOrder" :package="$package" active="spj" />

    {{-- 2 kolom: workspace (kiri) | kontrol (kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">

        {{-- ============ WORKSPACE (KIRI) ============ --}}
        <div class="space-y-4">

            {{-- Tab: Laporan / Biaya --}}
            <div class="inline-flex items-center p-1 bg-slate-100 border border-slate-200 rounded-xl">
                <button type="button" @click="tab = 'laporan'"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg transition-all"
                    :class="tab === 'laporan' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Laporan
                </button>
                <button type="button" @click="tab = 'biaya'"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg transition-all"
                    :class="tab === 'biaya' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                    <i data-lucide="wallet" class="w-4 h-4"></i> Biaya
                </button>
            </div>

            {{-- ===== Panel Laporan ===== --}}
            <div x-show="tab === 'laporan'">
                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <i data-lucide="file-text" class="w-5 h-5 text-indigo-500"></i>
                            Laporan Perjalanan Dinas
                        </h2>
                        <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded bg-slate-200 text-slate-500">Segera</span>
                    </div>
                    <div class="p-10 flex flex-col items-center text-center">
                        <span class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 mb-3">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </span>
                        <p class="text-sm font-bold text-slate-600">Laporan belum dibuat</p>
                        <p class="text-xs text-slate-400 mt-1 max-w-xs">Klik <strong>Generate Laporan</strong> di panel kanan untuk membuat draf laporan otomatis. Hasilnya akan tampil di sini.</p>
                    </div>
                </section>
            </div>

            {{-- ===== Panel Biaya ===== --}}
            <div x-show="tab === 'biaya'" style="display:none">
                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                        <h1 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <i data-lucide="wallet" class="w-5 h-5 text-indigo-500"></i>
                            Input Biaya SPD
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">Harga satuan default mengikuti tarif standar — jumlah = koefisien &times; harga satuan.</p>
                    </div>

                    <div class="p-5 space-y-4">
                        @error('personnels')
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $message }}</div>
                        @enderror

                        @unless($spjEditable)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-500 flex items-center gap-2">
                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                {{ $spjApproved ? 'SPJ sudah disetujui dan terkunci.' : 'SPJ sedang diajukan — tarik pengajuan untuk mengubah.' }}
                            </div>
                        @endunless

                        <form id="spj-form" method="POST" action="{{ route('staf.packages.travel-orders.spj.store', [$package, $travelOrder]) }}">
                            @csrf
                            <input type="hidden" name="then_submit" id="spj-then-submit" value="0">

                            <fieldset @unless($spjEditable) disabled @endunless class="space-y-4 {{ $spjEditable ? '' : 'opacity-90' }}">
                                @foreach($travelOrder->personnels as $i => $personnel)
                                    @php $comp = $spjTable[$personnel->id]; @endphp
                                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
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
                                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total</p>
                                                    <p class="text-base font-extrabold text-emerald-700" x-text="rp(rowTotal({{ $personnel->id }}))"></p>
                                                </div>
                                                @if($spjApproved)
                                                    <a href="{{ route('packages.travel-orders.personnels.print-kuitansi', [$package, $travelOrder, $personnel]) }}" target="_blank"
                                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-slate-900 rounded-lg hover:bg-black shadow-sm shrink-0">
                                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Kwitansi
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="text-[11px] text-slate-400 uppercase font-semibold bg-white border-b border-slate-100">
                                                        <th class="text-left font-semibold px-4 py-2.5">Nama Biaya</th>
                                                        <th class="text-center font-semibold px-3 py-2.5 w-24">Koefisien</th>
                                                        <th class="text-right font-semibold px-3 py-2.5 w-40">Harga Satuan (Rp)</th>
                                                        <th class="text-right font-semibold px-4 py-2.5 w-36">Jumlah</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    @foreach($comp as $key => $c)
                                                        <tr>
                                                            <td class="px-4 py-2.5 font-semibold text-slate-700">
                                                                {{ $c['label'] }}
                                                                @if($key === 'biaya_representasi')
                                                                    <span class="block text-[10px] font-normal text-slate-400">Khusus Eselon II</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-3 py-2.5 text-center text-slate-500 whitespace-nowrap">
                                                                {{ rtrim(rtrim(number_format($c['koef'], 2, ',', '.'), '0'), ',') }} {{ $c['satuan'] }}
                                                            </td>
                                                            <td class="px-3 py-2.5">
                                                                <input type="text" inputmode="numeric" autocomplete="off"
                                                                    :value="fmt(rows[{{ $personnel->id }}]['{{ $key }}'].rate)"
                                                                    @input="rows[{{ $personnel->id }}]['{{ $key }}'].rate = parseNum($event.target.value); $event.target.value = fmt(rows[{{ $personnel->id }}]['{{ $key }}'].rate)"
                                                                    class="w-full text-right rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                                                                <input type="hidden" name="personnels[{{ $personnel->id }}][{{ $key }}]"
                                                                    :value="Math.round(compTotal({{ $personnel->id }}, '{{ $key }}'))">
                                                                <p class="text-[10px] text-slate-400 mt-1 text-right">Standar: {{ $money($c['std']) }}</p>
                                                            </td>
                                                            <td class="px-4 py-2.5 text-right font-bold text-slate-800 whitespace-nowrap" x-text="rp(compTotal({{ $personnel->id }}, '{{ $key }}'))"></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="border-t border-slate-100 bg-slate-50/50">
                                                        <td colspan="3" class="px-4 py-2.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Total</td>
                                                        <td class="px-4 py-2.5 text-right font-extrabold text-emerald-700 whitespace-nowrap" x-text="rp(rowTotal({{ $personnel->id }}))"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        @unless($isLuarDaerah)
                                            <input type="hidden" name="personnels[{{ $personnel->id }}][biaya_taksi]" value="0">
                                        @endunless
                                    </div>
                                @endforeach
                            </fieldset>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        {{-- ============ KONTROL (KANAN) ============ --}}
        <aside class="space-y-4 lg:sticky lg:top-20">

            {{-- Generate Laporan (atas) --}}
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
                <div class="flex items-start gap-3 mb-3">
                    <span class="w-9 h-9 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-500 flex items-center justify-center shrink-0"><i data-lucide="sparkles" class="w-4 h-4"></i></span>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Generate Laporan</p>
                        <p class="text-xs text-slate-500 mt-0.5">Buat draf laporan perjalanan dinas otomatis.</p>
                    </div>
                </div>
                <button type="button" disabled title="Segera hadir"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    Generate Laporan
                    <span class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-slate-200 text-slate-500">Segera</span>
                </button>
            </section>

            {{-- Pengajuan (bawah) --}}
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 space-y-4">
                @if($spjApproved)
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">SPJ disetujui</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $travelOrder->spjReviewer ? 'Oleh ' . $travelOrder->spjReviewer->name . '. ' : '' }}Kwitansi siap dicetak (tab Biaya).</p>
                        </div>
                    </div>
                @elseif($spjSubmitted)
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i data-lucide="send" class="w-4 h-4"></i></span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Menunggu persetujuan</p>
                            <p class="text-xs text-slate-500 mt-0.5">Diajukan {{ $travelOrder->spj_submitted_at?->locale('id')->diffForHumans() }}. Bisa ditarik selama belum ditinjau.</p>
                        </div>
                    </div>
                @elseif($spjRevision)
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i data-lucide="file-warning" class="w-4 h-4"></i></span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Perlu revisi</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $travelOrder->spj_catatan ?: 'Perbaiki biaya rampung lalu ajukan ulang.' }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-start gap-3">
                        <span class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0"><i data-lucide="receipt-text" class="w-4 h-4"></i></span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Input biaya rampung</p>
                            <p class="text-xs text-slate-500 mt-0.5">Isi realisasi biaya, lalu simpan atau ajukan.</p>
                        </div>
                    </div>
                @endif

                {{-- Total Realisasi --}}
                <div class="border-t border-slate-100 pt-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Realisasi</p>
                    <p class="text-2xl font-extrabold text-emerald-700" x-text="rp(grandTotal)"></p>
                </div>

                {{-- Aksi --}}
                @if($spjEditable)
                    <div class="space-y-2">
                        <button type="submit" form="spj-form"
                            onclick="document.getElementById('spj-then-submit').value='1'"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                            <i data-lucide="send" class="w-4 h-4"></i> Simpan &amp; Ajukan
                        </button>
                        <button type="submit" form="spj-form"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                            <i data-lucide="save" class="w-4 h-4"></i> Simpan Draf
                        </button>
                    </div>
                @elseif($spjSubmitted)
                    <form method="POST" action="{{ route('staf.packages.travel-orders.spj.withdraw', [$package, $travelOrder]) }}"
                        onsubmit="return confirm('Tarik kembali pengajuan SPJ ke Draf?');">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                            <i data-lucide="undo-2" class="w-4 h-4"></i> Tarik Pengajuan
                        </button>
                    </form>
                @elseif($spjApproved)
                    <span class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <i data-lucide="lock" class="w-4 h-4"></i> Terkunci
                    </span>
                @endif
            </section>
        </aside>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endcomponent
