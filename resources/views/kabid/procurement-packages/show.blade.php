@component('layouts.kdmp')
@section('title', 'Tinjau Persiapan Pengadaan')

<div class="space-y-6" x-data="{ 
    step: {{ (int) session('panel', 1) }},
    getFormId() {
        const forms = {
            1: 'form-detail-kontrak',
            2: 'form-barang-jasa',
            3: 'form-spesifikasi-teknis',
            4: 'form-referensi-harga',
            5: 'form-surat-permohonan'
        };
        return forms[this.step] || null;
    },
    saveAndNext() {
        let formId = this.getFormId();
        let isLocked = {{ $locked ?? 'false' ? 'true' : 'false' }};
        if (formId && !isLocked) {
            let form = document.getElementById(formId);
            if (form) {
                // If form has no next_panel input, add it
                if (!form.querySelector('input[name=\'next_panel\']')) {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'next_panel';
                    input.value = this.step + 1;
                    form.appendChild(input);
                } else {
                    form.querySelector('input[name=\'next_panel\']').value = this.step + 1;
                }
                
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
                return;
            }
        }
        if (this.step < 6) this.step++;
    }
}">
    <x-ui.toast />

    {{-- Identitas Paket --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
            <i data-lucide="hash" class="w-3.5 h-3.5 text-blue-500"></i>
            {{ $procurementPackage->package->id_rup ?? '-' }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
            <i data-lucide="package" class="w-3.5 h-3.5 text-blue-500"></i>
            {{ $procurementPackage->package->nama_paket }}
        </span>
    </div>

    {{-- Progress Workflow Pengadaan --}}
    <x-kabid.workflow-progress :procurement-package="$procurementPackage" />

    {{-- Konten (kiri) + Alur Persiapan (kanan) --}}
    <div class="flex flex-col-reverse lg:flex-row gap-6 items-start">

        {{-- Kolom kiri: panel konten per langkah --}}
        <div class="flex-1 w-full min-w-0">
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden flex flex-col min-h-[480px]">
                @php
                    // Persiapan terkunci setelah diselesaikan (workflow bukan draft).
                    // Kabid tidak bisa membuka kembali; tombol buka kunci nanti di role Admin.
                    $locked = $procurementPackage->workflow_status !== \App\Models\ProcurementPackage::WORKFLOW_DRAFT;

                    // Panel dengan 'view' terisi memakai partial; sisanya placeholder (didesain bertahap).
                    $panels = [
                        1 => ['title' => 'Informasi Kontrak',  'icon' => 'file-signature', 'desc' => 'Data PPK dan detail kontrak paket ini.', 'view' => 'kabid.procurement-packages.panels.informasi-kontrak', 'save_form' => 'form-detail-kontrak', 'save_label' => 'Simpan', 'lock_wrap' => true],
                        2 => ['title' => 'Barang / Jasa',      'icon' => 'shopping-basket', 'desc' => 'Kelola rincian item barang/jasa yang diadakan.', 'view' => 'kabid.procurement-packages.panels.barang-jasa', 'save_form' => 'form-barang-jasa', 'save_label' => 'Simpan Barang/Jasa', 'lock_wrap' => true],
                        3 => ['title' => 'Spesifikasi Teknis', 'icon' => 'file-text',       'desc' => 'Susun dan periksa dokumen spesifikasi teknis (draf AI dapat diedit).', 'view' => 'kabid.procurement-packages.panels.spesifikasi-teknis'],
                        4 => ['title' => 'Referensi Harga',    'icon' => 'tags',            'desc' => 'Catat hasil survei harga per item dari katalog elektronik.', 'view' => 'kabid.procurement-packages.panels.referensi-harga'],
                        5 => ['title' => 'Surat Permohonan',   'icon' => 'mail',            'desc' => 'Susun surat permohonan pengadaan kepada pejabat pengadaan.', 'view' => 'kabid.procurement-packages.panels.surat-permohonan'],
                        6 => ['title' => 'Selesaikan',         'icon' => 'flag',            'desc' => 'Periksa kelengkapan lalu tutup tahap persiapan.', 'view' => 'kabid.procurement-packages.panels.selesaikan'],
                    ];
                @endphp

                {{-- Banner kunci --}}
                @if($locked)
                    <div class="px-6 py-3 bg-amber-50/80 border-b border-amber-100 flex items-center gap-2.5">
                        <i data-lucide="lock" class="w-4 h-4 text-amber-500 shrink-0"></i>
                        <p class="text-xs text-amber-800 font-semibold">
                            Persiapan sudah diselesaikan — seluruh data terkunci (hanya bisa dilihat).
                            Hubungi Admin untuk membuka kembali.
                        </p>
                    </div>
                @endif

                <div class="flex-1">
                    @foreach($panels as $no => $panel)
                        <div x-show="step === {{ $no }}" @if($no > 1) style="display: none;" @endif>
                            <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                        <i data-lucide="{{ $panel['icon'] }}" class="w-5 h-5 text-slate-400"></i>
                                        {{ $panel['title'] }}
                                    </h2>
                                    <p class="text-sm text-slate-500 mt-1">{{ $panel['desc'] }}</p>
                                </div>
                                @if(isset($panel['save_form']) && !$locked)
                                    <button type="submit" form="{{ $panel['save_form'] }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-black rounded-lg shadow-sm shadow-slate-300 transition-colors shrink-0">
                                        <i data-lucide="save" class="w-4 h-4"></i>
                                        {{ $panel['save_label'] }}
                                    </button>
                                @endif
                            </div>
                            <div class="p-6">
                                @if(isset($panel['view']))
                                    @if($locked && ($panel['lock_wrap'] ?? false))
                                        <div class="pointer-events-none opacity-70 select-none" aria-disabled="true">
                                            @include($panel['view'])
                                        </div>
                                    @else
                                        @include($panel['view'])
                                    @endif
                                @else
                                    {{-- PLACEHOLDER — konten panel ini akan didesain bertahap --}}
                                    <div class="border-2 border-dashed border-slate-200 rounded-xl p-12 flex flex-col items-center justify-center text-center bg-slate-50/50">
                                        <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mb-4 text-slate-300">
                                            <i data-lucide="{{ $panel['icon'] }}" class="w-7 h-7"></i>
                                        </div>
                                        <h3 class="text-md font-bold text-slate-700 mb-1">{{ $panel['title'] }}</h3>
                                        <p class="text-sm text-slate-500 max-w-sm">Bagian ini sedang dalam tahap desain.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Footer navigasi antar panel --}}
                <div class="border-t border-slate-200 p-4 bg-slate-50/50 flex justify-between items-center mt-auto">
                    <button type="button" @click="step > 1 ? step-- : null" :disabled="step === 1"
                        class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i> Sebelumnya
                    </button>
                    <button type="button" @click="saveAndNext()" :disabled="step === 6"
                        class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Selanjutnya <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Kolom kanan: Alur Persiapan (tanpa background, sticky) --}}
        <aside class="w-full lg:w-60 shrink-0 lg:sticky lg:top-20">
            <x-kabid.preparation-nav :procurement-package="$procurementPackage" />
        </aside>
    </div>
</div>

<script>
    document.addEventListener('alpine:initialized', () => {
        Alpine.effect(() => {
            setTimeout(() => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }, 50);
        });
    });
</script>
@endcomponent
