@php
    $package = $procurementPackage->package;
    $pagu = (float) ($package->pagu ?? 0);

    // Estimasi termurah dari survei referensi harga (harga > 0 saja)
    $items = $procurementPackage->technicalSpecification?->items ?? collect();
    $groupedRefs = $procurementPackage->priceReferences->groupBy('nama_barang_jasa');
    $estimasiTermurah = $items->sum(function ($item) use ($groupedRefs) {
        $valid = $groupedRefs->get($item->nama_barang_jasa, collect())->filter(fn($r) => (float) $r->harga_satuan > 0);
        return $valid->isNotEmpty() ? $valid->min('harga_satuan') * (float) $item->volume : 0;
    });

    $tanggalPesanan  = $process->tanggal_surat_pesanan?->format('Y-m-d');
    $tanggalDiterima = $process->tanggal_barang_diterima?->format('Y-m-d');
@endphp

<div x-data="kabidSuratPesanan({
        start: @js(old('tanggal_surat_pesanan', $tanggalPesanan)),
        end: @js(old('tanggal_barang_diterima', $tanggalDiterima)),
        nilai: {{ (float) old('nilai_kontrak', $process->nilai_kontrak ?? 0) }},
        pagu: {{ $pagu }},
        estimasi: {{ $estimasiTermurah }},
    })">
    <form method="POST" id="form-surat-pesanan"
          action="{{ route('kabid.procurement-packages.procurement-process.order.update', $package) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- Kolom kiri: nomor + nilai kontrak + catatan --}}
            <div class="space-y-4">
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100">
                    <div class="p-4">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor Surat Pesanan</label>
                        <input type="text" name="nomor_surat_pesanan"
                            value="{{ old('nomor_surat_pesanan', $process->nomor_surat_pesanan) }}"
                            placeholder="Contoh: 027/01/SP/2026"
                            class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                        <p class="text-[11px] text-slate-400 mt-1.5">
                            <i data-lucide="info" class="w-3 h-3 inline-block -mt-0.5"></i>
                            Sesuaikan dengan nomor surat pesanan pada sistem e-Katalog.
                        </p>
                    </div>

                    <div class="p-4">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nilai Kontrak (Rp)</label>
                        <input type="number" name="nilai_kontrak" min="0" step="any" x-model.number="nilai" placeholder="0"
                            class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">

                        {{-- Pembanding hidup vs pagu & estimasi survei --}}
                        <div class="mt-3 space-y-2" x-show="nilai > 0" x-transition.opacity>
                            <div>
                                <div class="flex items-center justify-between text-[11px] font-semibold mb-1">
                                    <span class="text-slate-400 uppercase tracking-wide">Terhadap Pagu</span>
                                    <span :class="nilai > pagu ? 'text-rose-600' : 'text-emerald-600'"
                                        x-text="pagu > 0 ? Math.round(nilai / pagu * 100) + '%' : '-'"></span>
                                </div>
                                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500"
                                        :class="nilai > pagu ? 'bg-gradient-to-r from-rose-400 to-rose-600' : 'bg-gradient-to-r from-emerald-400 to-emerald-500'"
                                        :style="`width: ${pagu > 0 ? Math.min(nilai / pagu * 100, 100) : 0}%`"></div>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] font-semibold">
                                <span class="text-slate-400">Pagu: <span class="text-emerald-600">{{ 'Rp ' . number_format($pagu, 0, ',', '.') }}</span></span>
                                @if($estimasiTermurah > 0)
                                    <span class="text-slate-400">Estimasi survei: <span class="text-slate-600">{{ 'Rp ' . number_format($estimasiTermurah, 0, ',', '.') }}</span></span>
                                @endif
                            </div>
                            <p x-show="nilai > pagu" x-transition.opacity style="display: none;"
                                class="text-[11px] font-bold text-rose-600 flex items-center gap-1">
                                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                Nilai kontrak melebihi pagu anggaran.
                            </p>
                            <p x-show="estimasi > 0 && nilai > 0 && nilai <= pagu && nilai > estimasi * 1.1" x-transition.opacity style="display: none;"
                                class="text-[11px] font-semibold text-amber-600 flex items-center gap-1">
                                <i data-lucide="search" class="w-3 h-3"></i>
                                Lebih tinggi dari estimasi termurah hasil survei &mdash; periksa kembali penawarannya.
                            </p>
                        </div>
                    </div>

                    <div class="p-4">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan Tambahan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea name="catatan" rows="3" placeholder="Catatan operasional..."
                            class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">{{ old('catatan', $process->catatan) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: kalender jadwal --}}
            <div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                        <i data-lucide="calendar-days" class="w-3.5 h-3.5 text-slate-400"></i>
                        Jadwal Pelaksanaan
                        <span class="font-normal text-slate-400">&mdash; klik tanggal surat pesanan, lalu tanggal barang diterima</span>
                    </label>

                    <div class="border border-slate-200 rounded-xl overflow-hidden select-none">
                        <div class="flex items-center justify-between px-3 py-2 bg-slate-50/70 border-b border-slate-100">
                            <button type="button" @click="prevMonth()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <p class="text-sm font-bold text-slate-700" x-text="monthLabel"></p>
                            <button type="button" @click="nextMonth()" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wide px-2 pt-2">
                            <template x-for="h in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']"><span x-text="h" class="py-1"></span></template>
                        </div>
                        <div class="grid grid-cols-7 gap-y-0.5 px-2 pb-2">
                            <template x-for="(cell, idx) in cells" :key="idx">
                                <div class="flex items-center justify-center">
                                    <button type="button" x-show="cell.d" @click="pick(cell.iso)"
                                        class="w-8 h-8 rounded-full text-xs font-semibold transition-all"
                                        :class="cellClass(cell.iso)" x-text="cell.d"></button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Ringkasan pilihan --}}
                    <div class="flex flex-wrap items-center gap-2 mt-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border"
                            :class="start ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200'">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            Surat Pesanan: <span x-text="start ? displayDate(start) : 'belum dipilih'"></span>
                        </span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-300"></i>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border"
                            :class="end ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-slate-50 text-slate-400 border-slate-200'">
                            <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                            Barang Diterima: <span x-text="end ? displayDate(end) : 'belum dipilih'"></span>
                        </span>
                        <span x-show="durasi > 0" x-transition.opacity
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <i data-lucide="timer" class="w-3.5 h-3.5"></i>
                            <span x-text="durasi"></span> hari kalender
                        </span>
                    </div>

                    {{-- Legenda --}}
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 pt-3 border-t border-dashed border-slate-200">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                            <span class="w-3 h-3 rounded-full ring-2 ring-inset ring-amber-400 bg-amber-50"></span> Hari ini
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                            <span class="w-3 h-3 rounded-full bg-emerald-600"></span> Surat Pesanan
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                            <span class="w-3 h-3 rounded-full bg-indigo-600"></span> Barang Diterima
                        </span>
                    </div>

                    {{-- Nilai kalender yang dikirim ke server --}}
                    <input type="hidden" name="tanggal_surat_pesanan" :value="start">
                    <input type="hidden" name="tanggal_barang_diterima" :value="end">
                </div>
            </div>
        </div>
    </form>
