@csrf

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
        <div class="p-1.5 rounded-full bg-rose-100 shrink-0"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i></div>
        <div>
            <p class="text-sm font-bold text-rose-800">Terjadi kesalahan validasi</p>
            <ul class="mt-1 text-xs text-rose-600 list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="max-w-3xl">
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="file-signature" class="w-4 h-4"></i></div>
            <h3 class="text-sm font-bold text-slate-900">Informasi Surat Permohonan Pengadaan</h3>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nomor_surat" class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Urut Surat</label>
                    <x-ui.input type="text" name="nomor_surat" id="nomor_surat" :value="old('nomor_surat', $procurementRequest->nomor_surat)" :invalid="$errors->has('nomor_surat')" placeholder="Contoh: 015" />
                    <p class="mt-1 text-xs text-slate-400">Isi nomor urut surat saja.</p>
                    @error('nomor_surat') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tanggal_surat" class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Surat</label>
                    <x-ui.input type="date" name="tanggal_surat" id="tanggal_surat" :value="old('tanggal_surat', optional($procurementRequest->tanggal_surat)->format('Y-m-d'))" :invalid="$errors->has('tanggal_surat')" />
                    @error('tanggal_surat') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                <span class="text-xs font-semibold text-slate-500">Preview Nomor Surat</span>
                <p class="mt-0.5 text-sm font-bold text-emerald-700 font-mono">000.3.2/{{ old('nomor_surat', $procurementRequest->nomor_surat ?: 'XXX') }}/SP-PBJ/2.11.11/PERKIMPLH-C</p>
            </div>

            <div>
                <label for="nama_pejabat_pengadaan" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Pejabat Pengadaan</label>
                <x-ui.input type="text" name="nama_pejabat_pengadaan" id="nama_pejabat_pengadaan" :value="old('nama_pejabat_pengadaan', $procurementRequest->nama_pejabat_pengadaan)" :invalid="$errors->has('nama_pejabat_pengadaan')" placeholder="Masukkan nama pejabat pengadaan" />
                @error('nama_pejabat_pengadaan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="nama_penyedia" class="block text-sm font-semibold text-slate-700 mb-1.5">Penyedia yang Dipilih</label>
                <x-ui.select name="nama_penyedia" id="nama_penyedia" :invalid="$errors->has('nama_penyedia')">
                    <option value="">-- Pilih Penyedia --</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor }}" @selected(old('nama_penyedia', $procurementRequest->nama_penyedia) == $vendor)>{{ $vendor }}</option>
                    @endforeach
                </x-ui.select>
                @error('nama_penyedia') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="alasan_pemilihan_penyedia" class="block text-sm font-semibold text-slate-700 mb-1.5">Alasan Pemilihan Penyedia</label>
                <x-ui.textarea name="alasan_pemilihan_penyedia" id="alasan_pemilihan_penyedia" rows="4" :invalid="$errors->has('alasan_pemilihan_penyedia')" placeholder="Jelaskan alasan pemilihan penyedia...">{{ old('alasan_pemilihan_penyedia', $procurementRequest->alasan_pemilihan_penyedia) }}</x-ui.textarea>
                @error('alasan_pemilihan_penyedia') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="isi_surat" class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan Tambahan</label>
                <x-ui.textarea name="isi_surat" id="isi_surat" rows="3" :invalid="$errors->has('isi_surat')" placeholder="Keterangan tambahan (opsional)">{{ old('isi_surat', $procurementRequest->isi_surat) }}</x-ui.textarea>
                @error('isi_surat') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3 mt-6">
        <x-ui.button variant="secondary" size="md" href="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.show', $procurementPackage->package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
        <x-ui.button variant="primary" size="lg" type="submit">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel }}
        </x-ui.button>
    </div>
</div>
