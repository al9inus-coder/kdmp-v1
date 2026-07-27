@php
    $package = $procurementPackage->package;
    $rekomendasi = $procurementPackage->procurementRequest?->nama_penyedia;
@endphp

{{-- Panel pembungkus sudah berupa kartu lengkap dengan judul dan tombol
     Simpan, jadi di sini tidak perlu kartu lagi.

     Kedua bagian ditaruh berdampingan supaya lebar kartu terpakai habis.
     Membatasi lebar form menyisakan ruang kosong di kanan, sedangkan
     memelarkan input pendek sampai selebar kartu juga tidak enak dilihat. --}}
<form method="POST" id="form-data-penyedia"
      action="{{ route('kabid.procurement-packages.procurement-process.vendor.update', $package) }}"
      class="grid grid-cols-1 xl:grid-cols-2 gap-x-10 gap-y-8">
    @csrf
    @method('PUT')

    {{-- Kiri: identitas --}}
    <section>
        <div class="flex items-center gap-3 mb-4">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Identitas Penyedia</p>
            <span class="flex-1 h-px bg-slate-100"></span>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Penyedia</label>
                <input type="text" name="nama_penyedia"
                    value="{{ old('nama_penyedia', $process->nama_penyedia) }}"
                    placeholder="Contoh: PT. Maju Bersama"
                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                @if($rekomendasi)
                    <p class="text-[11px] text-slate-400 mt-1.5 leading-snug">
                        Rekomendasi surat permohonan:
                        <button type="button"
                            @click="$root.querySelector('[name=nama_penyedia]').value = @js($rekomendasi)"
                            class="font-semibold text-blue-600 hover:text-blue-700 hover:underline">
                            {{ $rekomendasi }}
                        </button>
                    </p>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alamat Penyedia</label>
                <textarea name="alamat_penyedia" rows="3" placeholder="Alamat lengkap penyedia…"
                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('alamat_penyedia', $process->alamat_penyedia) }}</textarea>
            </div>
        </div>
    </section>

    {{-- Kanan: wakil sah --}}
    <section>
        <div class="flex items-center gap-3 mb-4">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Wakil Sah Penyedia</p>
            <span class="flex-1 h-px bg-slate-100"></span>
        </div>

        <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama PIC</label>
                    <input type="text" name="nama_pic"
                        value="{{ old('nama_pic', $process->nama_pic) }}"
                        placeholder="Nama lengkap"
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jabatan PIC</label>
                    <input type="text" name="jabatan_pic"
                        value="{{ old('jabatan_pic', $process->jabatan_pic) }}"
                        placeholder="Contoh: Direktur Utama"
                        class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                </div>
            </div>

            {{-- Catatan diberi tempat sendiri, bukan tulisan kecil menempel di
                 bawah kolom — sekaligus mengisi tinggi kolom kanan. --}}
            <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-xl bg-slate-50 border border-slate-200/80">
                <i data-lucide="pen-line" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Wakil sah adalah orang yang menandatangani kontrak atas nama penyedia,
                    dan namanya tercantum pada SSKK. Untuk penyedia perorangan, tulis
                    <strong class="text-slate-600">Penyedia</strong> pada kolom jabatan.
                </p>
            </div>
        </div>
    </section>
</form>
