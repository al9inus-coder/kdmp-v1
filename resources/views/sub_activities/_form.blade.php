@csrf

<div class="form-group">
    <label for="activity_id">Kegiatan</label>
    <select id="activity_id"
            name="activity_id"
            class="form-control @error('activity_id') is-invalid @enderror"
            required>
        <option value="">Pilih Kegiatan</option>
        @foreach($activities as $activity)
            <option value="{{ $activity->id }}"
                @selected((int) old('activity_id', $subActivity->activity_id) === $activity->id)>
                {{ $activity->kode }} - {{ $activity->nama }}
            </option>
        @endforeach
    </select>
    @error('activity_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="kode">Kode Sub Kegiatan</label>
    <input type="text"
           id="kode"
           name="kode"
           maxlength="50"
           class="form-control @error('kode') is-invalid @enderror"
           value="{{ old('kode', $subActivity->kode) }}"
           placeholder="Contoh: 1.02.01.001.01"
           required>
    @error('kode')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="nama">Nama Sub Kegiatan</label>
    <input type="text"
           id="nama"
           name="nama"
           maxlength="255"
           class="form-control @error('nama') is-invalid @enderror"
           value="{{ old('nama', $subActivity->nama) }}"
           placeholder="Masukkan nama sub kegiatan"
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
        <option value="1" @selected((int) old('is_active', $subActivity->is_active) === 1)>
            Aktif
        </option>
        <option value="0" @selected((int) old('is_active', $subActivity->is_active) === 0)>
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
    <a href="{{ route('sub-activities.index') }}" class="btn btn-secondary">
        Kembali
    </a>
</div>
