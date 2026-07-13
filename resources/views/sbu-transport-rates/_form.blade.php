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
                <i data-lucide="car" class="w-4 h-4"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Data Biaya Transportasi</h3>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="tempat_kedudukan" class="block text-sm font-semibold text-slate-700 mb-1.5">Tempat Kedudukan <span class="text-rose-500">*</span></label>
                    <x-ui.input type="text" name="tempat_kedudukan" id="tempat_kedudukan" maxlength="255"
                        :value="old('tempat_kedudukan', $rate->tempat_kedudukan ?? 'Bengkayang')" :invalid="$hasError('tempat_kedudukan')" required />
                    @error('tempat_kedudukan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tempat_tujuan" class="block text-sm font-semibold text-slate-700 mb-1.5">Tempat Tujuan <span class="text-rose-500">*</span></label>
                    <x-ui.input type="text" name="tempat_tujuan" id="tempat_tujuan" maxlength="255"
                        :value="old('tempat_tujuan', $rate->tempat_tujuan ?? null)" :invalid="$hasError('tempat_tujuan')" placeholder="Contoh: Sungai Raya" required />
                    @error('tempat_tujuan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="satuan" class="block text-sm font-semibold text-slate-700 mb-1.5">Satuan <span class="text-rose-500">*</span></label>
                <x-ui.input type="text" name="satuan" id="satuan" maxlength="50"
                    :value="old('satuan', $rate->satuan ?? 'PP')" :invalid="$hasError('satuan')" required />
                @error('satuan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="biaya_mobil" class="block text-sm font-semibold text-slate-700 mb-1.5">Biaya Mobil (Rp) <span class="text-rose-500">*</span></label>
                    <x-ui.input type="number" name="biaya_mobil" id="biaya_mobil" min="0"
                        :value="old('biaya_mobil', $rate->biaya_mobil ?? 0)" :invalid="$hasError('biaya_mobil')" required />
                    @error('biaya_mobil') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="biaya_motor" class="block text-sm font-semibold text-slate-700 mb-1.5">Biaya Sepeda Motor (Rp) <span class="text-rose-500">*</span></label>
                    <x-ui.input type="number" name="biaya_motor" id="biaya_motor" min="0"
                        :value="old('biaya_motor', $rate->biaya_motor ?? 0)" :invalid="$hasError('biaya_motor')" required />
                    @error('biaya_motor') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
        <x-ui.button variant="secondary" size="md" href="{{ route('admin.sbu-transport-rates.index') }}">
            <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
        </x-ui.button>
        <x-ui.button variant="primary" size="lg" type="submit">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
        </x-ui.button>
    </div>
</div>
