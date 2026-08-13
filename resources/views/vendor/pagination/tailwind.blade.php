{{--
    View pagination milik aplikasi.

    Dibuat sendiri, bukan memakai bawaan Laravel, karena dua sebab: bawaannya
    berteks Inggris ("Showing 1 to 15 of 40 results") sementara aplikasi ini
    berbahasa Indonesia dan tidak punya berkas terjemahan, dan paletnya
    gray/indigo sedangkan aplikasi memakai slate/emerald.

    Ditaruh di resources/views/vendor/ supaya kelas Tailwind-nya ikut terpindai
    saat build — berbeda dengan view di dalam vendor/ yang jalur pemindaiannya
    mudah terlewat.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman"
         class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        {{-- Keterangan jumlah. Di ponsel diringkas jadi nomor halaman saja
             supaya tidak berebut ruang dengan tombolnya. --}}
        <p class="text-xs sm:text-sm text-slate-500 order-2 sm:order-1">
            <span class="hidden sm:inline">
                Menampilkan
                <span class="font-semibold text-slate-700">{{ $paginator->firstItem() }}</span>–<span
                    class="font-semibold text-slate-700">{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-semibold text-slate-700">{{ $paginator->total() }}</span> data
            </span>
            <span class="sm:hidden">
                Halaman <span class="font-semibold text-slate-700">{{ $paginator->currentPage() }}</span>
                dari <span class="font-semibold text-slate-700">{{ $paginator->lastPage() }}</span>
            </span>
        </p>

        <div class="flex items-center gap-1 order-1 sm:order-2">
            {{-- Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true"
                      class="inline-flex items-center gap-1 h-9 pl-2 pr-3 rounded-xl text-sm font-semibold text-slate-300 cursor-not-allowed select-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"
                   class="inline-flex items-center gap-1 h-9 pl-2 pr-3 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Nomor halaman disembunyikan di ponsel; di sana keterangan
                 "Halaman 2 dari 3" di atas sudah mewakili. --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="w-9 h-9 inline-flex items-center justify-center text-sm text-slate-400 select-none">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-sm font-bold bg-emerald-600 text-white shadow-sm shadow-emerald-200">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="Halaman {{ $page }}"
                                   class="w-9 h-9 inline-flex items-center justify-center rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"
                   class="inline-flex items-center gap-1 h-9 pl-3 pr-2 rounded-xl text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span aria-disabled="true"
                      class="inline-flex items-center gap-1 h-9 pl-3 pr-2 rounded-xl text-sm font-semibold text-slate-300 cursor-not-allowed select-none">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
