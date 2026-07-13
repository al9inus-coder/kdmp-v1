@php
    $package = $procurementPackage->package;
    $ts = $procurementPackage->technicalSpecification;
    $surat = $procurementPackage->procurementRequest;
    $refs = $procurementPackage->priceReferences;
    $items = $ts?->items ?? collect();

    $checks = [
        ['step' => 1, 'icon' => 'file-signature',  'label' => 'Informasi Kontrak',
         'desc' => 'Jenis kontrak & jangka waktu pelaksanaan terisi.',
         'done' => filled($procurementPackage->jenis_kontrak) && filled($procurementPackage->jangka_waktu_nilai)],
        ['step' => 2, 'icon' => 'shopping-basket', 'label' => 'Barang / Jasa',
         'desc' => $items->count() . ' item barang/jasa tercatat.',
         'done' => $items->isNotEmpty()],
        ['step' => 3, 'icon' => 'file-text',       'label' => 'Spesifikasi Teknis',
         'desc' => 'Draf latar belakang s.d. uraian pekerjaan tersusun.',
         'done' => $ts && filled($ts->latar_belakang) && filled($ts->uraian_pekerjaan)],
        ['step' => 4, 'icon' => 'tags',            'label' => 'Referensi Harga',
         'desc' => $refs->count() . ' referensi harga dari hasil survei.',
         'done' => $refs->isNotEmpty()],
        ['step' => 5, 'icon' => 'mail',            'label' => 'Surat Permohonan',
         'desc' => $surat ? 'Surat permohonan sudah dibuat.' : 'Surat permohonan belum dibuat.',
         'done' => (bool) $surat],
    ];

    $doneCount = collect($checks)->where('done', true)->count();
    $allDone = $doneCount === count($checks);
    $locked = $procurementPackage->workflow_status !== \App\Models\ProcurementPackage::WORKFLOW_DRAFT;

    // Estimasi termurah (harga > 0 saja)
    $groupedRefs = $refs->groupBy('nama_barang_jasa');
    $totalTermurah = $items->sum(function ($item) use ($groupedRefs) {
        $valid = $groupedRefs->get($item->nama_barang_jasa, collect())->filter(fn($r) => (float) $r->harga_satuan > 0);
        return $valid->isNotEmpty() ? $valid->min('harga_satuan') * (float) $item->volume : 0;
    });
    $pagu = (float) ($package->pagu ?? 0);
    $hemat = $pagu - $totalTermurah;

    // Nomor surat permohonan lengkap (format sama dengan dokumen cetak)
    $nomorSuratLengkap = $surat && filled($surat->nomor_surat)
        ? '000.3.2/' . $surat->nomor_surat . '/SP-PBJ/' . ($package->program?->kode ?? '-') . '/PERKIMPLH-C'
        : null;

    $dokumen = [
        ['label' => 'Spesifikasi Teknis', 'icon' => 'file-text',
         'url' => $ts ? route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'technical-specifications.print', $ts) : null],
        ['label' => 'Referensi Harga', 'icon' => 'tags',
         'url' => $refs->isNotEmpty() ? route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.price-references.print', $package) : null],
        ['label' => 'Surat Permohonan', 'icon' => 'mail',
         'url' => $surat ? route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-request.print', $package) : null],
    ];
@endphp

@if($locked)
    {{-- ================= PERSIAPAN SELESAI ================= --}}
    <div class="flex flex-col items-center text-center py-6">
        <div class="relative w-20 h-20 mb-5">
            <span class="absolute inset-0 rounded-full bg-emerald-400 opacity-20 animate-ping" style="animation-duration: 2.5s;"></span>
            <span class="relative w-20 h-20 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                <i data-lucide="check" class="w-10 h-10 stroke-[3]"></i>
            </span>
        </div>
        <h3 class="text-xl font-extrabold text-slate-800">Persiapan Pengadaan Selesai</h3>
        <p class="text-sm text-slate-500 mt-2 max-w-md leading-relaxed">
            Seluruh tahap persiapan telah dituntaskan dan paket melaju ke tahap
            <strong class="text-slate-700">{{ \App\Models\ProcurementPackage::getWorkflowStatuses()[$procurementPackage->workflow_status] ?? 'Pemilihan Penyedia' }}</strong>.
        </p>
        <p class="text-xs text-slate-400 mt-3 flex items-center gap-1.5">
            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
            Data persiapan terkunci. Hubungi Admin jika perlu membuka kembali.
        </p>

        <a href="{{ route('kabid.procurement-packages.procurement-process.show', $package) }}"
            class="mt-6 inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all hover:-translate-y-0.5">
            Lanjut ke Proses Pengadaan
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>

        {{-- Dokumen hasil --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-8 w-full max-w-2xl">
            @foreach($dokumen as $doc)
                <a href="{{ $doc['url'] ?? '#' }}" @if($doc['url']) target="_blank" @endif
                    class="flex items-center gap-2.5 px-4 py-3 rounded-xl border transition-all
                        {{ $doc['url'] ? 'bg-white border-slate-200 hover:border-emerald-300 hover:shadow-sm' : 'bg-slate-50 border-slate-100 opacity-50 cursor-not-allowed' }}">
                    <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                        <i data-lucide="{{ $doc['icon'] }}" class="w-4 h-4"></i>
                    </span>
                    <span class="text-left min-w-0">
                        <span class="block text-xs font-bold text-slate-700 truncate">{{ $doc['label'] }}</span>
                        <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wide">
                            {{ $doc['url'] ? 'Buka dokumen' : 'Tidak tersedia' }}
                        </span>
                    </span>
                    @if($doc['url'])
                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-300 ml-auto shrink-0"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@else
    {{-- ================= DAFTAR PERIKSA AKHIR ================= --}}
    <div x-data="{ showConfirmSelesai: false }">

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Kolom kiri: checklist --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-blue-500"></i> Daftar Periksa Akhir
                    </h3>
                    <span class="text-[11px] font-bold {{ $allDone ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $doneCount }}/{{ count($checks) }} lengkap
                    </span>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100 overflow-hidden">
                    @foreach($checks as $check)
                        <button type="button" @click="step = {{ $check['step'] }}"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-slate-50 group">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                                {{ $check['done']
                                    ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm shadow-emerald-200'
                                    : 'bg-amber-50 border-2 border-amber-300 text-amber-500' }}">
                                @if($check['done'])
                                    <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                                @else
                                    <i data-lucide="{{ $check['icon'] }}" class="w-3.5 h-3.5"></i>
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold {{ $check['done'] ? 'text-slate-700' : 'text-amber-700' }}">
                                    {{ $check['label'] }}
                                </span>
                                <span class="block text-xs {{ $check['done'] ? 'text-slate-400' : 'text-amber-500 font-semibold' }}">
                                    {{ $check['done'] ? $check['desc'] : 'Belum lengkap — klik untuk melengkapi.' }}
                                </span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors shrink-0"></i>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Kolom kanan: ringkasan + dokumen --}}
            <div class="space-y-5">
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-blue-500"></i> Ringkasan Persiapan
                    </h3>
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                        <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                            <div class="p-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pagu Anggaran</p>
                                <p class="font-bold text-slate-800 mt-0.5">Rp {{ number_format($pagu, 0, ',', '.') }}</p>
                            </div>
                            <div class="p-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Termurah</p>
                                <p class="font-bold mt-0.5 {{ $totalTermurah > $pagu ? 'text-rose-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($totalTermurah, 0, ',', '.') }}
                                </p>
                                @if($totalTermurah > 0)
                                    <p class="text-[10px] font-semibold mt-0.5 {{ $hemat >= 0 ? 'text-emerald-500' : 'text-rose-500' }}">
                                        {{ $hemat >= 0 ? 'potensi hemat' : 'melebihi pagu' }}
                                        Rp {{ number_format(abs($hemat), 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                            <div class="p-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Item Barang/Jasa</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $items->count() }} item</p>
                            </div>
                            <div class="p-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Referensi Harga</p>
                                <p class="font-bold text-slate-800 mt-0.5">{{ $refs->count() }} referensi</p>
                            </div>
                        </div>
                        <div class="p-4 border-b border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rekomendasi Penyedia</p>
                            <p class="font-semibold text-slate-700 mt-0.5 text-sm truncate">{{ $surat->nama_penyedia ?? '-' }}</p>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Surat Permohonan</p>
                            @if($nomorSuratLengkap)
                                <p class="font-bold text-slate-700 mt-0.5 text-xs font-mono tracking-tight break-all">{{ $nomorSuratLengkap }}</p>
                            @else
                                <p class="font-semibold text-slate-400 mt-0.5 text-sm">-</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dokumen hasil persiapan (baris penuh) --}}
        <div class="mt-6">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i data-lucide="files" class="w-4 h-4 text-blue-500"></i> Dokumen Hasil Persiapan
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @foreach($dokumen as $doc)
                    <a href="{{ $doc['url'] ?? '#' }}" @if($doc['url']) target="_blank" @endif
                        class="flex items-center gap-2.5 px-4 py-3 rounded-xl border transition-all
                            {{ $doc['url'] ? 'bg-white border-slate-200 hover:border-emerald-300 hover:shadow-sm' : 'bg-slate-50 border-slate-100 opacity-50 cursor-not-allowed' }}">
                        <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                            <i data-lucide="{{ $doc['icon'] }}" class="w-4 h-4"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold truncate {{ $doc['url'] ? 'text-slate-700' : 'text-slate-400' }}">{{ $doc['label'] }}</span>
                            <span class="block text-[10px] font-bold uppercase tracking-wide {{ $doc['url'] ? 'text-slate-400' : 'text-slate-300' }}">
                                {{ $doc['url'] ? 'Cetak dokumen' : 'Belum ada' }}
                            </span>
                        </span>
                        @if($doc['url'])
                            <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-300 ml-auto shrink-0"></i>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- CTA Selesaikan --}}
        <div class="mt-6 rounded-2xl border p-5 flex flex-col sm:flex-row items-center justify-between gap-4
            {{ $allDone ? 'border-emerald-200 bg-gradient-to-r from-emerald-50/80 to-teal-50/50' : 'border-slate-200 bg-slate-50/60' }}">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                    {{ $allDone ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                    <i data-lucide="{{ $allDone ? 'flag' : 'flag-off' }}" class="w-5 h-5"></i>
                </span>
                <div>
                    <p class="font-bold text-slate-800 text-sm">
                        {{ $allDone ? 'Semua langkah lengkap — persiapan siap diselesaikan.' : 'Persiapan belum bisa diselesaikan.' }}
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($allDone)
                            Setelah diselesaikan, data persiapan <strong>terkunci</strong> dan paket masuk tahap Pemilihan Penyedia.
                        @else
                            Lengkapi {{ count($checks) - $doneCount }} langkah yang masih ditandai kuning pada daftar periksa.
                        @endif
                    </p>
                </div>
            </div>
            <button type="button" @click="showConfirmSelesai = true" @if(!$allDone) disabled @endif
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white rounded-xl shadow-md transition-all shrink-0
                    {{ $allDone
                        ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-200 hover:-translate-y-0.5'
                        : 'bg-slate-300 cursor-not-allowed shadow-none' }}">
                <i data-lucide="flag" class="w-4 h-4"></i>
                Selesaikan Persiapan
            </button>
        </div>

        {{-- Modal konfirmasi selesai --}}
        <div x-show="showConfirmSelesai" style="display: none;"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @keydown.escape.window="showConfirmSelesai = false">

            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmSelesai = false"></div>

            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden text-center"
                x-transition:enter="transition ease-out duration-200 delay-75"
                x-transition:enter-start="opacity-0 scale-90 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <div class="pt-8 px-6">
                    <div class="relative w-16 h-16 mx-auto mb-4">
                        <span class="absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 rotate-6 opacity-20"></span>
                        <span class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                            <i data-lucide="flag" class="w-7 h-7"></i>
                        </span>
                        <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-amber-400 border-2 border-white flex items-center justify-center text-white">
                            <i data-lucide="lock" class="w-3 h-3"></i>
                        </span>
                    </div>

                    <h3 class="text-lg font-extrabold text-slate-800">Selesaikan Persiapan?</h3>
                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                        Paket <strong>{{ Str::limit($package->nama_paket, 40) }}</strong> akan masuk tahap
                        <strong>Pemilihan Penyedia</strong> dan seluruh data persiapan
                        <span class="font-bold text-rose-600">dikunci</span>.
                    </p>
                    <p class="text-xs text-slate-400 mt-2 flex items-center justify-center gap-1.5">
                        <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                        Hanya Admin yang dapat membuka kunci kembali.
                    </p>
                </div>

                <div class="p-5 mt-3 flex items-stretch gap-2">
                    <button type="button" @click="showConfirmSelesai = false"
                        class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap shrink-0">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('kabid.procurement-packages.finish-preparation', $package) }}" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full h-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all whitespace-nowrap">
                            <i data-lucide="flag" class="w-4 h-4 shrink-0"></i>
                            Ya, Selesaikan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
