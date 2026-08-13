@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'bg-white rounded-[24px] shadow-sm shadow-slate-200/50 border border-slate-200/60 overflow-hidden flex flex-col w-full']) }}>
    <!-- Page Header & Toolbar Area -->
    {{-- Bantalan dirapatkan di ponsel: 32px di layar 393px memakan 64px lebar
         dan mendorong isi utama turun tanpa memberi apa pun. --}}
    <div class="px-4 pt-5 pb-4 sm:px-8 sm:pt-8 sm:pb-6 border-b border-slate-100">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
                @if($description)
                    {{-- Disembunyikan di ponsel: keterangan halaman adalah instruksi
                         sekali baca, tapi di layar sempit ia membayar dua sampai tiga
                         baris setiap kali halaman dibuka. --}}
                    <p class="hidden sm:block text-sm text-slate-500 mt-1.5">{{ $description }}</p>
                @endif
            </div>
            
            @if(isset($actions))
                <div class="flex items-center gap-3 shrink-0 mt-2 md:mt-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
        
        <!-- Toolbar -->
        @if(isset($toolbar))
            <div class="mt-4 pt-4 sm:mt-6 sm:pt-6 border-t border-slate-100/50">
                {{ $toolbar }}
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <div class="p-4 sm:p-8 flex-1 bg-white">
        {{ $slot }}
    </div>

    <!-- Optional Footer -->
    @if(isset($footer))
        <div class="px-4 sm:px-8 py-4 bg-slate-50 border-t border-slate-100 mt-auto">
            {{ $footer }}
        </div>
    @endif
</div>