</div>

@once
<script>
    function kabidSuratPesanan(init) {
        const toIso = (d) => {
            const p = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
        };
        const fromIso = (s) => new Date(s + 'T00:00:00');
        const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        const anchor = init.start ? fromIso(init.start) : new Date();

        return {
            start: init.start || null,
            end: init.end || null,
            nilai: init.nilai || null,
            pagu: init.pagu,
            estimasi: init.estimasi,
            viewYear: anchor.getFullYear(),
            viewMonth: anchor.getMonth(),
            todayIso: toIso(new Date()),

            get monthLabel() {
                return bulan[this.viewMonth] + ' ' + this.viewYear;
            },
            get cells() {
                const first = new Date(this.viewYear, this.viewMonth, 1);
                const total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                const cells = [];
                for (let i = 0; i < first.getDay(); i++) cells.push({ d: null, iso: null });
                for (let d = 1; d <= total; d++) {
                    cells.push({ d, iso: toIso(new Date(this.viewYear, this.viewMonth, d)) });
                }
                return cells;
            },
            get durasi() {
                if (!this.start || !this.end) return 0;
                // Inklusif: hari surat pesanan dan hari diterima ikut dihitung
                return Math.round((fromIso(this.end) - fromIso(this.start)) / 86400000) + 1;
            },

            prevMonth() {
                if (--this.viewMonth < 0) { this.viewMonth = 11; this.viewYear--; }
            },
            nextMonth() {
                if (++this.viewMonth > 11) { this.viewMonth = 0; this.viewYear++; }
            },

            pick(iso) {
                if (!this.start || (this.start && this.end)) {
                    this.start = iso;
                    this.end = null;
                } else if (iso < this.start) {
                    this.start = iso;
                } else {
                    this.end = iso;
                }
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
                    return 'bg-emerald-100 text-emerald-700' +
                        (isToday ? ' ring-2 ring-inset ring-amber-400 font-bold' : '');
                }
                if (isToday) {
                    return 'ring-2 ring-inset ring-amber-400 bg-amber-50 text-amber-700 font-bold hover:bg-amber-100';
                }
                return 'text-slate-600 hover:bg-slate-100';
            },

            displayDate(iso) {
                const d = fromIso(iso);
                return d.getDate() + ' ' + bulan[d.getMonth()].slice(0, 3) + ' ' + d.getFullYear();
            },
        };
    }
</script>
@endonce
