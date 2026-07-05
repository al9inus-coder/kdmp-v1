@php
    $skpd = $skpd ?? null;
    $val = fn($field) => old($field, $skpd?->{$field});
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Kolom Kiri --}}
    <div class="space-y-6">
        {{-- Informasi SKPD --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Informasi Perangkat Daerah</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="kode" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Kode SKPD <span class="text-rose-500">*</span>
                    </label>
                    <x-ui.input type="text" name="kode" id="kode" :value="$val('kode')" :invalid="$hasError('kode')" required />
                    @error('kode') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="nama" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nama SKPD <span class="text-rose-500">*</span>
                    </label>
                    <x-ui.input type="text" name="nama" id="nama" :value="$val('nama')" :invalid="$hasError('nama')" required />
                    @error('nama') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="singkatan" class="block text-sm font-semibold text-slate-700 mb-1.5">Singkatan Nama</label>
                    <x-ui.input type="text" name="singkatan" id="singkatan" :value="$val('singkatan')" :invalid="$hasError('singkatan')" />
                </div>
                <div>
                    <label for="npwp_dinas" class="block text-sm font-semibold text-slate-700 mb-1.5">NPWP Dinas</label>
                    <x-ui.input type="text" name="npwp_dinas" id="npwp_dinas" :value="$val('npwp_dinas')" :invalid="$hasError('npwp_dinas')" />
                </div>
                <div>
                    <label for="alamat" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat</label>
                    <x-ui.textarea name="alamat" id="alamat" rows="3" :invalid="$hasError('alamat')">{{ $val('alamat') }}</x-ui.textarea>
                </div>
            </div>
        </section>

        {{-- PA / Kadis --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Pengguna Anggaran (PA) / Kepala Dinas</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="kepala_skpd" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kepala Perangkat Daerah</label>
                    <x-ui.input type="text" name="kepala_skpd" id="kepala_skpd" :value="$val('kepala_skpd')" />
                </div>
                <div>
                    <label for="nip_kepala" class="block text-sm font-semibold text-slate-700 mb-1.5">NIP Kepala Perangkat Daerah</label>
                    <x-ui.input type="text" name="nip_kepala" id="nip_kepala" :value="$val('nip_kepala')" />
                </div>
            </div>
        </section>
    </div>

    {{-- Kolom Kanan --}}
    <div class="space-y-6">
        {{-- PPK --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Pejabat Pembuat Komitmen (PPK)</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="nama_ppk" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama PPK</label>
                    <x-ui.input type="text" name="nama_ppk" id="nama_ppk" :value="$val('nama_ppk')" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nip_ppk" class="block text-sm font-semibold text-slate-700 mb-1.5">NIP PPK</label>
                        <x-ui.input type="text" name="nip_ppk" id="nip_ppk" :value="$val('nip_ppk')" />
                    </div>
                    <div>
                        <label for="pangkat_ppk" class="block text-sm font-semibold text-slate-700 mb-1.5">Pangkat / Golongan</label>
                        <x-ui.input type="text" name="pangkat_ppk" id="pangkat_ppk" :value="$val('pangkat_ppk')" />
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="telepon_ppk" class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telpon</label>
                        <x-ui.input type="text" name="telepon_ppk" id="telepon_ppk" :value="$val('telepon_ppk')" />
                    </div>
                    <div>
                        <label for="email_ppk" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                        <x-ui.input type="email" name="email_ppk" id="email_ppk" :value="$val('email_ppk')" />
                    </div>
                </div>
                <div>
                    <label for="username_ppk" class="block text-sm font-semibold text-slate-700 mb-1.5">Username PPK (SPSE/LPSE)</label>
                    <x-ui.input type="text" name="username_ppk" id="username_ppk" :value="$val('username_ppk')" />
                </div>
            </div>
        </section>

        {{-- PPTK --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Pejabat Pelaksana Teknis Kegiatan (PPTK)</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="nama_pptk" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama PPTK</label>
                    <x-ui.input type="text" name="nama_pptk" id="nama_pptk" :value="$val('nama_pptk')" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nip_pptk" class="block text-sm font-semibold text-slate-700 mb-1.5">NIP PPTK</label>
                        <x-ui.input type="text" name="nip_pptk" id="nip_pptk" :value="$val('nip_pptk')" />
                    </div>
                    <div>
                        <label for="pangkat_pptk" class="block text-sm font-semibold text-slate-700 mb-1.5">Pangkat / Golongan</label>
                        <x-ui.input type="text" name="pangkat_pptk" id="pangkat_pptk" :value="$val('pangkat_pptk')" />
                    </div>
                </div>
            </div>
        </section>

        {{-- Bendahara --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Bendahara Pengeluaran</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_bendahara" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Bendahara</label>
                        <x-ui.input type="text" name="nama_bendahara" id="nama_bendahara" :value="$val('nama_bendahara')" />
                    </div>
                    <div>
                        <label for="nip_bendahara" class="block text-sm font-semibold text-slate-700 mb-1.5">NIP Bendahara</label>
                        <x-ui.input type="text" name="nip_bendahara" id="nip_bendahara" :value="$val('nip_bendahara')" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="flex flex-wrap items-center justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
    <x-ui.button variant="secondary" size="md" href="{{ route('skpds.index') }}">
        <i data-lucide="x" class="w-4 h-4 mr-2"></i> Batal
    </x-ui.button>
    <x-ui.button variant="primary" size="lg" type="submit">
        <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
    </x-ui.button>
</div>
