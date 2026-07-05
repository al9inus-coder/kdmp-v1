@php
    $package = $procurementPackage->package;
    $surat = $procurementPackage->procurementRequest;
    $kodeProgram = $package->program?->kode ?? '-';

    $tanggalSurat = $surat?->tanggal_surat
        ? \Illuminate\Support\Carbon::parse($surat->tanggal_surat)->format('Y-m-d')
        : null;

    // Kandidat penyedia dari hasil survei referensi harga + hitung berapa kali jadi penawar termurah
    $refs = $procurementPackage->priceReferences->filter(fn($r) => filled($r->nama_pelaku_usaha));
    $termurahPerItem = $refs->filter(fn($r) => (float) $r->harga_satuan > 0)
        ->groupBy('nama_barang_jasa')
        ->map(fn($g) => $g->sortBy('harga_satuan')->first()->nama_pelaku_usaha);
    $vendors = $refs->groupBy('nama_pelaku_usaha')->map(fn($g, $nama) => [
        'nama' => $nama,
        'penawaran' => $g->filter(fn($r) => (float) $r->harga_satuan > 0)->count(),
        'termurah' => $termurahPerItem->filter(fn($v) => $v === $nama)->count(),
    ])->sortByDesc('termurah')->values();

    $alasanTemplates = [
        'Harga terendah' => 'Penyedia menawarkan harga terendah berdasarkan hasil survei harga pada katalog elektronik untuk item barang/jasa yang dibutuhkan.',
        'Sesuai spesifikasi' => 'Produk yang ditawarkan penyedia sesuai dengan spesifikasi teknis yang telah ditetapkan dan tersedia pada etalase katalog elektronik.',
        'Pelaku usaha lokal' => 'Penyedia merupakan pelaku usaha lokal yang memiliki rekam jejak baik dalam penyediaan barang/jasa pemerintah.',
    ];
@endphp

