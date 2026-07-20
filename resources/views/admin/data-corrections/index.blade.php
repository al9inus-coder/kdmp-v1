@component('layouts.kdmp')
@section('title', 'Koreksi Data')

<x-ui.toast />

<div class="max-w-2xl mx-auto"
    x-data="{
        q: '',
        results: [],
        loading: false,
        searched: false,
        async search() {
            const term = this.q.trim();
            if (term.length < 2) { this.results = []; this.searched = false; return; }
            this.loading = true;
            try {
                const res = await fetch(`{{ route('admin.data-corrections.search') }}?q=${encodeURIComponent(term)}`, { headers: { 'Accept': 'application/json' } });
                this.results = (await res.json()).results || [];
            } catch (e) {
                this.results = [];
            }
            this.searched = true;
            this.loading = false;
            this.$nextTick(() => window.lucide && lucide.createIcons());
        },
        clear() { this.q = ''; this.results = []; this.searched = false; this.$refs.input.focus(); },
    }">

    {{-- Hero: judul + kolom pencarian --}}
    <div class="text-center pt-16 pb-8 transition-all duration-300" :class="searched || loading ? 'pt-6' : 'pt-16'">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-200">
            <i data-lucide="file-pen-line" class="w-7 h-7"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-4">Data Correction Center</h1>
        <p class="text-sm text-slate-500 mt-1.5 max-w-md mx-auto">
            Cari data yang ingin dikoreksi — workflow, approval, dan riwayat audit tidak akan berubah.
        </p>
    </div>

    {{-- Kolom search --}}
    <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none">
            <i data-lucide="search" class="w-5 h-5 text-slate-400"></i>
        </div>
        <input type="text" x-ref="input" x-model="q" autofocus autocomplete="off"
            @input.debounce.300ms="search()"
            @keydown.enter.prevent
            @keydown.escape="clear()"
            placeholder="Ketik nama paket, tujuan SPD, penyedia, nomor dokumen..."
            class="w-full pl-13 pr-12 py-4 text-base bg-white border border-slate-200 rounded-full shadow-md shadow-slate-200/60
                focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400 transition-shadow hover:shadow-lg"
            style="padding-left: 3.25rem;">
        <button type="button" x-show="q.length" x-cloak @click="clear()"
            class="absolute inset-y-0 right-0 flex items-center pr-5 text-slate-400 hover:text-slate-600 transition-colors">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    {{-- Hint jenis data (hanya saat belum mencari) --}}
    <div x-show="!searched && !loading" class="flex flex-wrap items-center justify-center gap-2 mt-6">
        @foreach($types as $key => $def)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $def['chip'] }}">
                <i data-lucide="{{ $def['icon'] }}" class="w-3.5 h-3.5"></i> {{ $def['label'] }}
            </span>
        @endforeach
    </div>

    {{-- Loading --}}
    <div x-show="loading" x-cloak class="text-center mt-10">
        <i data-lucide="loader-circle" class="w-6 h-6 text-emerald-500 mx-auto animate-spin"></i>
        <p class="text-xs text-slate-400 mt-2">Mencari…</p>
    </div>

    {{-- Hasil pencarian --}}
    <div x-show="searched && !loading" x-cloak class="mt-8">
        <template x-if="results.length">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 px-1"
                    x-text="results.length + ' hasil ditemukan'"></p>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-md overflow-hidden divide-y divide-slate-100">
                    <template x-for="item in results" :key="item.type + '-' + item.editUrl">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4 hover:bg-slate-50/70 transition-colors">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="item.iconBg">
                                <i :data-lucide="item.icon" class="w-5 h-5"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                        :class="item.chip" x-text="item.label"></span>
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span x-text="item.status"></span>
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm mt-1 leading-snug" x-text="item.title"></h3>
                                <p class="text-xs text-slate-400 mt-0.5 truncate" x-text="item.subtitle"></p>
                            </div>

                            <div class="flex items-center gap-2.5 shrink-0">
                                <a x-show="item.corrections > 0" :href="item.historyUrl"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors"
                                    title="Lihat riwayat koreksi">
                                    <i data-lucide="history" class="w-3.5 h-3.5"></i>
                                    <span x-text="item.corrections + ' koreksi'"></span>
                                </a>
                                <a :href="item.editUrl"
                                    class="inline-flex items-center px-3.5 py-2 text-xs font-semibold text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-sm transition-colors">
                                    <i data-lucide="file-pen-line" class="w-3.5 h-3.5 mr-1.5"></i> Koreksi
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="!results.length">
            <div class="text-center py-10">
                <i data-lucide="search-x" class="w-8 h-8 text-slate-300 mx-auto"></i>
                <p class="text-sm font-semibold text-slate-500 mt-3">Tidak ada data yang cocok</p>
                <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain — nama paket, tujuan SPD, nama penyedia, atau nomor dokumen.</p>
            </div>
        </template>
    </div>

    {{-- Reassurance --}}
    <p class="flex items-center justify-center gap-2 text-[11px] text-slate-400 mt-12 mb-8">
        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
        Seluruh koreksi tercatat permanen di riwayat &amp; audit trail
    </p>
</div>
@endcomponent
