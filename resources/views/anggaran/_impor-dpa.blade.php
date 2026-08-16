{{-- Modal unggah DPA. Sengaja sesedikit mungkin isiannya: sub kegiatan dan
     tahun sudah ditentukan halaman ini, dan tahap anggaran diambil dari
     dokumennya sendiri lalu ditawarkan saat meninjau — bukan ditebak di sini. --}}
<div x-data="{ buka: false, mengirim: false }"
    @buka-impor-dpa.window="buka = true"
    x-show="buka" style="display: none;"
    class="fixed inset-0 z-[70] flex items-center justify-center p-4">

    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="buka = false"></div>

    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden">
        <form action="{{ route('admin.anggaran.impor-dpa', $subActivity) }}"
            method="POST" enctype="multipart/form-data" @submit="mengirim = true">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahunId }}">

            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Impor DPA</h3>
                <button type="button" @click="buka = false"
                    class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3 text-xs leading-relaxed text-slate-600">
                    <p class="font-bold text-slate-700 mb-1">{{ $subActivity->kode }}</p>
                    <p>{{ $subActivity->nama }} &bull; Tahun {{ $tahun?->tahun ?? '—' }}</p>
                    <p class="mt-2 text-slate-500">
                        Terima cetakan <b>DPA</b>, <b>DPPA</b>, maupun <b>RKA</b> dari SIPD berformat PDF.
                        Berkas yang sub kegiatan atau tahun anggarannya tidak cocok akan ditolak.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Berkas PDF</label>
                    <input type="file" name="berkas" accept="application/pdf" required
                        class="w-full text-sm rounded-lg border border-slate-300 file:mr-3 file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-600">
                    @error('berkas')
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Angkanya dibaca langsung dari teks di dalam PDF, bukan dikira-kira dari gambar.
                    Sesudah dibaca, form plafon di bawah akan terisi dan Anda meninjaunya dulu —
                    tidak ada yang tersimpan sebelum Anda menekan Simpan.
                </p>
            </div>

            <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" @click="buka = false"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                <button type="submit" :disabled="mengirim"
                    class="px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm disabled:opacity-60">
                    <span x-show="!mengirim">Baca Berkas</span>
                    <span x-show="mengirim" style="display: none;">Membaca…</span>
                </button>
            </div>
        </form>
    </div>
</div>
