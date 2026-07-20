@php
    $ts = $procurementPackage->technicalSpecification;
    $package = $procurementPackage->package;
    $adaItems = $ts && $ts->items->isNotEmpty();
    $adaDraf = $ts && (filled($ts->latar_belakang) || filled($ts->uraian_pekerjaan));

    $tanggalDokumen = $ts?->tanggal
        ? \Illuminate\Support\Carbon::parse($ts->tanggal)->format('Y-m-d')
        : null;
@endphp

@if(!$adaItems)
    {{-- Belum ada barang/jasa: draf AI belum bisa dibuat --}}
    <div class="border-2 border-dashed border-amber-200 rounded-xl p-10 flex flex-col items-center justify-center text-center bg-amber-50/40">
        <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-amber-100 flex items-center justify-center mb-4 text-amber-400">
            <i data-lucide="shopping-basket" class="w-7 h-7"></i>
        </div>
        <h3 class="text-md font-bold text-slate-700 mb-1">Rincian Barang/Jasa Masih Kosong</h3>
        <p class="text-sm text-slate-500 max-w-sm mb-4">
            Draf Spesifikasi Teknis disusun AI berdasarkan rincian barang/jasa.
            Lengkapi dulu daftar barang/jasa pada langkah sebelumnya.
        </p>
        <button type="button" @click="step = 2"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Isi Barang/Jasa
        </button>
    </div>
