@component('layouts.kdmp')
    @section('title', 'Kalender Kegiatan')

    <div class="space-y-6" x-data="kabidKalender({
            tahun: {{ $tahun }},
            bulanAwal: {{ $bulanAwal }},
            todayIso: '{{ now()->format('Y-m-d') }}',
            selected: '{{ $tahun === now()->year ? now()->format('Y-m-d') : sprintf('%d-01-01', $tahun) }}',
            travels: @js($travels),
            contracts: @js($contracts),
            holidays: @js($holidays),
        })" x-init="refreshIcons()">
        <x-ui.toast />

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar-days" class="w-6 h-6 text-emerald-600"></i>
                    Kalender <span class="text-emerald-600">Kegiatan</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">Perjalanan dinas, masa kontrak, dan hari libur dalam satu tampilan.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Navigasi tahun --}}
                <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <a href="{{ route('kabid.kalender.index', ['tahun' => $tahun - 1]) }}" class="px-2.5 py-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                    <span class="px-2 text-sm font-extrabold text-slate-800">{{ $tahun }}</span>
                    <a href="{{ route('kabid.kalender.index', ['tahun' => $tahun + 1]) }}" class="px-2.5 py-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>

                {{-- Toggle mode --}}
                <div class="inline-flex items-center p-1 bg-slate-100 border border-slate-200 rounded-xl">
                    <button type="button" @click="setMode('bulan')"
                        class="px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all"
                        :class="mode === 'bulan' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">Bulan</button>
                    <button type="button" @click="setMode('tahun')"
                        class="px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all"
                        :class="mode === 'tahun' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">Tahun</button>
                </div>
            </div>
        </div>

        {{-- Filter jenis event --}}
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="toggle('travel')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border border-emerald-200 bg-emerald-50 text-emerald-700 transition-opacity"
                :class="f.travel ? '' : 'opacity-40'">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Perjalanan Dinas
            </button>
            <button type="button" @click="toggle('contract')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border border-indigo-200 bg-indigo-50 text-indigo-700 transition-opacity"
                :class="f.contract ? '' : 'opacity-40'">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Kontrak
            </button>
            <button type="button" @click="toggle('holiday')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border border-rose-200 bg-rose-50 text-rose-700 transition-opacity"
                :class="f.holiday ? '' : 'opacity-40'">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span> Hari Libur
            </button>
        </div>

        {{-- ================= MODE BULAN ================= --}}
        <div x-show="mode === 'bulan'" class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_300px] gap-6 items-start">

            {{-- Grid bulan --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-4 sm:p-6 select-none">
                <div class="flex items-center justify-between mb-3">
                    <button type="button" @click="prevMonth()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </button>
                    <p class="text-lg font-extrabold text-slate-800" x-text="monthLabel"></p>
                    <button type="button" @click="nextMonth()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                        <i data-lucide="chevron-right" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="grid grid-cols-7 text-center text-[11px] font-bold uppercase tracking-wide mb-1">
                    <span class="py-1.5 text-rose-400">Min</span>
                    <template x-for="h in ['Sen','Sel','Rab','Kam','Jum','Sab']"><span x-text="h" class="py-1.5 text-slate-400"></span></template>
                </div>
                <div class="grid grid-cols-7 gap-y-1">
                    <template x-for="(cell, idx) in cells" :key="idx">
                        <div>
                            <button type="button" x-show="cell.d" @click="pick(cell.iso)"
                                class="w-full h-12 sm:h-14 text-sm transition-all flex flex-col items-center justify-center leading-none gap-1"
                                :class="cellClass(cell.iso)">
                                <span x-text="cell.d"></span>
                                <i data-lucide="flag" class="w-3 h-3" x-show="dayType(cell.iso) === 'deadline'" style="display:none"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 mt-4 pt-4 border-t border-dashed border-slate-200">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-3 h-3 rounded-full ring-2 ring-inset ring-amber-400 bg-amber-50"></span> Hari ini</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-3 h-3 rounded-full bg-emerald-100 border border-emerald-300"></span> Perjalanan dinas</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-3 h-3 rounded-full bg-indigo-50 border border-indigo-300"></span> Masa kontrak</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-3 h-3 rounded-full bg-indigo-600"></span> Batas akhir kontrak</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-3 h-3 rounded-full bg-rose-100 border border-rose-300"></span> Libur</span>
                </div>
            </div>

            {{-- Panel kanan --}}
            <aside class="space-y-4 lg:sticky lg:top-20">
                {{-- Agenda tanggal terpilih --}}
                <section class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-xs font-black text-slate-500 uppercase tracking-widest">Agenda &middot; <span x-text="selectedLabel"></span></h2>
                    </div>
                    <div class="p-4 space-y-1">
                        <template x-if="agenda.length === 0">
                            <p class="text-xs text-slate-400 text-center py-6">Tidak ada kegiatan pada tanggal ini.</p>
                        </template>
                        <template x-for="(item, i) in agenda" :key="i">
                            <a :href="item.url || '#'" @click="if (!item.url) $event.preventDefault()"
                                class="flex items-start gap-3 px-2 py-2.5 rounded-xl transition-colors"
                                :class="item.url ? 'hover:bg-slate-50 cursor-pointer' : 'cursor-default'">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                    :class="{
                                        'bg-emerald-100 text-emerald-600': item.tone === 'emerald',
                                        'bg-indigo-100 text-indigo-600': item.tone === 'indigo',
                                        'bg-rose-100 text-rose-600': item.tone === 'rose',
                                    }">
                                    <i :data-lucide="item.icon" class="w-4 h-4"></i>
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-bold text-slate-800 leading-snug truncate" x-text="item.label"></span>
                                    <span class="block text-xs text-slate-400 mt-0.5" x-text="item.sub"></span>
                                </span>
                            </a>
                        </template>
                    </div>
                </section>

                {{-- Tenggat terdekat --}}
                <section class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden" x-show="tenggat.length">
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                        <h2 class="text-xs font-black text-slate-500 uppercase tracking-widest">Tenggat Terdekat</h2>
                    </div>
                    <div class="p-4 divide-y divide-slate-50">
                        <template x-for="(e, i) in tenggat" :key="i">
                            <a :href="e.url" class="flex items-center justify-between gap-2 py-2 group">
                                <span class="inline-flex items-center gap-2 min-w-0 text-sm text-slate-700 group-hover:text-indigo-700">
                                    <i data-lucide="flag" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                    <span class="truncate font-semibold" x-text="e.label"></span>
                                </span>
                                <span class="text-[11px] font-bold rounded-full px-2 py-0.5 shrink-0"
                                    :class="e.sisa <= 3 ? 'bg-rose-50 text-rose-700' : (e.sisa <= 7 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700')"
                                    x-text="e.sisa === 0 ? 'Hari ini' : 'Sisa ' + e.sisa + ' hari'"></span>
                            </a>
                        </template>
                    </div>
                </section>
            </aside>
        </div>

        {{-- ================= MODE TAHUN ================= --}}
        <div x-show="mode === 'tahun'" style="display:none" class="space-y-6">

            {{-- Kalender penuh 12 bulan (ala kalender dinding) --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm font-extrabold text-slate-800">Kalender {{ $tahun }}</p>
                    <p class="text-[11px] font-semibold text-slate-400">Klik tanggal untuk membuka agendanya &middot; arahkan kursor untuk detail</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    <template x-for="m in 12" :key="m">
                        <div class="border rounded-xl p-3"
                            :class="(tahun === Number(todayIso.slice(0, 4)) && (m - 1) === new Date(todayIso).getMonth()) ? 'border-amber-300 ring-2 ring-amber-200' : 'border-slate-200'">
                            <button type="button" @click="openMonth(m - 1)"
                                class="w-full text-center text-xs font-black text-slate-700 hover:text-indigo-600 transition-colors mb-2"
                                x-text="bulanNama[m - 1]"></button>

                            <div class="grid grid-cols-7 text-center text-[10px] font-bold uppercase mb-1">
                                <span class="text-rose-400">M</span>
                                <template x-for="h in ['S','S','R','K','J','S']"><span class="text-slate-300" x-text="h"></span></template>
                            </div>
                            <div class="grid grid-cols-7 gap-[2px]">
                                <template x-for="(cell, ci) in buildCells(m - 1)" :key="ci">
                                    <button type="button" @click="openDate(cell.iso)"
                                        class="h-8 w-full rounded-md text-[11px] font-semibold flex items-center justify-center transition-colors"
                                        :class="cell.iso ? yearCellClass(cell.iso) : 'invisible'"
                                        :title="cell.iso ? tipOf(cell.iso) : ''"
                                        x-text="cell.d"></button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Linimasa kontrak --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5" x-show="ganttContracts.length">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Linimasa Kontrak {{ $tahun }}</p>
                <div class="space-y-2.5">
                    <template x-for="(e, i) in ganttContracts" :key="i">
                        <a :href="e.url" class="flex items-center gap-3 group">
                            <span class="w-40 shrink-0 text-xs font-semibold text-slate-700 truncate group-hover:text-indigo-700" :title="e.label" x-text="e.label"></span>
                            <span class="flex-1 relative h-4 rounded-full bg-slate-100">
                                <span class="absolute top-0.5 bottom-0.5 rounded-full"
                                    :class="e.finished ? 'bg-emerald-300' : 'bg-indigo-300'"
                                    :style="barStyle(e)"></span>
                                <i data-lucide="flag" class="w-3.5 h-3.5 text-indigo-600 absolute -top-0.5" :style="flagStyle(e)"></i>
                            </span>
                        </a>
                    </template>
                </div>
                <div class="flex justify-between text-[10px] font-bold text-slate-300 uppercase mt-3 pl-[172px]">
                    <template x-for="b in ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']">
                        <span x-text="b"></span>
                    </template>
                </div>
                <p class="text-[11px] text-slate-400 mt-3 flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2 rounded-full bg-indigo-300 inline-block"></span> Berjalan</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2 rounded-full bg-emerald-300 inline-block"></span> Selesai</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="flag" class="w-3 h-3 text-indigo-600"></i> Batas akhir</span>
                </p>
            </div>
        </div>
    </div>

    <script>
        function kabidKalender(cfg) {
            const bulanNama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const pad = n => String(n).padStart(2, '0');
            const toIso = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());

            return {
                tahun: cfg.tahun,
                mode: 'bulan',
                viewMonth: cfg.bulanAwal,
                selected: cfg.selected,
                todayIso: cfg.todayIso,
                f: { travel: true, contract: true, holiday: true },
                travels: cfg.travels || [],
                contracts: cfg.contracts || [],
                holidays: cfg.holidays || {},
                bulanNama,

                get monthLabel() { return bulanNama[this.viewMonth] + ' ' + this.tahun; },
                buildCells(m) {
                    const first = new Date(this.tahun, m, 1);
                    const total = new Date(this.tahun, m + 1, 0).getDate();
                    const cells = [];
                    for (let i = 0; i < first.getDay(); i++) cells.push({ d: null, iso: null });
                    for (let d = 1; d <= total; d++) cells.push({ d, iso: toIso(new Date(this.tahun, m, d)) });
                    return cells;
                },
                get cells() { return this.buildCells(this.viewMonth); },

                inRange(iso, e) { return iso >= e.start && iso <= e.end; },
                travelsOn(iso) { return this.f.travel ? this.travels.filter(e => this.inRange(iso, e)) : []; },
                contractsOn(iso) { return this.f.contract ? this.contracts.filter(e => this.inRange(iso, e)) : []; },
                deadlinesOn(iso) { return this.f.contract ? this.contracts.filter(e => e.end === iso) : []; },
                holidayOf(iso) { return this.f.holiday ? (this.holidays[iso] || null) : null; },

                // Prioritas warna: batas kontrak > libur > perjalanan dinas > masa kontrak.
                dayType(iso) {
                    if (!iso) return '';
                    if (this.deadlinesOn(iso).length) return 'deadline';
                    if (this.holidayOf(iso)) return 'holiday';
                    if (this.travelsOn(iso).length) return 'travel';
                    if (this.contractsOn(iso).length) return 'contract';
                    return '';
                },
                shiftIso(iso, n) {
                    const [y, m, d] = iso.split('-').map(Number);
                    return toIso(new Date(y, m - 1, d + n));
                },
                bandClass(iso, type) {
                    const l = this.dayType(this.shiftIso(iso, -1)) !== type;
                    const r = this.dayType(this.shiftIso(iso, 1)) !== type;
                    return l && r ? 'rounded-xl' : (l ? 'rounded-l-xl' : (r ? 'rounded-r-xl' : 'rounded-none'));
                },
                cellClass(iso) {
                    if (!iso) return '';
                    const t = this.dayType(iso);
                    const minggu = new Date(iso + 'T00:00:00').getDay() === 0;
                    let c = '';
                    if (t === 'deadline') c = 'bg-indigo-600 text-white font-bold rounded-xl shadow-sm';
                    else if (t === 'holiday') c = 'bg-rose-100 text-rose-700 font-bold rounded-xl';
                    else if (t === 'travel') c = 'bg-emerald-100 text-emerald-900 font-semibold ' + this.bandClass(iso, 'travel');
                    else if (t === 'contract') c = 'bg-indigo-50 text-indigo-700 font-semibold ' + this.bandClass(iso, 'contract');
                    else c = (minggu ? 'text-rose-400' : 'text-slate-600') + ' hover:bg-slate-100 rounded-xl';
                    if (iso === this.todayIso) c += ' ring-2 ring-amber-400';
                    if (iso === this.selected) c += ' shadow-[inset_0_0_0_2px_#6366f1]';
                    return c;
                },
                // Sel kalender tahun: warna sama dengan mode bulan, ukuran kecil.
                yearCellClass(iso) {
                    const t = this.dayType(iso);
                    const minggu = new Date(iso + 'T00:00:00').getDay() === 0;
                    let c = '';
                    if (t === 'deadline') c = 'bg-indigo-600 text-white font-bold';
                    else if (t === 'holiday') c = 'bg-rose-100 text-rose-700 font-bold';
                    else if (t === 'travel') c = 'bg-emerald-100 text-emerald-800';
                    else if (t === 'contract') c = 'bg-indigo-50 text-indigo-700';
                    else c = (minggu ? 'text-rose-400' : 'text-slate-500') + ' hover:bg-slate-100';
                    if (iso === this.todayIso) c += ' ring-2 ring-amber-400';
                    return c;
                },
                // Klik tanggal di kalender tahun: buka mode bulan pada tanggal itu.
                openDate(iso) {
                    if (!iso) return;
                    this.selected = iso;
                    this.viewMonth = Number(iso.split('-')[1]) - 1;
                    this.mode = 'bulan';
                    this.refreshIcons();
                },
                // Tooltip: tanggal + ringkasan kejadian hari itu.
                tipOf(iso) {
                    const [y, m, d] = iso.split('-').map(Number);
                    const parts = [];
                    const hol = this.holidayOf(iso);
                    if (hol) parts.push('Libur: ' + hol);
                    this.deadlinesOn(iso).forEach(e => parts.push('Batas akhir: ' + e.label));
                    this.travelsOn(iso).forEach(e => parts.push(e.label));
                    if (!parts.length && this.contractsOn(iso).length) parts.push(this.contractsOn(iso).length + ' kontrak berjalan');
                    return d + ' ' + bulanNama[m - 1] + (parts.length ? ' — ' + parts.join(' · ') : '');
                },

                pick(iso) { if (!iso) return; this.selected = iso; this.refreshIcons(); },
                prevMonth() {
                    if (this.viewMonth === 0) { window.location = '{{ route('kabid.kalender.index') }}?tahun=' + (this.tahun - 1) + '&bulan=11'; return; }
                    this.viewMonth--;
                },
                nextMonth() {
                    if (this.viewMonth === 11) { window.location = '{{ route('kabid.kalender.index') }}?tahun=' + (this.tahun + 1) + '&bulan=0'; return; }
                    this.viewMonth++;
                },
                openMonth(m) { this.viewMonth = m; this.mode = 'bulan'; this.refreshIcons(); },
                setMode(m) { this.mode = m; this.refreshIcons(); },
                toggle(k) { this.f[k] = !this.f[k]; },

                get selectedLabel() {
                    const [y, m, d] = this.selected.split('-').map(Number);
                    const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][new Date(y, m - 1, d).getDay()];
                    return hari + ', ' + d + ' ' + bulanNama[m - 1];
                },
                diffDays(a, b) { return Math.round((new Date(b) - new Date(a)) / 86400000); },
                rangeLabel(e) {
                    const f = iso => { const [y, m, d] = iso.split('-').map(Number); return d + ' ' + bulanNama[m - 1].slice(0, 3); };
                    return e.start === e.end ? f(e.start) : f(e.start) + '–' + f(e.end);
                },

                get agenda() {
                    const iso = this.selected;
                    const items = [];
                    const hol = this.holidayOf(iso);
                    if (hol) items.push({ icon: 'sun', tone: 'rose', label: hol, sub: 'Hari libur', url: null });
                    this.travelsOn(iso).forEach(e => items.push({ icon: 'plane', tone: 'emerald', label: e.label, sub: e.sub + ' · ' + this.rangeLabel(e), url: e.url }));
                    this.contractsOn(iso).forEach(e => {
                        const hariKe = this.diffDays(e.start, iso) + 1;
                        const total = this.diffDays(e.start, e.end) + 1;
                        const isDeadline = e.end === iso;
                        items.push({
                            icon: isDeadline ? 'flag' : 'truck', tone: 'indigo', label: e.label,
                            sub: isDeadline ? 'Batas akhir kontrak' : 'Pelaksanaan hari ke-' + hariKe + ' dari ' + total,
                            url: e.url,
                        });
                    });
                    return items;
                },

                get tenggat() {
                    if (!this.f.contract) return [];
                    return this.contracts
                        .filter(e => !e.finished && e.end >= this.todayIso)
                        .sort((a, b) => a.end.localeCompare(b.end))
                        .slice(0, 5)
                        .map(e => ({ ...e, sisa: this.diffDays(this.todayIso, e.end) }));
                },

                // Linimasa tahun (posisi % dalam setahun)
                pct(iso) {
                    const start = new Date(this.tahun, 0, 1);
                    const days = (new Date(this.tahun, 11, 31) - start) / 86400000 + 1;
                    const d = Math.max(0, Math.min(days, (new Date(iso + 'T00:00:00') - start) / 86400000));
                    return d / days * 100;
                },
                barStyle(e) {
                    const s = e.start < this.tahun + '-01-01' ? this.tahun + '-01-01' : e.start;
                    const t = e.end > this.tahun + '-12-31' ? this.tahun + '-12-31' : e.end;
                    const l = this.pct(s);
                    return 'left:' + l.toFixed(2) + '%; width:' + Math.max(0.8, this.pct(t) - l).toFixed(2) + '%;';
                },
                flagStyle(e) { return 'left:' + Math.min(98, this.pct(e.end)).toFixed(2) + '%;'; },
                get ganttContracts() { return this.f.contract ? [...this.contracts].sort((a, b) => a.start.localeCompare(b.start)) : []; },

                refreshIcons() { this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); },
            };
        }
    </script>
@endcomponent
