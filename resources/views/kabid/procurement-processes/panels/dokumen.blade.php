@php
    $package = $procurementPackage->package;

    $syarat = [
        ['label' => 'Nomor & tanggal surat pesanan terisi', 'done' => filled($process->nomor_surat_pesanan) && filled($process->tanggal_surat_pesanan), 'step' => 1],
        ['label' => 'Nilai kontrak terisi',                 'done' => (float) $process->nilai_kontrak > 0, 'step' => 1],
        ['label' => 'Data penyedia lengkap',                'done' => filled($process->nama_penyedia) && filled($process->alamat_penyedia) && filled($process->npwp_penyedia), 'step' => 2],
    ];
    $siapCetak = collect($syarat)->every(fn($s) => $s['done']);
@endphp

@if(!$siapCetak)
    {{-- Data belum lengkap --}}
    <div class="border-2 border-dashed border-amber-200 rounded-xl p-10 bg-amber-50/40">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-amber-100 flex items-center justify-center mb-4 text-amber-400">
                <i data-lucide="file-x" class="w-7 h-7"></i>
            </div>
            <h3 class="text-md font-bold text-slate-700 mb-1">Dokumen Belum Bisa Dibuat</h3>
            <p class="text-sm text-slate-500 max-w-sm">
                SSUK &amp; SSKK dirender otomatis dari data surat pesanan dan penyedia. Lengkapi dulu:
            </p>
        </div>
        <div class="max-w-sm mx-auto space-y-2">
            @foreach($syarat as $s)
                <button type="button" @click="step = {{ $s['step'] }}"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl border text-left text-sm font-semibold transition-colors
                        {{ $s['done'] ? 'bg-white border-emerald-200 text-emerald-700' : 'bg-white border-amber-200 text-amber-700 hover:bg-amber-50' }}">
                    <i data-lucide="{{ $s['done'] ? 'check-circle-2' : 'circle-alert' }}" class="w-4 h-4 shrink-0"></i>
                    {{ $s['label'] }}
                    @unless($s['done'])
                        <i data-lucide="chevron-right" class="w-4 h-4 ml-auto shrink-0"></i>
                    @endunless
                </button>
            @endforeach
        </div>
    </div>
@else
<div x-data="{ previewLoading: true }"
     x-init="$refs.docFrame.src = '{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-process.print-document', $package) }}?embed=1&t=' + Date.now()">
    <div class="border border-slate-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-slate-500 flex items-center gap-1.5">
                <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                SSUK &amp; SSKK dirender otomatis dari data surat pesanan &amp; penyedia yang tersimpan.
            </p>
            <div class="flex items-center gap-2">
                <button type="button" @click="$refs.docFrame.contentWindow.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak PDF
                </button>
                <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-process.print-document', $package) }}" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-sm transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Tab Baru
                </a>
            </div>
        </div>
        <div class="relative bg-slate-200" style="min-height: 900px;">
            <div x-show="previewLoading" x-transition.opacity
                class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-100/80 backdrop-blur-sm">
                <span class="relative flex w-10 h-10 mb-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-60"></span>
                    <span class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white shadow text-emerald-600">
                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                    </span>
                </span>
                <p class="text-sm font-semibold text-slate-600">Memuat dokumen SSUK &amp; SSKK...</p>
            </div>
            <iframe x-ref="docFrame" @load="previewLoading = false"
                class="w-full border-0 block bg-white" style="height: 900px;"></iframe>
        </div>
    </div>
</div>
@endif