<div x-data="kabidSuratPermohonan({
        printUrl: '{{ route('procurement-packages.procurement-request.print', $package) }}?embed=1',
        hasSurat: {{ $surat ? 'true' : 'false' }},
    })">

    {{-- Toolbar: tab --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="inline-flex items-center p-1 bg-slate-100 border border-slate-200 rounded-xl">
            <button type="button" @click="tab = 'editor'"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all"
                :class="tab === 'editor' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                <i data-lucide="pen-line" class="w-4 h-4"></i> Isi Surat
            </button>
            <button type="button" @click="openPreview()" :disabled="!hasSurat"
                :title="hasSurat ? '' : 'Simpan surat terlebih dahulu untuk melihat pratinjau'"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                :class="tab === 'preview' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                <i data-lucide="eye" class="w-4 h-4"></i> Pratinjau Surat
            </button>
        </div>

        <div class="flex items-center gap-2">
            @if($surat)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i data-lucide="mail-check" class="w-3.5 h-3.5"></i> Surat sudah dibuat
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    <i data-lucide="mail-question" class="w-3.5 h-3.5"></i> Belum dibuat
                </span>
            @endif
            @if(!($locked ?? false))
                <button type="submit" form="form-surat-permohonan" x-show="tab === 'editor'"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-200 transition-colors">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    {{ $surat ? 'Simpan Perubahan' : 'Buat Surat' }}
                </button>
            @endif
        </div>
    </div>

    {{-- ====================== TAB: FORM ====================== --}}
    <div x-show="tab === 'editor'">
        <form method="POST" id="form-surat-permohonan" action="{{ route('kabid.procurement-packages.request.update', $package) }}"
              class="space-y-5 {{ ($locked ?? false) ? 'pointer-events-none opacity-70 select-none' : '' }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                {{-- Kolom kiri: administrasi surat --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="mail" class="w-4 h-4 text-blue-500"></i> Administrasi Surat
                    </h3>

                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100">
                        <div class="p-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor Surat</label>
                            <input type="text" name="nomor_surat" x-model="nomor" placeholder="Contoh: 012"
                                value="{{ old('nomor_surat', $surat->nomor_surat ?? '') }}"
                                class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                            {{-- Pratinjau nomor lengkap live --}}
                            <div class="mt-2 flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-50 border border-slate-200">
                                <i data-lucide="file-badge" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                <code class="text-xs font-bold text-slate-600 tracking-tight truncate">
                                    000.3.2/<span class="text-emerald-600" x-text="nomor || '___'"></span>/SP-PBJ/{{ $kodeProgram }}/PERKIMPLH-C
                                </code>
                            </div>
                        </div>
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Surat</label>
                                <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', $tanggalSurat) }}"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Pejabat Pengadaan</label>
                                <input type="text" name="nama_pejabat_pengadaan" placeholder="Nama lengkap"
                                    value="{{ old('nama_pejabat_pengadaan', $surat->nama_pejabat_pengadaan ?? '') }}"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom kanan: penyedia terpilih --}}
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="store" class="w-4 h-4 text-blue-500"></i> Penyedia Terpilih
                    </h3>

                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Penyedia</label>
                            <input type="text" name="nama_penyedia" x-model="penyedia" placeholder="Ketik atau pilih dari hasil survei"
                                class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                        </div>

                        @if($vendors->isNotEmpty())
                            <div>
                                <p class="text-[11px] font-semibold text-slate-400 mb-1.5">Dari hasil survei referensi harga &mdash; klik untuk memilih:</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($vendors as $v)
                                        <button type="button" @click="penyedia = '{{ addslashes($v['nama']) }}'"
                                            class="inline-flex items-center gap-1.5 pl-2.5 pr-2 py-1 rounded-full text-xs font-semibold border transition-all"
                                            :class="penyedia === '{{ addslashes($v['nama']) }}'
                                                ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm shadow-emerald-200'
                                                : 'bg-white text-slate-600 border-slate-200 hover:border-emerald-300 hover:bg-emerald-50'">
                                            {{ $v['nama'] }}
                                            @if($v['termurah'] > 0)
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wide"
                                                    :class="penyedia === '{{ addslashes($v['nama']) }}' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-700'">
                                                    <i data-lucide="trending-down" class="w-2.5 h-2.5"></i>
                                                    {{ $v['termurah'] }}x termurah
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-amber-600 font-semibold flex items-center gap-1.5">
                                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                                Belum ada penyedia dari survei &mdash; isi Referensi Harga dulu agar bisa memilih cepat.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Alasan pemilihan --}}
            <div class="space-y-2">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="message-square-quote" class="w-4 h-4 text-blue-500"></i> Alasan Pemilihan Penyedia
                </h3>
                <div class="flex flex-wrap gap-1.5 mb-1">
                    @foreach($alasanTemplates as $label => $teks)
                        <button type="button" @click="isiAlasan('{{ addslashes($teks) }}')"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 transition-colors">
                            <i data-lucide="wand-2" class="w-3 h-3"></i> {{ $label }}
                        </button>
                    @endforeach
                </div>
                <textarea name="alasan_pemilihan_penyedia" x-ref="alasan" rows="4"
                    placeholder="Tuliskan alasan pemilihan penyedia, atau klik salah satu template di atas..."
                    class="w-full rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm shadow-sm p-4">{{ old('alasan_pemilihan_penyedia', $surat->alasan_pemilihan_penyedia ?? '') }}</textarea>
            </div>

        </form>
    </div>

    {{-- ====================== TAB: PRATINJAU ====================== --}}
    <div x-show="tab === 'preview'" style="display: none;">
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Surat Permohonan siap cetak.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="$refs.previewFrame.contentWindow.print()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak PDF
                    </button>
                    <a href="{{ route('procurement-packages.procurement-request.print', $package) }}" target="_blank"
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
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </span>
                    </span>
                    <p class="text-sm font-semibold text-slate-600">Memuat surat...</p>
                </div>
                <iframe x-ref="previewFrame" @load="previewLoading = false"
                    class="w-full border-0 block" style="height: 900px;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    function kabidSuratPermohonan(config) {
        return {
            tab: 'editor',
            previewLoading: false,
            hasSurat: config.hasSurat,
            nomor: @js(old('nomor_surat', $surat->nomor_surat ?? '')),
            penyedia: @js(old('nama_penyedia', $surat->nama_penyedia ?? '')),

            isiAlasan(teks) {
                const el = this.$refs.alasan;
                if (el.value.trim() !== '' && !confirm('Ganti isi alasan dengan template ini?')) return;
                el.value = teks;
            },

            openPreview() {
                if (!this.hasSurat) return;
                this.tab = 'preview';
                this.previewLoading = true;
                this.$refs.previewFrame.src = config.printUrl + '&t=' + Date.now();
            },
        };
    }
</script>
