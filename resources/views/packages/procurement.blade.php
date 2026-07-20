@component('layouts.kdmp')
@section('title', 'Persiapan Pengadaan')

<x-ui.toast />

<x-ui.workspace title="Persiapan Pengadaan" description="{{ $package->nama_paket }}">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.show', $package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form method="POST" action="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.procurement.update', $package) }}">
        @csrf
        @method('PUT')

        <div class="max-w-2xl">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Data Persiapan Pengadaan</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">ID RUP</label>
                            <x-ui.input type="text" :value="$package->id_rup" readonly />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status Pengadaan</label>
                            <x-ui.input type="text" :value="$package->procurement_status" readonly />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="pptk_name" class="block text-sm font-semibold text-slate-700 mb-1.5">PPTK</label>
                            <x-ui.input type="text" name="pptk_name" id="pptk_name" :value="old('pptk_name', $package->pptk_name)" :invalid="$errors->has('pptk_name')" />
                            @error('pptk_name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="ppk_name" class="block text-sm font-semibold text-slate-700 mb-1.5">PPK</label>
                            <x-ui.input type="text" name="ppk_name" id="ppk_name" :value="old('ppk_name', $package->ppk_name)" :invalid="$errors->has('ppk_name')" />
                            @error('ppk_name') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="target_procurement_date" class="block text-sm font-semibold text-slate-700 mb-1.5">Target Pengadaan</label>
                        <x-ui.input type="date" name="target_procurement_date" id="target_procurement_date"
                            :value="old('target_procurement_date', optional($package->target_procurement_date)->format('Y-m-d'))" :invalid="$errors->has('target_procurement_date')" />
                        @error('target_procurement_date') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="procurement_notes" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan</label>
                        <x-ui.textarea name="procurement_notes" id="procurement_notes" rows="4" :invalid="$errors->has('procurement_notes')">{{ old('procurement_notes', $package->procurement_notes) }}</x-ui.textarea>
                        @error('procurement_notes') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <x-ui.button variant="secondary" size="md" href="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.show', $package) }}">
                    <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
                </x-ui.button>
                <x-ui.button variant="primary" size="lg" type="submit">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Simpan
                </x-ui.button>
            </div>
        </div>
    </form>
</x-ui.workspace>
@endcomponent
