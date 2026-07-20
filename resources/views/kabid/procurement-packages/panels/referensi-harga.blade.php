@php
    $package = $procurementPackage->package;
    $items = $procurementPackage->technicalSpecification?->items ?? collect();
    $refs = $procurementPackage->priceReferences;
    $grouped = $refs->groupBy('nama_barang_jasa');

    // Estimasi termurah: harga terendah per item, hanya dari referensi dengan harga > 0.
    // Harga Rp 0 berarti penyedia tidak memiliki produk — bukan penawaran.
    $totalTermurah = $items->sum(function ($item) use ($grouped) {
        $valid = $grouped->get($item->nama_barang_jasa, collect())->filter(fn($r) => (float) $r->harga_satuan > 0);
        return $valid->isNotEmpty() ? $valid->min('harga_satuan') * (float) $item->volume : 0;
    });
    $itemsTersurvei = $items->filter(fn($i) => $grouped->has($i->nama_barang_jasa))->count();
@endphp

@if($items->isEmpty())
    <div class="border-2 border-dashed border-amber-200 rounded-xl p-10 flex flex-col items-center justify-center text-center bg-amber-50/40">
        <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-amber-100 flex items-center justify-center mb-4 text-amber-400">
            <i data-lucide="shopping-basket" class="w-7 h-7"></i>
        </div>
        <h3 class="text-md font-bold text-slate-700 mb-1">Rincian Barang/Jasa Masih Kosong</h3>
        <p class="text-sm text-slate-500 max-w-sm mb-4">
            Referensi harga dicatat per item barang/jasa. Lengkapi dulu daftar barang/jasa pada langkah kedua.
        </p>
        <button type="button" @click="step = 2"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Isi Barang/Jasa
        </button>
    </div>
