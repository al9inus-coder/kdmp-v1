@php
    $baru = !$anggaran->exists;
    $val = fn ($field, $default = null) => old($field, $anggaran->{$field} ?? $default);
    $hasError = fn ($field) => $errors->has($field);
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

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <i data-lucide="wallet" class="w-4 h-4"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Identitas Baris Anggaran</h3>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="fiscal_year_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Tahun Anggaran <span class="text-rose-500">*</span>
                </label>
                <x-ui.select name="fiscal_year_id" id="fiscal_year_id" :invalid="$hasError('fiscal_year_id')" required>
                    @foreach($fiscalYears as $fy)
                        <option value="{{ $fy->id }}" @selected($val('fiscal_year_id') == $fy->id)>TA {{ $fy->tahun }}</option>
                    @endforeach
                </x-ui.select>
                @error('fiscal_year_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="account_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Rekening Belanja <span class="text-rose-500">*</span>
                </label>
                <x-ui.select name="account_id" id="account_id" :invalid="$hasError('account_id')" required>
                    <option value="">— Pilih rekening —</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}" @selected($val('account_id') == $acc->id)>{{ $acc->kode }} — {{ $acc->nama }}</option>
                    @endforeach
                </x-ui.select>
                @error('account_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="sub_activity_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                Sub Kegiatan <span class="text-rose-500">*</span>
            </label>
            <x-ui.select name="sub_activity_id" id="sub_activity_id" :invalid="$hasError('sub_activity_id')" required>
                <option value="">— Pilih sub kegiatan —</option>
                @foreach($subActivities as $sa)
                    <option value="{{ $sa->id }}" @selected($val('sub_activity_id') == $sa->id)>
                        {{ $sa->kode }} — {{ $sa->nama }}
                    </option>
                @endforeach
            </x-ui.select>
            @error('sub_activity_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-slate-400">Satu rekening hanya boleh muncul sekali dalam satu sub kegiatan per tahun.</p>
        </div>

        <div>
            <label for="keterangan" class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan</label>
            <x-ui.textarea name="keterangan" id="keterangan" rows="2" :invalid="$hasError('keterangan')"
                placeholder="Catatan internal (opsional)">{{ $val('keterangan') }}</x-ui.textarea>
            @error('keterangan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
</section>

@if($baru)
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-5">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <i data-lucide="file-plus" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pagu Awal</h3>
                <p class="text-xs text-slate-400">Dicatat sebagai revisi pertama pada riwayat baris ini.</p>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jenis" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Tahap Anggaran <span class="text-rose-500">*</span>
                    </label>
                    <x-ui.select name="jenis" id="jenis" :invalid="$hasError('jenis')" required>
                        @foreach($jenisOptions as $key => $label)
                            <option value="{{ $key }}" @selected(old('jenis', 'murni') === $key)>{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                    @error('jenis') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="pagu" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Pagu (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <x-ui.input type="number" name="pagu" id="pagu" step="0.01" min="0"
                        :value="old('pagu')" :invalid="$hasError('pagu')" placeholder="0" required />
                    @error('pagu') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tanggal" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Dasar</label>
                    <x-ui.input type="date" name="tanggal" id="tanggal" :value="old('tanggal')" :invalid="$hasError('tanggal')" />
                    @error('tanggal') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="nomor_dasar" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Dasar Hukum</label>
                    <x-ui.input type="text" name="nomor_dasar" id="nomor_dasar" :value="old('nomor_dasar')"
                        :invalid="$hasError('nomor_dasar')" placeholder="Perda / Perkada" />
                    @error('nomor_dasar') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </section>
@endif

<div class="flex flex-wrap items-center justify-end gap-3 mt-6">
    <x-ui.button variant="secondary" size="md" href="{{ route('admin.anggaran.index') }}">
        <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
    </x-ui.button>
    <x-ui.button variant="primary" size="lg" type="submit">
        <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
    </x-ui.button>
</div>
