{{-- Konten informasi perjalanan dinas. Variabel diwarisi dari show.blade.php --}}

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden font-sans text-slate-700">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
        <h2 class="text-lg font-black text-slate-900 flex items-center gap-3">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-indigo-500"></i>
            Informasi Perjalanan Dinas
        </h2>
        <span class="px-4 py-1.5 text-sm font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full shadow-sm">
            {{ $travelOrder->tipe_perjalanan === 'dalam_daerah' ? 'Dalam daerah' : 'Luar daerah' }}
        </span>
    </div>

    <div class="p-6 space-y-6">
        {{-- Tempat Tujuan --}}
        <div class="bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 relative shadow-sm">
            <div class="text-center -mb-1 relative z-20">
                <h2 class="text-xl font-bold text-slate-900 flex items-center justify-center gap-2">
                    <i data-lucide="map-pin" class="w-5 h-5 text-rose-500"></i>
                    {{ $travelOrder->tempat_tujuan }}
                </h2>
            </div>
            
            <div class="flex items-center justify-between relative px-2">
                {{-- Berangkat --}}
                <div class="text-center md:text-left z-10 w-24">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Berangkat</p>
                    <p class="text-lg font-bold text-slate-900 leading-none">{{ $travelOrder->tanggal_berangkat->format('d M') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $travelOrder->tanggal_berangkat->format('Y') }}</p>
                </div>
                
                {{-- Progress Line --}}
                <div class="flex-1 absolute left-[80px] right-[80px] md:left-[100px] md:right-[100px] top-1/2 -translate-y-1/2">
                    <div class="h-0.5 bg-slate-200 w-full"></div>
                    <div class="absolute left-0 w-2.5 h-2.5 rounded-full bg-emerald-500 top-1/2 -translate-y-1/2 shadow-[0_0_6px_rgba(16,185,129,0.4)]"></div>
                    <div class="absolute right-0 w-2.5 h-2.5 rounded-full bg-indigo-500 top-1/2 -translate-y-1/2 shadow-[0_0_6px_rgba(99,102,241,0.4)]"></div>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 bg-slate-100 px-3">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            {{ $days }} hari &bull; {{ max(0, $days - 1) }} malam
                        </span>
                    </div>
                </div>

                {{-- Kembali --}}
                <div class="text-center md:text-right z-10 w-24">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-0.5">Kembali</p>
                    <p class="text-lg font-bold text-slate-900 leading-none">{{ $travelOrder->tanggal_kembali->format('d M') }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $travelOrder->tanggal_kembali->format('Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Sub Kegiatan --}}
        <div class="bg-white border border-slate-100 rounded-xl p-4 flex gap-4 shadow-sm">
            <div class="pt-1">
                <i data-lucide="layout-grid" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Sub Kegiatan</p>
                <p class="text-sm font-bold text-slate-900">{{ $package->subActivity->nama ?? 'Nama Sub Kegiatan' }}</p>
            </div>
        </div>

        {{-- Maksud & Dasar --}}
        <div class="space-y-4 px-1 pt-1">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Maksud Perjalanan</p>
                <p class="text-sm text-slate-800 leading-relaxed font-medium">{{ $travelOrder->maksud_perjalanan }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1">Dasar Pelaksanaan</p>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $travelOrder->dasar_pelaksanaan ?: '-' }}</p>
            </div>
        </div>

        {{-- Pelaksana --}}
        <div class="border-t border-slate-100 pt-5 mt-5">
            <div class="flex items-center justify-between mb-3 px-1">
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Pelaksana</p>
                <span class="px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 bg-slate-100 rounded-full border border-slate-200">
                    {{ $travelOrder->personnels->count() }} orang
                </span>
            </div>
            
            <div class="space-y-2">
                @foreach ($travelOrder->personnels as $i => $personnel)
                    @php
                        $initials = collect(explode(' ', $personnel->employee?->nama ?? 'Pegawai'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                    @endphp
                    <div class="bg-white border border-slate-100 rounded-lg px-4 py-2 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs bg-indigo-50 text-indigo-600 shrink-0 border border-indigo-100">
                                {{ strtoupper($initials) }}
                            </div>
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $personnel->employee?->nama ?? 'Pegawai' }}</p>
                            </div>
                        </div>
                        
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 border border-slate-200 text-slate-600 text-[11px] font-semibold shrink-0">
                            <i data-lucide="{{ strtolower($personnel->jenis_kendaraan ?? '') === 'mobil' ? 'car' : (strtolower($personnel->jenis_kendaraan ?? '') === 'motor' ? 'bike' : 'car') }}" class="w-3 h-3 text-slate-400"></i>
                            {{ ucfirst($personnel->jenis_kendaraan ?? 'Mobil') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
