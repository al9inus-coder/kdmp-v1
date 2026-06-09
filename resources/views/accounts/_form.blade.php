@csrf

<div class="form-group">
    <label for="kode">Kode Rekening Belanja</label>
    <input type="text"
           id="kode"
           name="kode"
           maxlength="50"
           class="form-control @error('kode') is-invalid @enderror"
           value="{{ old('kode', $account->kode) }}"
           placeholder="Contoh: 5.1.02.01"
           required>
    @error('kode')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="nama">Nama Rekening Belanja</label>
    <input type="text"
           id="nama"
           name="nama"
           maxlength="255"
           class="form-control @error('nama') is-invalid @enderror"
           value="{{ old('nama', $account->nama) }}"
           placeholder="Masukkan nama rekening belanja"
           required>
    @error('nama')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="is_active">Status</label>
    <select id="is_active"
            name="is_active"
            class="form-control @error('is_active') is-invalid @enderror"
            required>
        <option value="1" @selected((int) old('is_active', $account->is_active) === 1)>
            Aktif
        </option>
        <option value="0" @selected((int) old('is_active', $account->is_active) === 0)>
            Nonaktif
        </option>
    </select>
    @error('is_active')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mt-3">
    <button type="submit" class="btn btn-success">
        {{ $submitLabel }}
    </button>
    <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
        Kembali
    </a>
</div>
