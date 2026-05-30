@csrf

<div class="form-group">
    <label for="program_id">Program</label>
    <select id="program_id"
            name="program_id"
            class="form-control @error('program_id') is-invalid @enderror"
            required>
        <option value="">Pilih Program</option>
        @foreach($programs as $program)
            <option value="{{ $program->id }}"
                @selected((int) old('program_id', $activity->program_id) === $program->id)>
                {{ $program->kode }} - {{ $program->nama }}
            </option>
        @endforeach
    </select>
    @error('program_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="kode">Kode Kegiatan</label>
    <input type="text"
           id="kode"
           name="kode"
           maxlength="50"
           class="form-control @error('kode') is-invalid @enderror"
           value="{{ old('kode', $activity->kode) }}"
           placeholder="Contoh: 1.02.01.001"
           required>
    @error('kode')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="nama">Nama Kegiatan</label>
    <input type="text"
           id="nama"
           name="nama"
           maxlength="255"
           class="form-control @error('nama') is-invalid @enderror"
           value="{{ old('nama', $activity->nama) }}"
           placeholder="Masukkan nama kegiatan"
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
        <option value="1" @selected((int) old('is_active', $activity->is_active) === 1)>
            Aktif
        </option>
        <option value="0" @selected((int) old('is_active', $activity->is_active) === 0)>
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
    <a href="{{ route('activities.index') }}" class="btn btn-secondary">
        Kembali
    </a>
</div>
