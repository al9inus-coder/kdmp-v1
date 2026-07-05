@php
    $val = fn($field) => old($field, $subActivity->{$field});
    $hasError = fn($field) => $errors->has($field);
@endphp

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
        <div class="p-1.5 rounded-full bg-rose-100 shrink-0">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
        </div>
        <div>
            <p class="text-sm font-bold text-rose-800">Terjadi kesalahan validasi</p>
            <ul class="mt-1 text-xs text-rose-600 list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden max-w-2xl">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <i data-lucide="layers" class="w-4 h-4"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Informasi Sub Kegiatan</h3>
    </div>
    <div class="p-6 space-y-4">
        <div>
            <label for="activity_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                Kegiatan <span class="text-rose-500">*</span>
            </label>
            <x-ui.select name="activity_id" id="activity_id" :invalid="$hasError('activity_id')" required>
                <option value="">Pilih Kegiatan</option>
                @foreach($activities as $activity)
                    <option value="{{ $activity->id }}" @selected((int) $val('activity_id') === $activity->id)>
                        {{ $activity->kode }} - {{ $activity->nama }}
                    </option>
                @endforeach
            </x-ui.select>
            @error('activity_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="kode" class="block text-sm font-semibold text-slate-700 mb-1.5">
                Kode Sub Kegiatan <span class="text-rose-500">*</span>
            </label>
            <x-ui.input type="text" name="kode" id="kode" maxlength="50" placeholder="Contoh: 1.02.01.001.01" :value="$val('kode')" :invalid="$hasError('kode')" required />
            @error('kode') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="nama" class="block text-sm font-semibold text-slate-700 mb-1.5">
                Nama Sub Kegiatan <span class="text-rose-500">*</span>
            </label>
            <x-ui.input type="text" name="nama" id="nama" maxlength="255" placeholder="Masukkan nama sub kegiatan" :value="$val('nama')" :invalid="$hasError('nama')" required />
            @error('nama') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="is_active" class="block text-sm font-semibold text-slate-700 mb-1.5">
                Status <span class="text-rose-500">*</span>
            </label>
            <x-ui.select name="is_active" id="is_active" :invalid="$hasError('is_active')" required>
                <option value="1" @selected((int) $val('is_active') === 1)>Aktif</option>
                <option value="0" @selected((int) $val('is_active') === 0)>Nonaktif</option>
            </x-ui.select>
            @error('is_active') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</section>

<div class="flex flex-wrap items-center justify-end gap-3 mt-8 max-w-2xl">
    <x-ui.button variant="secondary" size="md" href="{{ route('sub-activities.index') }}">
        <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
    </x-ui.button>
    <x-ui.button variant="primary" size="lg" type="submit">
        <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
    </x-ui.button>
</div>
