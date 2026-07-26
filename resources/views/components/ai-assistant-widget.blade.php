{{--
    Asisten di layar besar: tombol melayang di kanan bawah yang membuka
    panel percakapan.

    Isi panelnya adalah komponen <x-ai.chat> yang sama persis dengan halaman
    /asisten — supaya tidak ada dua implementasi chat yang harus dirawat
    berbarengan. Di layar kecil widget ini disembunyikan, karena di sana
    percakapan sudah menjadi halaman penuh.
--}}
<div x-data="{ terbuka: false }" @buka-asisten.window="terbuka = true" class="hidden md:block">

    {{-- Tombol melayang — bentuk yang sama persis dengan yang di ponsel,
         hanya perilakunya berbeda: di sini membuka panel, di sana pindah
         ke halaman /asisten. --}}
    <x-ai.tombol-asisten x-show="!terbuka" @click="terbuka = true" />

    {{-- Panel percakapan --}}
    <div x-show="terbuka" style="display:none"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 w-[420px] h-[640px] rounded-3xl border border-slate-200 bg-white shadow-2xl flex flex-col overflow-hidden">

        <header class="h-14 shrink-0 flex items-center justify-between px-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 via-violet-500 to-rose-500 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-white"></i>
                </div>
                <span class="text-sm font-semibold text-slate-700">Asisten KDMP</span>
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('asisten') }}"
                   class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"
                   title="Buka layar penuh">
                    <i data-lucide="maximize-2" class="w-4 h-4"></i>
                </a>
                <button type="button" @click="terbuka = false"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition-colors"
                        title="Tutup">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
        </header>

        <div class="flex-1 min-h-0">
            <x-ai.chat mode="panel" />
        </div>
    </div>
</div>
