@php
    $package = $procurementPackage->package;
    $rekomendasi = $procurementPackage->procurementRequest?->nama_penyedia;
@endphp

<form method="POST" id="form-data-penyedia"
      action="{{ route('kabid.procurement-packages.procurement-process.vendor.update', $package) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Kolom kiri: identitas penyedia --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="store" class="w-4 h-4 text-blue-500"></i> Identitas Penyedia
            </h3>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100">
                <div class="p-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Penyedia</label>
                    <input type="text" name="nama_penyedia"
                        value="{{ old('nama_penyedia', $process->nama_penyedia) }}"
                        placeholder="Contoh: PT. Maju Bersama"
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    @if($rekomendasi)
                        <p class="text-[11px] text-slate-400 mt-1.5">
                            <i data-lucide="mail" class="w-3 h-3 inline-block -mt-0.5"></i>
                            Rekomendasi dari Surat Permohonan: <strong class="text-slate-500">{{ $rekomendasi }}</strong>
                        </p>
                    @endif
                </div>
                <div class="p-4">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alamat Penyedia</label>
                    <textarea name="alamat_penyedia" rows="3" placeholder="Alamat lengkap penyedia..."
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">{{ old('alamat_penyedia', $process->alamat_penyedia) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Kolom kanan: wakil sah --}}
        <div class="space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4 text-blue-500"></i> Wakil Sah Penyedia
            </h3>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100">
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama PIC (Wakil Sah)</label>
                        <input type="text" name="nama_pic"
                            value="{{ old('nama_pic', $process->nama_pic) }}"
                            placeholder="Nama lengkap"
                            class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jabatan PIC</label>
                        <input type="text" name="jabatan_pic"
                            value="{{ old('jabatan_pic', $process->jabatan_pic) }}"
                            placeholder="Contoh: Direktur Utama"
                            class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                        <p class="text-[11px] text-slate-500 mt-1.5 leading-snug">
                            <i data-lucide="info" class="w-3 h-3 inline-block -mt-0.5"></i>
                            Ketik <strong>Penyedia</strong> jika merupakan perorangan (bukan badan usaha).
                        </p>
                    </div>
                </div>
                <div class="p-4 bg-slate-50/70 rounded-b-xl">
                    <p class="text-[11px] text-slate-400 leading-snug">
                        <i data-lucide="info" class="w-3 h-3 inline-block -mt-0.5"></i>
                        NPWP, nama bank, dan nomor rekening penyedia diisi nanti pada
                        <strong class="text-slate-500">tahap Pembayaran</strong> — ketiganya baru dipakai
                        oleh BAP, Ringkasan Kontrak, dan surat Non-PKP.
                    </p>
                </div>
            </div>
        </div>
    </div>
</form>
