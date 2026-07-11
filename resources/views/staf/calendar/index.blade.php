@component('layouts.kdmp')
    @section('title', 'Kalender Kegiatan')

    <div class="space-y-6" x-data="stafKalender({
            tahun: {{ $tahun }},
            bulanAwal: {{ $bulanAwal }},
            todayIso: '{{ now()->format('Y-m-d') }}',
            selected: '{{ $tahun === now()->year ? now()->format('Y-m-d') : sprintf('%d-01-01', $tahun) }}',
            travels: @js($travels),
            holidays: @js($holidays),
            eligibleSubActivities: @js($eligibleSubActivities),
        })" x-init="init()" @mouseup.window="endDrag()">
        <x-ui.toast />

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="calendar-days" class="w-6 h-6 text-emerald-600"></i>
                    Kalender <span class="text-emerald-600">Kegiatan</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">Jadwal perjalanan dinas dan hari libur. Klik ganda tanggal untuk membuat SPPD baru.</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Navigasi tahun --}}
                <div class="inline-flex items-center bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <a href="{{ route('staf.kalender.index', ['tahun' => $tahun - 1]) }}" class="px-2.5 py-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </a>
                    <span class="px-2 text-sm font-extrabold text-slate-800">{{ $tahun }}</span>
                    <a href="{{ route('staf.kalender.index', ['tahun' => $tahun + 1]) }}" class="px-2.5 py-2 text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors">
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
                <div class="grid grid-cols-7 gap-px bg-slate-200 border border-slate-200 rounded-xl overflow-hidden">
                    <template x-for="(cell, idx) in cells" :key="idx">
                        <div class="bg-white" :class="cell.iso === todayIso ? 'bg-amber-50/10' : ''">
                            <button type="button" x-show="cell.d" :data-iso="cell.iso"
                                @click="pick(cell.iso)" @dblclick="openCreateModal(cell.iso)"
                                @mousedown.prevent="startDrag(cell.iso)" @mouseenter="extendDrag(cell.iso)" @mouseup="endDrag()"
                                @touchstart="startDrag(cell.iso)"
                                class="w-full min-h-[90px] sm:min-h-[110px] p-1.5 sm:p-2 transition-all flex flex-col items-stretch text-left group select-none touch-none"
                                :class="inDrag(cell.iso) ? 'ring-2 ring-inset ring-emerald-500 bg-emerald-50/60' : (cell.iso === selected ? 'ring-2 ring-inset ring-indigo-500 bg-indigo-50/30' : 'hover:bg-slate-50')">
                                
                                {{-- Tanggal & Indikator --}}
                                <div class="flex items-center justify-between w-full mb-1.5">
                                    <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold transition-colors"
                                        :class="cell.iso === todayIso ? 'bg-amber-400 text-amber-900 shadow-sm' : (new Date(cell.iso + 'T00:00:00').getDay() === 0 ? 'text-rose-500 group-hover:bg-rose-50' : 'text-slate-700 group-hover:bg-slate-100')"
                                        x-text="cell.d"></span>
                                    
                                    <div class="flex items-center gap-1 opacity-80">
                                        <span x-show="holidayOf(cell.iso)" style="display:none" :title="holidayOf(cell.iso)">
                                            <i data-lucide="sun" class="w-3.5 h-3.5 text-rose-500"></i>
                                        </span>
                                    </div>
                                </div>

                                {{-- Stacked Bars --}}
                                <div class="space-y-1 flex-1 overflow-hidden pointer-events-none">
                                    <template x-for="(e, i) in travelsOn(cell.iso)" :key="'t'+i">
                                        <div class="h-5 bg-emerald-100 border border-emerald-200 text-emerald-800 text-[10px] px-1.5 rounded flex items-center font-semibold truncate shadow-sm cursor-help pointer-events-auto"
                                            :title="e.sub + ' (' + e.label.replace('SPPD — ', '') + ')'">
                                            <span class="truncate" x-text="e.sub"></span>
                                        </div>
                                    </template>
                                </div>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 mt-5 pt-4 border-t border-dashed border-slate-200">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-3 h-3 rounded-full ring-2 ring-inset ring-amber-400 bg-amber-50"></span> Hari ini</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><span class="w-4 h-3 rounded bg-emerald-100 border border-emerald-200"></span> Perjalanan dinas</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500"><i data-lucide="sun" class="w-3.5 h-3.5 text-rose-500"></i> Libur</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-400"><i data-lucide="mouse-pointer-click" class="w-3.5 h-3.5"></i> Klik &amp; seret tanggal untuk buat SPPD</span>
                </div>
            </div>

            {{-- Panel kanan --}}
            <aside class="space-y-4 lg:sticky lg:top-20">
                {{-- Agenda tanggal terpilih --}}
                <section class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-2">
                        <h2 class="text-xs font-black text-slate-500 uppercase tracking-widest">Agenda &middot; <span x-text="selectedLabel"></span></h2>
                        <button type="button" @click="openCreateModal(selected)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors shrink-0">
                            <i data-lucide="plus" class="w-3 h-3"></i> SPPD
                        </button>
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
                        <button type="button" @click="openCreateModal(selected)" class="mt-4 w-full px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-sm font-bold border border-indigo-200 rounded-lg transition-colors flex items-center justify-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> Buat SPPD
                        </button>
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
                    <p class="text-[11px] font-semibold text-slate-400">Klik ganda tanggal untuk membuat SPPD &middot; arahkan kursor untuk detail</p>
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
                                    <button type="button" @click="openDate(cell.iso)" @dblclick="openCreateModal(cell.iso)"
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
        </div>

        {{-- Modal Pembuatan SPPD --}}
        <div x-show="modalOpen" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="modalOpen" @click.away="modalOpen = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i data-lucide="plane" class="h-5 w-5 text-emerald-600"></i>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                                    <h3 class="text-base font-semibold leading-6 text-slate-900" id="modal-title">Buat Perjalanan Dinas</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-slate-500 mb-4">
                                            Pilih sub kegiatan untuk perjalanan dinas
                                            <template x-if="modalStart === modalEnd">
                                                <span>pada tanggal <strong class="text-slate-700" x-text="formatDateStr(modalStart)"></strong>.</span>
                                            </template>
                                            <template x-if="modalStart !== modalEnd">
                                                <span><strong class="text-slate-700" x-text="formatDateStr(modalStart)"></strong> s.d. <strong class="text-slate-700" x-text="formatDateStr(modalEnd)"></strong> (<span x-text="rangeDays"></span> hari).</span>
                                            </template>
                                        </p>

                                        <label for="modal-sub" class="block text-sm font-medium leading-6 text-slate-900">Sub Kegiatan</label>
                                        <select id="modal-sub" x-model="modalPackage" class="mt-1 block w-full rounded-md border-0 py-2 pl-3 pr-10 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-emerald-600 sm:text-sm sm:leading-6">
                                            <option value="" disabled selected>-- Pilih Sub Kegiatan --</option>
                                            <template x-for="s in eligibleSubActivities" :key="s.package_id">
                                                <option :value="s.package_id" x-text="s.label"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="button" @click="submitModal" :disabled="!modalPackage" class="inline-flex w-full justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto">Buat SPPD</button>
                            <button type="button" @click="modalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Batal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function stafKalender(cfg) {
            const bulanNama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            const pad = n => String(n).padStart(2, '0');
            const toIso = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());

            return {
                tahun: cfg.tahun,
                mode: 'bulan',
                viewMonth: cfg.bulanAwal,
                selected: cfg.selected,
                todayIso: cfg.todayIso,
                f: { travel: true, holiday: true },
                travels: cfg.travels || [],
                holidays: cfg.holidays || {},
                eligibleSubActivities: cfg.eligibleSubActivities || [],
                bulanNama,

                // Modal state (rentang tanggal)
                modalOpen: false,
                modalStart: '',
                modalEnd: '',
                modalPackage: '',

                // Drag state
                dragging: false,
                dragStart: null,
                dragHover: null,

                init() {
                    this.refreshIcons();
                    // Touch: petakan koordinat jari ke sel tanggal (mouseenter tak ada di layar sentuh).
                    document.addEventListener('touchmove', (e) => this.touchMove(e), { passive: false });
                    document.addEventListener('touchend', () => this.endDrag());
                    document.addEventListener('touchcancel', () => this.endDrag());
                },
                touchMove(e) {
                    if (!this.dragging) return;
                    const t = e.touches[0];
                    if (!t) return;
                    const el = document.elementFromPoint(t.clientX, t.clientY);
                    const btn = el && el.closest('[data-iso]');
                    if (btn) {
                        const iso = btn.getAttribute('data-iso');
                        if (iso && iso !== this.dragStart) {
                            this.extendDrag(iso);
                            e.preventDefault(); // kunci scroll hanya saat benar-benar menyeret lintas tanggal
                        }
                    }
                },
                startDrag(iso) {
                    if (!iso) return;
                    this.dragging = true;
                    this.dragStart = iso;
                    this.dragHover = iso;
                },
                extendDrag(iso) {
                    if (this.dragging && iso) this.dragHover = iso;
                },
                endDrag() {
                    if (!this.dragging) return;
                    this.dragging = false;
                    // Hanya buka modal bila benar-benar diseret melintasi tanggal (bukan klik biasa).
                    if (this.dragStart && this.dragHover && this.dragStart !== this.dragHover) {
                        const [a, b] = [this.dragStart, this.dragHover].sort();
                        this.openCreateModal(a, b);
                    }
                    this.dragStart = this.dragHover = null;
                },
                inDrag(iso) {
                    if (!this.dragging || !this.dragStart || !iso) return false;
                    const [a, b] = [this.dragStart, this.dragHover].sort();
                    return iso >= a && iso <= b;
                },

                openCreateModal(startIso, endIso = null) {
                    if (!startIso) return;
                    this.modalStart = startIso;
                    this.modalEnd = endIso || startIso;
                    this.modalPackage = '';
                    this.modalOpen = true;
                    this.refreshIcons();
                },
                get rangeDays() {
                    if (!this.modalStart || !this.modalEnd) return 1;
                    return Math.round((new Date(this.modalEnd) - new Date(this.modalStart)) / 86400000) + 1;
                },
                submitModal() {
                    if (!this.modalPackage || !this.modalStart) return;
                    let url = `/staf/packages/${this.modalPackage}/travel-orders/create?date=${this.modalStart}`;
                    if (this.modalEnd && this.modalEnd !== this.modalStart) url += `&end=${this.modalEnd}`;
                    window.location.href = url;
                },
                formatDateStr(iso) {
                    if (!iso) return '';
                    const [y, m, d] = iso.split('-').map(Number);
                    return d + ' ' + this.bulanNama[m - 1] + ' ' + y;
                },

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
                holidayOf(iso) { return this.f.holiday ? (this.holidays[iso] || null) : null; },

                dayType(iso) {
                    if (!iso) return '';
                    if (this.holidayOf(iso)) return 'holiday';
                    if (this.travelsOn(iso).length) return 'travel';
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
                yearCellClass(iso) {
                    const t = this.dayType(iso);
                    const minggu = new Date(iso + 'T00:00:00').getDay() === 0;
                    let c = '';
                    if (t === 'holiday') c = 'bg-rose-100 text-rose-700 font-bold';
                    else if (t === 'travel') c = 'bg-emerald-100 text-emerald-800';
                    else c = (minggu ? 'text-rose-400' : 'text-slate-500') + ' hover:bg-slate-100';
                    if (iso === this.todayIso) c += ' ring-2 ring-amber-400';
                    return c;
                },
                openDate(iso) {
                    if (!iso) return;
                    this.selected = iso;
                    this.viewMonth = Number(iso.split('-')[1]) - 1;
                    this.mode = 'bulan';
                    this.refreshIcons();
                },
                tipOf(iso) {
                    const [y, m, d] = iso.split('-').map(Number);
                    const parts = [];
                    const hol = this.holidayOf(iso);
                    if (hol) parts.push('Libur: ' + hol);
                    this.travelsOn(iso).forEach(e => parts.push('SPPD ' + e.sub));
                    return d + ' ' + bulanNama[m - 1] + (parts.length ? ' — ' + parts.join(' · ') : '');
                },

                pick(iso) { if (!iso) return; this.selected = iso; this.refreshIcons(); },
                prevMonth() {
                    if (this.viewMonth === 0) { window.location = '{{ route('staf.kalender.index') }}?tahun=' + (this.tahun - 1) + '&bulan=11'; return; }
                    this.viewMonth--;
                },
                nextMonth() {
                    if (this.viewMonth === 11) { window.location = '{{ route('staf.kalender.index') }}?tahun=' + (this.tahun + 1) + '&bulan=0'; return; }
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
                    return items;
                },

                refreshIcons() { this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }); },
            };
        }
    </script>
@endcomponent
