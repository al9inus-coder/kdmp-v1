@component('layouts.kdmp')
    @section('title', 'Arsip Dokumen')

    @php
        // Meta tampilan per jenis dokumen (class literal agar terbaca Tailwind JIT).
        $typeMeta = [
            'SPPD' => ['icon' => 'printer', 'chip' => 'bg-emerald-50 border-emerald-100 text-emerald-500'],
            'Surat Permohonan' => ['icon' => 'file-text', 'chip' => 'bg-blue-50 border-blue-100 text-blue-500'],
            'Surat Tugas' => ['icon' => 'file-output', 'chip' => 'bg-amber-50 border-amber-100 text-amber-500'],
            'Kwitansi' => ['icon' => 'receipt', 'chip' => 'bg-rose-50 border-rose-100 text-rose-500'],
            'Pengeluaran Riil' => ['icon' => 'receipt-text', 'chip' => 'bg-violet-50 border-violet-100 text-violet-500'],
            'Laporan Perjalanan Dinas' => ['icon' => 'file-check', 'chip' => 'bg-indigo-50 border-indigo-100 text-indigo-500'],
        ];
        $defaultMeta = ['icon' => 'file', 'chip' => 'bg-slate-50 border-slate-100 text-slate-500'];
    @endphp

    <div class="space-y-6" x-data="{
        level: 'root',
        y: '',
        t: '',
        q: '',
        openYear(year) { this.level = 'year'; this.y = year; this.t = ''; this.q = ''; },
        openType(type) { this.level = 'type'; this.t = type; this.q = ''; },
        goRoot() { this.level = 'root'; this.y = ''; this.t = ''; this.q = ''; },
        match(s) { return !this.q || s.toLowerCase().includes(this.q.toLowerCase()); },
    }">
        <x-ui.toast />

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="folder-open" class="w-6 h-6 text-indigo-600"></i>
                    Arsip <span class="text-indigo-600">Dokumen</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">Semua dokumen yang dihasilkan aplikasi, tersusun per tahun anggaran dan jenis dokumen.</p>
            </div>
        </div>

        {{-- Breadcrumb + pencarian --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5 text-sm font-semibold">
                <button type="button" @click="goRoot()"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg transition-colors"
                    :class="level === 'root' ? 'text-indigo-700 bg-indigo-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                    <i data-lucide="archive" class="w-4 h-4"></i> Arsip
                </button>

                <template x-if="level !== 'root'">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                        <button type="button" @click="level = 'year'; t = ''; q = ''"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg transition-colors"
                            :class="level === 'year' ? 'text-indigo-700 bg-indigo-50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50'">
                            <i data-lucide="folder" class="w-4 h-4 text-amber-400"></i> <span x-text="y"></span>
                        </button>
                    </span>
                </template>

                <template x-if="level === 'type'">
                    <span class="flex items-center gap-1.5">
                        <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-indigo-700 bg-indigo-50" x-text="t"></span>
                    </span>
                </template>
            </div>

            <div class="relative" x-show="level === 'type'" style="display:none">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" x-model="q" placeholder="Cari dokumen..."
                    class="w-56 pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
            </div>
        </div>

        {{-- Konten --}}
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5 min-h-[320px]">
            {{-- Level root: semua folder tahun dalam satu grid --}}
            @if (count($tree))
                <div x-show="level === 'root'" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($tree as $year => $types)
                        @php $docCount = collect($types)->flatten(1)->count(); @endphp
                        <button type="button" @click="openYear('{{ $year }}')"
                            class="group flex flex-col items-center text-center gap-2 p-5 rounded-2xl border border-slate-200 bg-white hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-md transition-all">
                            <span class="relative">
                                <i data-lucide="folder" class="w-14 h-14 text-amber-400 fill-amber-100 group-hover:scale-105 transition-transform"></i>
                            </span>
                            <span class="font-black text-slate-800">{{ $year }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">{{ count($types) }} jenis &middot; {{ $docCount }} dokumen</span>
                        </button>
                    @endforeach
                </div>

                {{-- Level year: folder jenis dokumen per tahun --}}
                @foreach ($tree as $year => $types)
                    <div x-show="level === 'year' && y === '{{ $year }}'" style="display:none"
                        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($types as $type => $files)
                            @php $meta = $typeMeta[$type] ?? $defaultMeta; @endphp
                            <button type="button" @click="openType('{{ $type }}')"
                                class="group flex flex-col items-center text-center gap-2 p-5 rounded-2xl border border-slate-200 bg-white hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-md transition-all">
                                <span class="w-14 h-14 rounded-2xl border flex items-center justify-center {{ $meta['chip'] }} group-hover:scale-105 transition-transform">
                                    <i data-lucide="{{ $meta['icon'] }}" class="w-7 h-7"></i>
                                </span>
                                <span class="font-bold text-slate-800 text-sm leading-tight">{{ $type }}</span>
                                <span class="text-[11px] font-semibold text-slate-400">{{ count($files) }} dokumen</span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Level type: daftar dokumen --}}
                    @foreach ($types as $type => $files)
                        @php $meta = $typeMeta[$type] ?? $defaultMeta; @endphp
                        <div x-show="level === 'type' && y === '{{ $year }}' && t === '{{ $type }}'" style="display:none"
                            class="divide-y divide-slate-100">
                            @foreach ($files as $f)
                                <div class="py-3 px-1 flex items-center justify-between gap-4"
                                    x-show="match({{ \Illuminate\Support\Js::from($f['label'] . ' ' . $f['sub']) }})">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 {{ $meta['chip'] }}">
                                            <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 text-sm leading-snug truncate">{{ $f['label'] }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $f['sub'] }}</p>
                                        </div>
                                    </div>
                                    @if ($f['action'] === 'download')
                                        <a href="{{ $f['url'] }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm shrink-0">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh
                                        </a>
                                    @elseif ($f['action'] === 'popup')
                                        <button type="button"
                                            onclick="window.open('{{ $f['url'] }}', 'arsip-cetak', 'width=900,height=700')"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm shrink-0">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                                        </button>
                                    @else
                                        <button type="button"
                                            onclick="window.open('{{ $f['url'] }}', '_blank')"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm shrink-0">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endforeach
            @else
                {{-- Arsip kosong --}}
                <div class="py-20 flex flex-col items-center justify-center text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <i data-lucide="folder-open" class="w-8 h-8 text-slate-400"></i>
                    </div>
                    <p class="font-medium text-slate-600">Arsip masih kosong.</p>
                    <p class="text-sm text-slate-400 mt-1 max-w-sm">Dokumen akan muncul di sini setelah ada SPPD yang disetujui — SPPD, Surat Tugas, Kwitansi, Pengeluaran Riil, dan Laporan.</p>
                </div>
            @endif
        </div>
    </div>
@endcomponent
