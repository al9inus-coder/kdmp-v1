@php
    $employee = $employee ?? null;
    $val = fn($field) => old($field, $employee?->{$field});
    $hasError = fn($field) => $errors->has($field);
    $kategori = old('kategori_biaya', $employee?->kategori_biaya);
    $tglLahir = old('tanggal_lahir', $employee && $employee->tanggal_lahir ? $employee->tanggal_lahir->format('Y-m-d') : '');
    $kategoriOptions = [
        'Eselon II',
        'Eselon III, Gol. IV dan Jafung Madya',
        'Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN',
    ];
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

<div class="max-w-2xl">
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="user" class="w-4 h-4"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Data Pegawai</h3>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <label for="nama" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <x-ui.input type="text" name="nama" id="nama" maxlength="255" :value="$val('nama')"
                    :invalid="$hasError('nama')" placeholder="Nama lengkap pegawai" required />
                @error('nama') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nip" class="block text-sm font-semibold text-slate-700 mb-1.5">NIP</label>
                    <x-ui.input type="text" name="nip" id="nip" maxlength="50" :value="$val('nip')"
                        :invalid="$hasError('nip')" placeholder="NIP pegawai" />
                    @error('nip') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="golongan" class="block text-sm font-semibold text-slate-700 mb-1.5">Golongan</label>
                    <x-ui.input type="text" name="golongan" id="golongan" maxlength="50" :value="$val('golongan')"
                        :invalid="$hasError('golongan')" placeholder="Contoh: III/c" />
                    @error('golongan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jabatan" class="block text-sm font-semibold text-slate-700 mb-1.5">Jabatan</label>
                    <x-ui.input type="text" name="jabatan" id="jabatan" maxlength="255" :value="$val('jabatan')"
                        :invalid="$hasError('jabatan')" placeholder="Jabatan pegawai" />
                    @error('jabatan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Lahir</label>
                    <x-ui.input type="date" name="tanggal_lahir" id="tanggal_lahir" :value="$tglLahir" :invalid="$hasError('tanggal_lahir')" />
                    @error('tanggal_lahir') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="tipe" class="block text-sm font-semibold text-slate-700 mb-1.5">Tipe Pegawai</label>
                <x-ui.select name="tipe" id="tipe" :invalid="$hasError('tipe')">
                    <option value="dinas" @selected(old('tipe', $employee?->tipe ?? 'dinas') === 'dinas')>Pegawai Dinas</option>
                    <option value="kebersihan" @selected(old('tipe', $employee?->tipe) === 'kebersihan')>Petugas Kebersihan</option>
                </x-ui.select>
                <p class="mt-1 text-xs text-slate-400">Petugas kebersihan tidak muncul di roster lembur pegawai dinas.</p>
                @error('tipe') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="kategori_biaya" class="block text-sm font-semibold text-slate-700 mb-1.5">Kategori Biaya Perjalanan (SBU)</label>
                <x-ui.select name="kategori_biaya" id="kategori_biaya" :invalid="$hasError('kategori_biaya')">
                    <option value="">— Otomatis berdasarkan Jabatan/Golongan —</option>
                    @foreach($kategoriOptions as $opt)
                        <option value="{{ $opt }}" @selected($kategori === $opt)>{{ $opt }}</option>
                    @endforeach
                </x-ui.select>
                @error('kategori_biaya') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
        <x-ui.button variant="secondary" size="md" href="{{ route('admin.employees.index') }}">
            <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
        </x-ui.button>
        <x-ui.button variant="primary" size="lg" type="submit">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
        </x-ui.button>
    </div>
</div>
