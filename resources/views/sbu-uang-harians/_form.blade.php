@php
    $rate = $rate ?? null;
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

<div class="max-w-2xl">
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="wallet" class="w-4 h-4"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Data Uang Harian (Luar Daerah)</h3>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="provinsi" class="block text-sm font-semibold text-slate-700 mb-1.5">Provinsi <span class="text-rose-500">*</span></label>
                    <x-ui.input type="text" name="provinsi" id="provinsi" maxlength="255"
                        :value="old('provinsi', $rate->provinsi ?? null)" :invalid="$hasError('provinsi')" placeholder="Contoh: DKI Jakarta" required />
                    @error('provinsi') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="satuan" class="block text-sm font-semibold text-slate-700 mb-1.5">Satuan <span class="text-rose-500">*</span></label>
                    <x-ui.input type="text" name="satuan" id="satuan" maxlength="50"
                        :value="old('satuan', $rate->satuan ?? 'OH')" :invalid="$hasError('satuan')" required />
                    @error('satuan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="luar_kota" class="block text-sm font-semibold text-slate-700 mb-1.5">Uang Harian Luar Kota (Rp) <span class="text-rose-500">*</span></label>
                    <x-ui.input type="number" name="luar_kota" id="luar_kota" min="0"
                        :value="old('luar_kota', $rate->luar_kota ?? 0)" :invalid="$hasError('luar_kota')" required />
                    @error('luar_kota') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="diklat" class="block text-sm font-semibold text-slate-700 mb-1.5">Uang Harian Diklat (Rp) <span class="text-rose-500">*</span></label>
                    <x-ui.input type="number" name="diklat" id="diklat" min="0"
                        :value="old('diklat', $rate->diklat ?? 0)" :invalid="$hasError('diklat')" required />
                    @error('diklat') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
        <x-ui.button variant="secondary" size="md" href="{{ route('sbu-uang-harians.index') }}">
            <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
        </x-ui.button>
        <x-ui.button variant="primary" size="lg" type="submit">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
        </x-ui.button>
    </div>
</div>
