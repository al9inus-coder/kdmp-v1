@component('layouts.kdmp')
@section('title', 'Tambah Tahun Anggaran')

<x-ui.toast />

<x-ui.workspace title="Tambah Tahun Anggaran" description="Tambahkan tahun anggaran baru ke sistem.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('fiscal-years.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('fiscal-years.store') }}" method="POST">
        @csrf

        @if ($errors->any())
            <div class="mb-6 max-w-2xl flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
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
                        <i data-lucide="calendar" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Data Tahun Anggaran</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label for="tahun" class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Tahun <span class="text-rose-500">*</span>
                        </label>
                        <x-ui.input type="number" name="tahun" id="tahun" :value="old('tahun')"
                            :invalid="$errors->has('tahun')" placeholder="2027" required />
                        @error('tahun') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active'))
                            class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-medium text-slate-700">Jadikan Tahun Aktif</span>
                    </label>
                </div>
            </section>

            <div class="flex flex-wrap items-center justify-end gap-3 mt-6">
                <x-ui.button variant="secondary" size="md" href="{{ route('fiscal-years.index') }}">
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
