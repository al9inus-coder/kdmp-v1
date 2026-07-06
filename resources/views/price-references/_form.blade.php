@csrf

@php
    $selectedBarangJasaId = old('technical_specification_item_id', request('technical_specification_item_id'));
    if (!$selectedBarangJasaId) {
        $selectedBarangJasaId = collect($barangJasaOptions)
            ->first(fn ($option) => $option['nama_barang_jasa'] === $priceReference->nama_barang_jasa)['id'] ?? null;
    }
    $selectedBarangJasaId = $selectedBarangJasaId ?? array_key_first($barangJasaOptions);
    $selectedBarangJasaOption = $barangJasaOptions[$selectedBarangJasaId] ?? reset($barangJasaOptions);
@endphp

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
        <div class="p-1.5 rounded-full bg-rose-100 shrink-0"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i></div>
        <div>
            <p class="text-sm font-bold text-rose-800">Terjadi kesalahan validasi</p>
            <ul class="mt-1 text-xs text-rose-600 list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="max-w-3xl">
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="tag" class="w-4 h-4"></i></div>
            <h3 class="text-sm font-bold text-slate-900">Data Referensi Harga</h3>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <label for="nama_barang_jasa" class="block text-sm font-semibold text-slate-700 mb-1.5">Barang/Jasa <span class="text-rose-500">*</span></label>
                <x-ui.select name="technical_specification_item_id" id="nama_barang_jasa" :invalid="$errors->has('technical_specification_item_id')" required>
                    @foreach($barangJasaOptions as $barangJasa)
                        <option value="{{ $barangJasa['id'] }}" data-volume="{{ $barangJasa['volume'] }}" data-satuan="{{ $barangJasa['satuan'] }}" @selected((string) $selectedBarangJasaId === (string) $barangJasa['id'])>{{ $barangJasa['nama_barang_jasa'] }}</option>
                    @endforeach
                </x-ui.select>
                @error('technical_specification_item_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nama_produk_etalase" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Produk Etalase</label>
                <x-ui.input type="text" name="nama_produk_etalase" id="nama_produk_etalase" :value="old('nama_produk_etalase', $priceReference->nama_produk_etalase)" :invalid="$errors->has('nama_produk_etalase')" />
                @error('nama_produk_etalase') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="volume" class="block text-sm font-semibold text-slate-700 mb-1.5">Volume <span class="text-rose-500">*</span></label>
                    <x-ui.input type="number" id="volume" min="0" step="0.01" :value="$selectedBarangJasaOption['volume']" readonly />
                </div>
                <div>
                    <label for="satuan" class="block text-sm font-semibold text-slate-700 mb-1.5">Satuan</label>
                    <x-ui.input type="text" id="satuan" :value="$selectedBarangJasaOption['satuan']" readonly />
                </div>
            </div>

            <div>
                <label for="nama_pelaku_usaha" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Pelaku Usaha</label>
                <x-ui.input type="text" name="nama_pelaku_usaha" id="nama_pelaku_usaha" :value="old('nama_pelaku_usaha', $priceReference->nama_pelaku_usaha)" :invalid="$errors->has('nama_pelaku_usaha')" />
                @error('nama_pelaku_usaha') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="harga_satuan" class="block text-sm font-semibold text-slate-700 mb-1.5">Harga Satuan <span class="text-rose-500">*</span></label>
                    <x-ui.input type="number" name="harga_satuan" id="harga_satuan" min="0" step="0.01" :value="old('harga_satuan', $priceReference->harga_satuan ?? 0)" :invalid="$errors->has('harga_satuan')" required />
                    @error('harga_satuan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="jumlah_harga_display" class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Harga</label>
                    <x-ui.input type="text" id="jumlah_harga_display" :value="'Rp '.number_format((float) old('jumlah_harga', $priceReference->jumlah_harga ?? 0), 0, ',', '.')" readonly />
                    <p class="mt-1 text-xs text-slate-400">Dihitung otomatis dari volume dikali harga satuan.</p>
                </div>
            </div>

            <div>
                <label for="link_produk" class="block text-sm font-semibold text-slate-700 mb-1.5">Link Produk</label>
                <x-ui.textarea name="link_produk" id="link_produk" rows="2" :invalid="$errors->has('link_produk')">{{ old('link_produk', $priceReference->link_produk) }}</x-ui.textarea>
                @error('link_produk') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3 mt-6">
        <x-ui.button variant="secondary" size="md" href="{{ route('procurement-packages.price-references.index', $procurementPackage->package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
        <x-ui.button variant="primary" size="lg" type="submit">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
        </x-ui.button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const barangJasaInput = document.getElementById('nama_barang_jasa');
    const volumeInput = document.getElementById('volume');
    const satuanInput = document.getElementById('satuan');
    const hargaSatuanInput = document.getElementById('harga_satuan');
    const jumlahHargaDisplay = document.getElementById('jumlah_harga_display');
    if (!barangJasaInput) return;

    const formatRupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value || 0);

    const calculate = () => {
        const volume = parseFloat(volumeInput.value) || 0;
        const harga = parseFloat(hargaSatuanInput.value) || 0;
        jumlahHargaDisplay.value = formatRupiah(volume * harga);
    };

    const sync = () => {
        const opt = barangJasaInput.options[barangJasaInput.selectedIndex];
        volumeInput.value = opt.dataset.volume || 0;
        satuanInput.value = opt.dataset.satuan || '';
        calculate();
    };

    barangJasaInput.addEventListener('change', sync);
    hargaSatuanInput.addEventListener('input', calculate);
    sync();
});
</script>