@else
<div x-data="kabidRefHarga({
        storeUrl: '{{ route('kabid.procurement-packages.price-references.store', $package) }}',
        updateBase: '{{ url('kabid/procurement-packages/' . $package->getRouteKey() . '/price-references') }}',
        printUrl: '{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.price-references.print', $package) }}?embed=1',
        fetchUrl: '{{ route('kabid.procurement-packages.price-references.fetch', $package) }}',
        csrf: '{{ csrf_token() }}',
    })">

    {{-- Toolbar: tab --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div class="inline-flex items-center p-1 bg-slate-100 border border-slate-200 rounded-xl">
            <button type="button" @click="tab = 'editor'"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all"
                :class="tab === 'editor' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                <i data-lucide="pen-line" class="w-4 h-4"></i> Kelola Referensi
            </button>
            <button type="button" @click="openPreview()"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-sm font-semibold rounded-lg transition-all"
                :class="tab === 'preview' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                <i data-lucide="eye" class="w-4 h-4"></i> Pratinjau Dokumen
            </button>
        </div>

        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border
            {{ $itemsTersurvei === $items->count() ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
            <i data-lucide="{{ $itemsTersurvei === $items->count() ? 'check-circle-2' : 'search' }}" class="w-3.5 h-3.5"></i>
            {{ $itemsTersurvei }}/{{ $items->count() }} item tersurvei
        </span>
    </div>

    {{-- ====================== TAB: KELOLA ====================== --}}
    <div x-show="tab === 'editor'" class="space-y-5">

        @foreach($items as $item)
            @php
                // Urutkan: yang punya harga dulu (termurah di atas), harga 0 (tidak tersedia) di bawah
                $itemRefs = $grouped->get($item->nama_barang_jasa, collect())
                    ->sortBy([fn($a, $b) => ((float) $a->harga_satuan > 0 ? 0 : 1) <=> ((float) $b->harga_satuan > 0 ? 0 : 1)])
                    ->sortBy(fn($r) => (float) $r->harga_satuan > 0 ? (float) $r->harga_satuan : PHP_FLOAT_MAX)
                    ->values();
                $refsValid = $itemRefs->filter(fn($r) => (float) $r->harga_satuan > 0);
                $hargaTermurah = $refsValid->isNotEmpty() ? $refsValid->min('harga_satuan') : null;
            @endphp

            <div class="border border-slate-200 rounded-xl overflow-hidden">
                {{-- Header item --}}
                <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 shrink-0">
                            <i data-lucide="package" class="w-4 h-4"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 text-sm truncate">{{ $item->nama_barang_jasa }}</p>
                            <p class="text-[11px] text-slate-400 font-semibold">
                                {{ rtrim(rtrim(number_format((float) $item->volume, 2, ',', '.'), '0'), ',') }} {{ $item->satuan }}
                                &bull; {{ $itemRefs->count() }} referensi
                            </p>
                        </div>
                    </div>
                    @if(!($locked ?? false))
                        <button type="button"
                            @click="openCreate({{ $item->id }}, '{{ addslashes($item->nama_barang_jasa) }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition-colors shrink-0">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Referensi
                        </button>
                    @endif
                </div>

                {{-- Daftar referensi --}}
                @if($itemRefs->isEmpty())
                    <div class="px-4 py-6 text-center">
                        <p class="text-xs font-semibold text-amber-600 flex items-center justify-center gap-1.5">
                            <i data-lucide="search" class="w-3.5 h-3.5"></i>
                            Belum ada referensi harga untuk item ini.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="text-[11px] text-slate-400 bg-white border-b border-slate-100 uppercase font-semibold">
                                <tr>
                                    <th class="px-4 py-2.5">Produk Etalase</th>
                                    <th class="px-4 py-2.5">Pelaku Usaha</th>
                                    <th class="px-4 py-2.5 text-right">Harga Satuan</th>
                                    <th class="px-4 py-2.5 text-right">Jumlah</th>
                                    <th class="px-4 py-2.5 text-center w-24">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($itemRefs as $ref)
                                    @php
                                        $tidakTersedia = (float) $ref->harga_satuan <= 0;
                                        $isMurah = !$tidakTersedia && $refsValid->count() > 1 && (float) $ref->harga_satuan === (float) $hargaTermurah;
                                        $editPayload = \Illuminate\Support\Js::from([
                                            'id' => $ref->id,
                                            'item_id' => $item->id,
                                            'etalase' => $ref->nama_produk_etalase,
                                            'pelaku' => $ref->nama_pelaku_usaha,
                                            'harga' => (float) $ref->harga_satuan,
                                            'link' => $ref->link_produk,
                                        ]);
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 {{ $isMurah ? 'bg-emerald-50/40' : '' }} {{ $tidakTersedia ? 'opacity-60' : '' }}">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-slate-700">{{ $ref->nama_produk_etalase ?? '-' }}</span>
                                                @if($ref->link_produk)
                                                    <a href="{{ $ref->link_produk }}" target="_blank" rel="noopener" title="Buka produk"
                                                       class="text-slate-300 hover:text-indigo-500 transition-colors">
                                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $ref->nama_pelaku_usaha ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            @if($tidakTersedia)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wide">
                                                    <i data-lucide="package-x" class="w-2.5 h-2.5"></i> Tidak Tersedia
                                                </span>
                                            @else
                                                <span class="font-semibold text-slate-800">Rp {{ number_format((float) $ref->harga_satuan, 0, ',', '.') }}</span>
                                                @if($isMurah)
                                                    <span class="ml-1.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">
                                                        <i data-lucide="trending-down" class="w-2.5 h-2.5"></i> Termurah
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium text-slate-600">
                                            {{ $tidakTersedia ? '-' : 'Rp ' . number_format((float) $ref->jumlah_harga, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(!($locked ?? false))
                                                <div class="flex items-center justify-center gap-1">
                                                    <button type="button"
                                                        @click="openEdit({{ $editPayload }})"
                                                        class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors" title="Ubah">
                                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('kabid.procurement-packages.price-references.destroy', [$package, $ref]) }}"
                                                          onsubmit="return confirm('Hapus referensi harga ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors" title="Hapus">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <div class="flex items-center justify-center">
                                                    <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-300"></i>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Ringkasan estimasi termurah --}}
        <div class="border border-slate-200 rounded-xl bg-slate-50/70 px-4 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <p class="text-xs text-slate-500 leading-snug max-w-md">
                <i data-lucide="calculator" class="w-3.5 h-3.5 inline-block -mt-0.5 text-slate-400"></i>
                <strong>Estimasi termurah</strong> dihitung dari harga satuan terendah tiap item yang sudah tersurvei ({{ $itemsTersurvei }}/{{ $items->count() }} item).
            </p>
            <div class="flex items-center gap-4 shrink-0">
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Termurah</p>
                    <p class="font-bold {{ $totalTermurah > (float) $package->pagu ? 'text-rose-600' : 'text-slate-800' }}">
                        Rp {{ number_format($totalTermurah, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-right border-l border-slate-200 pl-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pagu Anggaran</p>
                    <p class="font-bold text-emerald-600">Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================== TAB: PRATINJAU ====================== --}}
    <div x-show="tab === 'preview'" style="display: none;">
        <div class="border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-slate-50/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i data-lucide="file-check-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Dokumen Referensi Harga siap cetak.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="$refs.previewFrame.contentWindow.print()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak PDF
                    </button>
                    <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.price-references.print', $package) }}" target="_blank"
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
                            <i data-lucide="tags" class="w-5 h-5"></i>
                        </span>
                    </span>
                    <p class="text-sm font-semibold text-slate-600">Memuat dokumen...</p>
                </div>
                <iframe x-ref="previewFrame" @load="previewLoading = false"
                    class="w-full border-0 block" style="height: 900px;"></iframe>
            </div>
        </div>
    </div>

    {{-- ====================== MODAL TAMBAH/EDIT ====================== --}}
    <div x-show="modalOpen" style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        @keydown.escape.window="modalOpen = false">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalOpen = false"></div>

        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-200 delay-75"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <form method="POST" :action="formAction">
                @csrf
                <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">

                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500">
                            <i data-lucide="tags" class="w-4 h-4"></i>
                        </span>
                        <span x-text="mode === 'edit' ? 'Ubah Referensi Harga' : 'Tambah Referensi Harga'"></span>
                    </h3>
                    <button type="button" @click="modalOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Barang / Jasa</label>
                        <select name="technical_specification_item_id" x-model="form.item_id" required
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->nama_barang_jasa }} ({{ rtrim(rtrim(number_format((float) $item->volume, 2, ',', '.'), '0'), ',') }} {{ $item->satuan }})</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Isi otomatis dari link katalog --}}
                    <div class="rounded-lg border border-dashed border-indigo-300 bg-indigo-50/50 p-3">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-indigo-800 mb-1.5">
                            <i data-lucide="wand-2" class="w-3.5 h-3.5"></i> Isi Otomatis dari Katalog
                        </label>
                        <div class="flex gap-2">
                            <input type="url" x-model="importUrl" @keydown.enter.prevent="fetchFromLink()"
                                placeholder="Tempel link katalog.inaproc.id..."
                                class="flex-1 min-w-0 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <button type="button" @click="fetchFromLink()" :disabled="importing"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm disabled:opacity-60 disabled:cursor-not-allowed transition-colors shrink-0">
                                <i data-lucide="download" class="w-4 h-4" x-show="!importing"></i>
                                <svg x-show="importing" style="display:none;" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                                    <path d="M4 12a8 8 0 018-8" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="opacity-90"></path>
                                </svg>
                                <span x-text="importing ? 'Mengambil...' : 'Ambil'"></span>
                            </button>
                        </div>
                        <p x-show="importError" x-cloak x-text="importError" class="text-[11px] text-rose-600 font-semibold mt-1.5"></p>
                        <p x-show="importNotice" x-cloak x-text="importNotice" class="text-[11px] text-emerald-600 font-semibold mt-1.5"></p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Produk di Etalase</label>
                            <input type="text" name="nama_produk_etalase" x-model="form.etalase" placeholder="Nama produk"
                                class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pelaku Usaha / Penyedia</label>
                            <input type="text" name="nama_pelaku_usaha" x-model="form.pelaku" placeholder="Nama penyedia"
                                class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Harga Satuan (Rp)</label>
                        <input type="number" name="harga_satuan" x-model.number="form.harga" min="0" step="any" required placeholder="0"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-[11px] text-slate-400 mt-1" x-show="form.harga > 0">
                            Terbilang: <span class="font-semibold text-slate-500" x-text="formatRp(form.harga)"></span> per satuan
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                            Link Produk <span class="font-normal text-slate-400">(katalog elektronik)</span>
                        </label>
                        <input type="url" name="link_produk" x-model="form.link" placeholder="https://katalog.inaproc.id/..."
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>

                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="modalOpen = false"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm shadow-emerald-200 transition-colors">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Tambah Referensi'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function kabidRefHarga(config) {
        return {
            tab: 'editor',
            previewLoading: false,
            modalOpen: false,
            mode: 'create',
            formAction: config.storeUrl,
            form: { item_id: '', etalase: '', pelaku: '', harga: null, link: '' },

            importUrl: '',
            importing: false,
            importError: '',
            importNotice: '',

            resetImport() {
                this.importUrl = '';
                this.importError = '';
                this.importNotice = '';
                this.importing = false;
            },

            openCreate(itemId) {
                this.mode = 'create';
                this.formAction = config.storeUrl;
                this.form = { item_id: String(itemId), etalase: '', pelaku: '', harga: null, link: '' };
                this.resetImport();
                this.modalOpen = true;
            },

            openEdit(ref) {
                this.mode = 'edit';
                this.formAction = config.updateBase + '/' + ref.id;
                this.form = {
                    item_id: String(ref.item_id),
                    etalase: ref.etalase || '',
                    pelaku: ref.pelaku || '',
                    harga: ref.harga,
                    link: ref.link || '',
                };
                this.resetImport();
                this.importUrl = ref.link || '';
                this.modalOpen = true;
            },

            async fetchFromLink() {
                const url = this.importUrl.trim();
                this.importError = '';
                this.importNotice = '';
                if (!url) { this.importError = 'Tempel link katalog terlebih dahulu.'; return; }

                this.importing = true;
                try {
                    const res = await fetch(config.fetchUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': config.csrf,
                        },
                        body: JSON.stringify({ url }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.importError = data.message || 'Gagal mengambil data dari link.';
                        return;
                    }
                    if (data.nama_produk_etalase) this.form.etalase = data.nama_produk_etalase;
                    if (data.nama_pelaku_usaha) this.form.pelaku = data.nama_pelaku_usaha;
                    if (data.harga_satuan !== null && data.harga_satuan !== undefined) this.form.harga = data.harga_satuan;
                    this.form.link = data.link_produk || url;
                    this.importNotice = 'Data terisi otomatis. Silakan periksa & sesuaikan bila perlu.';
                } catch (e) {
                    this.importError = 'Terjadi kesalahan jaringan. Coba lagi.';
                } finally {
                    this.importing = false;
                }
            },

            openPreview() {
                this.tab = 'preview';
                this.previewLoading = true;
                this.$refs.previewFrame.src = config.printUrl + '&t=' + Date.now();
            },

            formatRp(n) {
                return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n || 0);
            },
        };
    }
</script>
@endif
