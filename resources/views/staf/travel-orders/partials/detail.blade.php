{{-- Konten informasi perjalanan dinas. Variabel diwarisi dari show.blade.php --}}

<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60">
        <h1 class="text-lg font-black text-slate-900 flex items-center gap-2">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-indigo-500"></i>
            Informasi Perjalanan Dinas
        </h1>
        <p class="mt-1 text-sm text-slate-500">{{ $package->nama_paket }}</p>
    </div>

    <div class="p-6 space-y-5">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Maksud Perjalanan Dinas</p>
            <p class="mt-1.5 text-base font-bold text-slate-900 leading-relaxed">{{ $travelOrder->maksud_perjalanan }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Tujuan Perjalanan Dinas</p>
                <p class="mt-1.5 font-bold text-slate-900">{{ $travelOrder->tempat_tujuan }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Lama Perjalanan</p>
                <p class="mt-1.5 font-bold text-slate-900">{{ $days }} hari</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Tanggal Berangkat</p>
                <p class="mt-1.5 font-bold text-slate-900">{{ $travelOrder->tanggal_berangkat->locale('id')->translatedFormat('d M Y') }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Tanggal Kembali</p>
                <p class="mt-1.5 font-bold text-slate-900">{{ $travelOrder->tanggal_kembali->locale('id')->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        <div>
            <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Dasar Pelaksanaan</p>
            <p class="mt-1.5 text-sm text-slate-700 leading-relaxed">{{ $travelOrder->dasar_pelaksanaan ?: '-' }}</p>
        </div>

        <div>
            <div class="flex items-center justify-between gap-3 mb-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Nama Pelaksana Perjalanan Dinas</p>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg bg-slate-100 text-slate-600">
                    {{ $travelOrder->personnels->count() }} orang
                </span>
            </div>
            <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 overflow-hidden">
                @foreach ($travelOrder->personnels as $i => $personnel)
                    <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white">
                        <div>
                            <p class="font-bold {{ $i === 0 ? 'text-amber-700' : 'text-slate-900' }}">{{ $personnel->employee?->nama ?? 'Pegawai' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $personnel->employee?->jabatan ?? '-' }} &bull; Gol. {{ $personnel->employee?->golongan ?? '-' }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-bold shrink-0">
                            <i data-lucide="car" class="w-3.5 h-3.5 text-slate-400"></i>
                            {{ ucfirst($personnel->jenis_kendaraan ?? 'mobil') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