@else
<div x-data="kabidSpesifikasi({
        printUrl: '{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'technical-specifications.print', $ts) }}?embed=1',
        hasDraf: {{ $adaDraf ? 'true' : 'false' }},
        hadFlash: {{ (session('error') || session('success')) ? 'true' : 'false' }},
        locked: {{ ($locked ?? false) ? 'true' : 'false' }},
    })"
    x-init="$watch('step', v => { if (v === 3) maybeAutoGenerate() })">

    {{-- Toolbar: tab + aksi --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        {{-- Tab switch --}}
        <div class="inline-flex items-center p-1 bg-slate-100 border border-slate-200 rounded-xl">
            <button type="button" @click="tab = 'editor'"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all"
                :class="tab === 'editor' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                <i data-lucide="pen-line" class="w-4 h-4"></i> Editor
            </button>
            <button type="button" @click="openPreview()"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all"
                :class="tab === 'preview' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                <i data-lucide="eye" class="w-4 h-4"></i> Pratinjau Dokumen
            </button>
        </div>

        <div class="flex items-center gap-2">
            {{-- Status auto-save --}}
            <span x-show="savedAt" x-transition.opacity style="display: none;"
                class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                Tersimpan <span x-text="savedAt"></span>
            </span>
            <span x-show="saveError" x-transition.opacity style="display: none;"
                class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                Gagal menyimpan otomatis
            </span>

            @if(!($locked ?? false))
            {{-- Edit Prompt --}}
            <button type="button" @click="showPromptModal = true"
                class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition-colors">
                <i data-lucide="settings-2" class="w-4 h-4 text-slate-400"></i>
                Edit Prompt
            </button>

            {{-- Generate AI --}}
            <button type="button" @click="startGenerate({{ $adaDraf ? 'true' : 'false' }})" :disabled="generating"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg shadow-sm transition-colors disabled:opacity-60 disabled:cursor-wait">
                <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i>
                {{ $adaDraf ? 'Generate Ulang AI' : 'Generate AI' }}
            </button>

            {{-- Simpan (kanan atas, hanya di tab editor) --}}
            <button type="submit" form="form-spesifikasi-teknis" x-show="tab === 'editor'"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-200 transition-colors">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan
            </button>
            @endif
        </div>
    </div>

    {{-- Form tersembunyi untuk submit generate --}}
    <form method="POST" id="form-generate-ai" class="hidden"
          action="{{ route('kabid.procurement-packages.specification.generate', $package) }}">
        @csrf
    </form>

    {{-- ====================== TAB: EDITOR ====================== --}}
    <div x-show="tab === 'editor'">
        <div class="flex items-start gap-2.5 px-3.5 py-2.5 rounded-xl bg-indigo-50/70 border border-indigo-100 mb-5">
            <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500 shrink-0 mt-0.5"></i>
            <p class="text-xs text-slate-600 leading-relaxed">
                Bagian <strong>1&ndash;4</strong> adalah draf otomatis AI dan dapat diubah langsung.
                Bagian lain dokumen terisi otomatis dari data paket. Saat membuka <strong>Pratinjau Dokumen</strong>,
                perubahan Anda <strong>tersimpan otomatis</strong>.
            </p>
        </div>

        @if(!$adaDraf)
            <div class="border-2 border-dashed border-indigo-200 rounded-xl p-10 flex flex-col items-center justify-center text-center bg-indigo-50/30 mb-5">
                <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-indigo-100 flex items-center justify-center mb-4 text-indigo-400">
                    <i data-lucide="bot" class="w-7 h-7"></i>
                </div>
                <h3 class="text-md font-bold text-slate-700 mb-1">Draf Belum Dibuat</h3>
                <p class="text-sm text-slate-500 max-w-sm mb-4">
                    AI siap menyusun draf dari {{ $ts->items->count() }} item barang/jasa.
                    Anda juga bisa menulisnya manual di bawah.
                </p>
                <button type="button" @click="startGenerate(false)"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 rounded-xl shadow-md shadow-indigo-200 transition-all hover:-translate-y-0.5">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Buat Draf dengan AI
                </button>
            </div>
        @endif

        <form method="POST" id="form-spesifikasi-teknis"
              action="{{ route('kabid.procurement-packages.specification.update', $package) }}"
              class="space-y-6 {{ ($locked ?? false) ? 'pointer-events-none opacity-70 select-none' : '' }}">
            @csrf
            @method('PUT')

            {{-- 1. Latar Belakang --}}
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold">1</span>
                    Latar Belakang
                </label>
                <textarea name="latar_belakang" rows="6"
                    placeholder="Latar belakang pengadaan..."
                    class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm shadow-sm p-4">{{ old('latar_belakang', $ts->latar_belakang ?? '') }}</textarea>
            </div>

            {{-- 2. Maksud dan Tujuan --}}
            <div class="space-y-3">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold">2</span>
                    Maksud dan Tujuan
                </label>
                <div class="pl-8 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">a. Maksud</label>
                        <textarea name="maksud[Maksud]" rows="3"
                            class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm shadow-sm p-3">{{ old('maksud.Maksud', $ts->maksud['Maksud'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">b. Tujuan</label>
                        <textarea name="maksud[Tujuan]" rows="3"
                            class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm shadow-sm p-3">{{ old('maksud.Tujuan', $ts->maksud['Tujuan'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 3. Target dan Sasaran --}}
            <div class="space-y-3">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold">3</span>
                    Target dan Sasaran
                </label>
                <div class="pl-8 space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">a. Target</label>
                        <textarea name="target_sasaran[Target]" rows="3"
                            class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm shadow-sm p-3">{{ old('target_sasaran.Target', $ts->target_sasaran['Target'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">b. Sasaran</label>
                        <textarea name="target_sasaran[Sasaran]" rows="3"
                            class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm shadow-sm p-3">{{ old('target_sasaran.Sasaran', $ts->target_sasaran['Sasaran'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- 4. Uraian Pekerjaan --}}
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-600 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-[11px] font-bold">4</span>
                    Uraian Pekerjaan
                </label>
                <textarea name="uraian_pekerjaan" rows="6"
                    placeholder="Uraian pekerjaan..."
                    class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm shadow-sm p-4">{{ old('uraian_pekerjaan', $ts->uraian_pekerjaan ?? '') }}</textarea>
            </div>

            {{-- Blok tanda tangan: tanggal dokumen di posisi seperti dokumen aslinya --}}
            <div class="pt-5 border-t border-slate-100 flex justify-end">
                <div class="w-full sm:w-80 border border-slate-200 rounded-xl bg-slate-50/60 px-5 py-4 text-center relative">
                    <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-2 py-0.5 bg-white border border-slate-200 rounded-full text-[9px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">
                        <i data-lucide="pen-tool" class="w-2.5 h-2.5 inline-block -mt-0.5"></i>
                        Blok Tanda Tangan
                    </span>

                    <div class="flex items-center justify-center gap-1.5 text-sm text-slate-700 mt-1">
                        <span>Bengkayang,</span>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $tanggalDokumen) }}"
                            class="rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm py-1 px-2 w-auto bg-white">
                    </div>
                    <p class="text-xs text-slate-500 mt-1.5 leading-snug">
                        Pejabat Pembuat Komitmen<br>{{ \App\Models\Skpd::first()?->nama ?? '' }}
                    </p>

                    <div class="h-10 flex items-end justify-center">
                        <div class="w-40 border-b border-dashed border-slate-300"></div>
                    </div>

                    <p class="text-sm font-bold text-slate-800 underline underline-offset-2 mt-1.5">
                        {{ $procurementPackage->nama_ppk ?? '—' }}
                    </p>
                    <p class="text-[11px] text-slate-500">{{ $procurementPackage->pangkat_gol_ppk ?? '' }}</p>
                    <p class="text-[11px] text-slate-500">NIP. {{ $procurementPackage->nip_ppk ?? '-' }}</p>
                </div>
            </div>
        </form>
    </div>

    {{-- ====================== TAB: PRATINJAU ====================== --}}
    <div x-show="tab === 'preview'" style="display: none;">
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Dokumen resmi lengkap &mdash; perubahan editor sudah disimpan otomatis.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="$refs.previewFrame.contentWindow.print()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak PDF
                    </button>
                    <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'technical-specifications.print', $ts) }}" target="_blank"
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
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </span>
                    </span>
                    <p class="text-sm font-semibold text-slate-600">Menyimpan &amp; memuat dokumen...</p>
                </div>
                <iframe x-ref="previewFrame" @load="previewLoading = false"
                    class="w-full border-0 block" style="height: 900px;"></iframe>
            </div>
        </div>
    </div>

    {{-- ====================== MODAL KONFIRMASI GENERATE ULANG ====================== --}}
    <div x-show="showConfirmModal" style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @keydown.escape.window="showConfirmModal = false">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmModal = false"></div>

        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden text-center"
            x-transition:enter="transition ease-out duration-200 delay-75"
            x-transition:enter-start="opacity-0 scale-90 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            <div class="pt-8 px-6">
                {{-- Ikon --}}
                <div class="relative w-16 h-16 mx-auto mb-4">
                    <span class="absolute inset-0 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 rotate-6 opacity-20"></span>
                    <span class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                        <i data-lucide="sparkles" class="w-7 h-7"></i>
                    </span>
                    <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-amber-400 border-2 border-white flex items-center justify-center text-white">
                        <i data-lucide="refresh-cw" class="w-3 h-3"></i>
                    </span>
                </div>

                <h3 class="text-lg font-extrabold text-slate-800">Generate Ulang dengan AI?</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Draf <strong>Latar Belakang, Maksud &amp; Tujuan, Target &amp; Sasaran,</strong> dan
                    <strong>Uraian Pekerjaan</strong> yang ada sekarang akan <span class="font-bold text-rose-600">ditimpa</span> hasil AI yang baru.
                </p>
                <p class="text-xs text-slate-400 mt-2 flex items-center justify-center gap-1.5">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Rincian barang/jasa &amp; data kontrak tidak terpengaruh.
                </p>
            </div>

            <div class="p-5 mt-3 flex items-stretch gap-2">
                <button type="button" @click="showConfirmModal = false"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap shrink-0">
                    Batal
                </button>
                <button type="button" @click="doGenerate()"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 rounded-xl shadow-md shadow-indigo-200 transition-all whitespace-nowrap">
                    <i data-lucide="sparkles" class="w-4 h-4 shrink-0"></i>
                    Ya, Generate Ulang
                </button>
            </div>
        </div>
    </div>

    {{-- ====================== MODAL EDIT PROMPT ====================== --}}
    <div x-show="showPromptModal" style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        @keydown.escape.window="showPromptModal = false">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showPromptModal = false"></div>

        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-200 delay-75"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <form method="POST" action="{{ route('kabid.procurement-packages.specification.prompt', $package) }}">
                @csrf

                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500">
                            <i data-lucide="settings-2" class="w-4 h-4"></i>
                        </span>
                        Konfigurasi Prompt AI
                    </h3>
                    <button type="button" @click="showPromptModal = false"
                        class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-5 space-y-3">
                    <div class="flex items-start gap-2.5 px-3.5 py-2.5 rounded-xl bg-blue-50/60 border border-blue-100">
                        <i data-lucide="info" class="w-4 h-4 text-blue-500 shrink-0 mt-0.5"></i>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Sesuaikan gaya bahasa dan instruksi AI di sini. <strong>Jangan mengubah variabel dalam kurung kurawal</strong>
                            seperti <code class="px-1 py-0.5 bg-white border border-blue-200 rounded text-[10px] font-bold">{NAMA_PAKET}</code>,
                            <code class="px-1 py-0.5 bg-white border border-blue-200 rounded text-[10px] font-bold">{SKPD}</code>,
                            <code class="px-1 py-0.5 bg-white border border-blue-200 rounded text-[10px] font-bold">{ITEMS}</code>
                            &mdash; nilainya diisi otomatis dari data paket. Prompt ini berlaku untuk semua paket.
                        </p>
                    </div>
                    <textarea name="prompt" x-ref="promptArea" rows="14" required
                        class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono shadow-sm p-4 leading-relaxed">{{ $aiPrompt->prompt ?? config('ai_prompts.technical_specification') }}</textarea>
                </div>

                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-2">
                    <button type="button" @click="restoreDefaultPrompt()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors">
                        <i data-lucide="undo-2" class="w-4 h-4"></i> Gunakan Default
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showPromptModal = false"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm shadow-indigo-200 transition-colors">
                            <i data-lucide="save" class="w-4 h-4"></i> Simpan Prompt
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ====================== MODAL AI GENERATE ====================== --}}
    <div x-show="showAiModal" style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm"></div>

        {{-- Kartu modal --}}
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-300 delay-75"
            x-transition:enter-start="opacity-0 scale-90 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            {{-- Panggung ilustrasi AI --}}
            <div class="relative h-44 bg-gradient-to-br from-indigo-600 via-violet-600 to-indigo-800 overflow-hidden">
                {{-- Bintang latar --}}
                <span class="absolute w-1 h-1 bg-white/60 rounded-full top-6 left-10 kdmp-twinkle"></span>
                <span class="absolute w-1.5 h-1.5 bg-white/40 rounded-full top-16 right-12 kdmp-twinkle" style="animation-delay: .6s;"></span>
                <span class="absolute w-1 h-1 bg-white/50 rounded-full bottom-8 left-20 kdmp-twinkle" style="animation-delay: 1.1s;"></span>
                <span class="absolute w-1 h-1 bg-white/60 rounded-full top-10 right-28 kdmp-twinkle" style="animation-delay: 1.6s;"></span>

                {{-- Orbit + robot --}}
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative w-28 h-28 flex items-center justify-center">
                        {{-- Ring pulse --}}
                        <span class="absolute w-24 h-24 rounded-full border-2 border-white/25 animate-ping" style="animation-duration: 2.2s;"></span>
                        <span class="absolute w-full h-full rounded-full border border-white/20 animate-ping" style="animation-duration: 3s; animation-delay: .5s;"></span>

                        {{-- Partikel mengorbit --}}
                        <span class="absolute inset-0 kdmp-orbit">
                            <span class="absolute -top-1 left-1/2 -ml-1.5 w-3 h-3 rounded-full bg-amber-300 shadow-lg shadow-amber-300/50"></span>
                        </span>
                        <span class="absolute inset-2 kdmp-orbit-reverse">
                            <span class="absolute top-1/2 -right-1 -mt-1 w-2 h-2 rounded-full bg-emerald-300 shadow-lg shadow-emerald-300/50"></span>
                        </span>

                        {{-- Robot --}}
                        <span class="relative w-16 h-16 rounded-2xl bg-white/95 shadow-xl flex items-center justify-center text-indigo-600 kdmp-float">
                            <i data-lucide="bot" class="w-9 h-9"></i>
                        </span>
                    </div>
                </div>

                {{-- Dokumen kecil "mengetik" --}}
                <div class="absolute bottom-3 right-4 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/15 border border-white/20 backdrop-blur-sm">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-white/90"></i>
                    <span class="flex gap-0.5">
                        <span class="w-1 h-1 rounded-full bg-white/90 animate-bounce"></span>
                        <span class="w-1 h-1 rounded-full bg-white/90 animate-bounce" style="animation-delay: .15s;"></span>
                        <span class="w-1 h-1 rounded-full bg-white/90 animate-bounce" style="animation-delay: .3s;"></span>
                    </span>
                </div>
            </div>

            {{-- Isi modal --}}
            <div class="p-6">
                <h3 class="text-lg font-extrabold text-slate-800 text-center">AI Sedang Menyusun Dokumen</h3>
                <p class="text-xs text-slate-500 text-center mt-1 mb-5">
                    Spesifikasi Teknis &mdash; {{ Str::limit($package->nama_paket, 45) }}
                </p>

                {{-- Checklist langkah --}}
                <div class="space-y-2.5 mb-5">
                    <template x-for="(s, i) in aiSteps" :key="i">
                        <div class="flex items-center gap-3 transition-all duration-300"
                            :class="i < currentStep ? 'text-emerald-600' : (i === currentStep ? 'text-indigo-700' : 'text-slate-300')">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center border-2 shrink-0 transition-all duration-300 text-[11px] font-bold"
                                :class="i < currentStep
                                    ? 'bg-emerald-500 border-emerald-500 text-white'
                                    : (i === currentStep ? 'border-indigo-500 text-indigo-600 kdmp-spin-border' : 'border-slate-200')">
                                <span x-show="i < currentStep">&#10003;</span>
                                <span x-show="i >= currentStep" x-text="i + 1"></span>
                            </span>
                            <span class="text-sm transition-all duration-300"
                                :class="i === currentStep ? 'font-bold' : 'font-medium'" x-text="s"></span>
                        </div>
                    </template>
                </div>

                {{-- Progress bar --}}
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">Progres</span>
                    <span class="text-xs font-extrabold text-indigo-600" x-text="progress + '%'"></span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-violet-500 to-indigo-500 kdmp-shimmer-bar transition-all duration-700"
                        :style="`width: ${progress}%`"></div>
                </div>

                <p class="text-[11px] text-slate-400 text-center mt-4 flex items-center justify-center gap-1.5">
                    <i data-lucide="lock" class="w-3 h-3"></i>
                    Jangan tutup halaman ini &mdash; biasanya selesai dalam 15&ndash;60 detik.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes kdmp-orbit-kf { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .kdmp-orbit { animation: kdmp-orbit-kf 3.5s linear infinite; }
    .kdmp-orbit-reverse { animation: kdmp-orbit-kf 5s linear infinite reverse; }

    @keyframes kdmp-float-kf { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
    .kdmp-float { animation: kdmp-float-kf 2.6s ease-in-out infinite; }

    @keyframes kdmp-twinkle-kf { 0%, 100% { opacity: .2; } 50% { opacity: 1; } }
    .kdmp-twinkle { animation: kdmp-twinkle-kf 2s ease-in-out infinite; }

    @keyframes kdmp-spin-border-kf { to { transform: rotate(360deg); } }
    .kdmp-spin-border { border-top-color: transparent !important; animation: kdmp-spin-border-kf 1s linear infinite; }

    @keyframes kdmp-shimmer-bar-kf { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    .kdmp-shimmer-bar { background-size: 200% 100%; animation: kdmp-shimmer-bar-kf 2.5s linear infinite; }
</style>

<script>
    function kabidSpesifikasi(config) {
        return {
            tab: 'editor',
            generating: false,
            showAiModal: false,
            showPromptModal: false,
            showConfirmModal: false,
            autoTried: false,
            previewLoading: false,
            savedAt: null,
            saveError: false,
            defaultPrompt: @js(config('ai_prompts.technical_specification')),

            restoreDefaultPrompt() {
                if (confirm('Kembalikan prompt ke versi default? Prompt saat ini di kotak teks akan diganti.')) {
                    this.$refs.promptArea.value = this.defaultPrompt;
                }
            },

            aiSteps: [
                'Membaca data paket & rincian barang/jasa',
                'Menyusun latar belakang',
                'Menyusun maksud dan tujuan',
                'Menyusun target dan sasaran',
                'Menyusun uraian pekerjaan',
                'Finalisasi draf dokumen',
            ],
            currentStep: 0,
            progress: 0,

            // Auto-generate saat masuk dari Barang/Jasa dan draf belum ada.
            // Tidak dipicu ulang jika baru saja ada flash (mis. error AI sebelumnya).
            maybeAutoGenerate() {
                if (config.locked || config.hasDraf || config.hadFlash || this.autoTried || this.generating) return;
                this.autoTried = true;
                this.startGenerate(false);
            },

            startGenerate(needConfirm) {
                if (this.generating) return;
                if (needConfirm) {
                    this.showConfirmModal = true;
                    return;
                }
                this.doGenerate();
            },

            doGenerate() {
                if (this.generating) return;
                this.showConfirmModal = false;
                this.generating = true;
                this.showAiModal = true;
                this.currentStep = 0;
                this.progress = 0;
                this.runChecklist();

                document.getElementById('form-generate-ai').submit();
            },

            runChecklist() {
                const total = this.aiSteps.length;
                const timer = setInterval(() => {
                    if (this.currentStep < total - 1) {
                        this.currentStep++;
                        this.progress = Math.floor(((this.currentStep + 1) / total) * 90);
                    } else {
                        // Tahan di langkah terakhir; progres merayap sampai server merespons
                        this.progress = Math.min(this.progress + 1, 95);
                    }
                }, 1600);
            },

            async openPreview() {
                this.tab = 'preview';
                this.previewLoading = true;
                this.saveError = false;

                // Auto-save isi editor sebelum menampilkan dokumen
                try {
                    const form = document.getElementById('form-spesifikasi-teknis');
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    });
                    if (!response.ok) throw new Error('save failed');
                    this.savedAt = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                } catch (e) {
                    this.saveError = true;
                }

                this.$refs.previewFrame.src = config.printUrl + '&t=' + Date.now();
            },
        };
    }
</script>
@endif
