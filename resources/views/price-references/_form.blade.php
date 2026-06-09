@csrf

@php
    $selectedBarangJasaId = old('technical_specification_item_id');

    if (!$selectedBarangJasaId) {
        $selectedBarangJasaId = collect($barangJasaOptions)
            ->first(fn ($option) => $option['nama_barang_jasa'] === $priceReference->nama_barang_jasa)['id'] ?? null;
    }

    $selectedBarangJasaId = $selectedBarangJasaId ?? array_key_first($barangJasaOptions);
    $selectedBarangJasaOption = $barangJasaOptions[$selectedBarangJasaId] ?? reset($barangJasaOptions);
@endphp

<div class="form-group">
    <label for="nama_barang_jasa">Barang/Jasa <span class="text-danger">*</span></label>
    <select id="nama_barang_jasa"
            name="technical_specification_item_id"
            class="form-control @error('technical_specification_item_id') is-invalid @enderror"
            required>
        @foreach($barangJasaOptions as $barangJasa)
            <option value="{{ $barangJasa['id'] }}"
                    data-volume="{{ $barangJasa['volume'] }}"
                    data-satuan="{{ $barangJasa['satuan'] }}"
                    @selected((string) $selectedBarangJasaId === (string) $barangJasa['id'])>
                {{ $barangJasa['nama_barang_jasa'] }}
            </option>
        @endforeach
    </select>
    @error('technical_specification_item_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="nama_produk_etalase">Nama Produk Etalase</label>
    <input type="text"
           id="nama_produk_etalase"
           name="nama_produk_etalase"
           class="form-control @error('nama_produk_etalase') is-invalid @enderror"
           value="{{ old('nama_produk_etalase', $priceReference->nama_produk_etalase) }}">
    @error('nama_produk_etalase')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="volume">Volume <span class="text-danger">*</span></label>
            <input type="number"
                   id="volume"
                   min="0"
                   step="0.01"
                   class="form-control"
                   value="{{ $selectedBarangJasaOption['volume'] }}"
                   readonly>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="satuan">Satuan</label>
            <input type="text"
                   id="satuan"
                   class="form-control"
                   value="{{ $selectedBarangJasaOption['satuan'] }}"
                   readonly>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="nama_pelaku_usaha">Nama Pelaku Usaha</label>
    <input type="text"
           id="nama_pelaku_usaha"
           name="nama_pelaku_usaha"
           class="form-control @error('nama_pelaku_usaha') is-invalid @enderror"
           value="{{ old('nama_pelaku_usaha', $priceReference->nama_pelaku_usaha) }}">
    @error('nama_pelaku_usaha')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="harga_satuan">Harga Satuan <span class="text-danger">*</span></label>
            <input type="number"
                   id="harga_satuan"
                   name="harga_satuan"
                   min="0"
                   step="0.01"
                   class="form-control @error('harga_satuan') is-invalid @enderror"
                   value="{{ old('harga_satuan', $priceReference->harga_satuan ?? 0) }}"
                   required>
            @error('harga_satuan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="jumlah_harga_display">Jumlah Harga</label>
            <input type="text"
                   id="jumlah_harga_display"
                   class="form-control"
                   value="Rp {{ number_format((float) old('jumlah_harga', $priceReference->jumlah_harga ?? 0), 0, ',', '.') }}"
                   readonly>
            <small class="form-text text-muted">Dihitung otomatis dari volume dikali harga satuan.</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="link_produk">Link Produk</label>
    <textarea id="link_produk"
              name="link_produk"
              rows="2"
              class="form-control @error('link_produk') is-invalid @enderror">{{ old('link_produk', $priceReference->link_produk) }}</textarea>
    @error('link_produk')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-success">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('procurement-packages.price-references.index', $procurementPackage->package) }}" class="btn btn-secondary">
        Kembali
    </a>
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const barangJasaInput = document.getElementById('nama_barang_jasa');
            const volumeInput = document.getElementById('volume');
            const satuanInput = document.getElementById('satuan');
            const hargaSatuanInput = document.getElementById('harga_satuan');
            const jumlahHargaDisplay = document.getElementById('jumlah_harga_display');

            const formatRupiah = function (value) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0,
                }).format(value || 0);
            };

            const calculateJumlahHarga = function () {
                const volume = parseFloat(volumeInput.value) || 0;
                const hargaSatuan = parseFloat(hargaSatuanInput.value) || 0;

                jumlahHargaDisplay.value = formatRupiah(volume * hargaSatuan);
            };

            const syncBarangJasa = function () {
                const selectedOption = barangJasaInput.options[barangJasaInput.selectedIndex];

                volumeInput.value = selectedOption.dataset.volume || 0;
                satuanInput.value = selectedOption.dataset.satuan || '';
                calculateJumlahHarga();
            };

            barangJasaInput.addEventListener('change', syncBarangJasa);
            hargaSatuanInput.addEventListener('input', calculateJumlahHarga);
            syncBarangJasa();
        });
    </script>
@endpush
