{{-- Konten detail SPPD. Variabel diwarisi dari show.blade.php --}}

<div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">
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

    <aside class="space-y-4 lg:sticky lg:top-20">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                    <i data-lucide="download" class="w-4 h-4 text-indigo-500"></i>
                    Dokumen SPPD
                </h2>
                <p class="mt-1 text-xs text-slate-500">Dokumen dapat diunduh setelah pengajuan disetujui.</p>
            </div>
            <div class="p-5 space-y-2">
                @if ($isLuarDaerah)
                    @if ($isApproved)
                        <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, 'permohonan-bupati']) }}"
                            class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-700">
                            <span class="inline-flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-blue-500"></i> Surat Permohonan</span>
                            <i data-lucide="download" class="w-4 h-4 text-slate-400"></i>
                        </a>
                    @else
                        <button type="button" disabled
                            class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-400 cursor-not-allowed">
                            <span class="inline-flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4"></i> Surat Permohonan</span>
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </button>
                    @endif
                @endif

                @if ($isApproved)
                    <a href="{{ route('packages.travel-orders.export-word', [$package, $travelOrder, $isLuarDaerah ? 'surat-tugas-bupati' : 'surat-tugas-kadis']) }}"
                        class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-700">
                        <span class="inline-flex items-center gap-2"><i data-lucide="file-output" class="w-4 h-4 text-amber-500"></i> Surat Tugas</span>
                        <i data-lucide="download" class="w-4 h-4 text-slate-400"></i>
                    </a>
                    <button type="button"
                        onclick="window.open('{{ route('packages.travel-orders.print-html', [$package, $travelOrder, 'sppd']) }}', '_blank')"
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-700">
                        <span class="inline-flex items-center gap-2"><i data-lucide="printer" class="w-4 h-4 text-emerald-500"></i> SPPD</span>
                        <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
                    </button>
                @else
                    <button type="button" disabled
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-400 cursor-not-allowed">
                        <span class="inline-flex items-center gap-2"><i data-lucide="file-output" class="w-4 h-4"></i> Surat Tugas</span>
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </button>
                    <button type="button" disabled
                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-400 cursor-not-allowed">
                        <span class="inline-flex items-center gap-2"><i data-lucide="printer" class="w-4 h-4"></i> SPPD</span>
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </button>
                @endif
            </div>
        </section>

        {{-- Kartu aksi: status SPPD + tombol yang berubah sesuai tahap --}}
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 space-y-4">
            @if ($isSubmitted)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i data-lucide="send" class="w-4 h-4"></i></span>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Menunggu persetujuan</p>
                        <p class="text-xs text-slate-500 mt-0.5">Diajukan {{ $travelOrder->submitted_at?->locale('id')->diffForHumans() }}. Bisa ditarik selama belum ditinjau.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('staf.packages.travel-orders.withdraw', [$package, $travelOrder]) }}"
                    onsubmit="return confirm('Tarik kembali pengajuan ini ke Draf?');">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                        <i data-lucide="undo-2" class="w-4 h-4"></i> Tarik Pengajuan
                    </button>
                </form>
            @elseif ($isApproved)
                @php $spjStarted = $travelOrder->spjStatus() !== \App\Models\TravelOrder::SPJ_DRAFT; @endphp
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">SPPD disetujui</p>
                        <p class="text-xs text-slate-500 mt-0.5">Lanjutkan ke pertanggungjawaban biaya (SPJ).</p>
                    </div>
                </div>
                <a href="{{ route('staf.packages.travel-orders.spj.show', [$package, $travelOrder]) }}"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-black text-white bg-slate-900 rounded-xl hover:bg-black shadow-sm">
                    <i data-lucide="receipt-text" class="w-4 h-4"></i> {{ $spjStarted ? 'Buka SPJ' : 'Buat SPJ' }}
                </a>
            @elseif ($isRevision)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i data-lucide="file-warning" class="w-4 h-4"></i></span>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Perlu revisi</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $travelOrder->catatan_review ?: 'Perbaiki data lalu ajukan ulang.' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('staf.packages.travel-orders.edit', [$package, $travelOrder]) }}"
                        class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Perbaiki
                    </a>
                    <form method="POST" action="{{ route('staf.packages.travel-orders.submit', [$package, $travelOrder]) }}"
                        onsubmit="return confirm('Ajukan ulang SPPD ini ke Pimpinan?');">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                            <i data-lucide="send" class="w-4 h-4"></i> Ajukan Ulang
                        </button>
                    </form>
                </div>
            @elseif ($isRejected)
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0"><i data-lucide="x-circle" class="w-4 h-4"></i></span>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Pengajuan ditolak</p>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $travelOrder->catatan_review ?: 'Tidak ada catatan dari Pimpinan.' }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0"><i data-lucide="file-pen" class="w-4 h-4"></i></span>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Draf — belum diajukan</p>
                        <p class="text-xs text-slate-500 mt-0.5">Periksa data lalu ajukan ke Pimpinan untuk disetujui.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('staf.packages.travel-orders.edit', [$package, $travelOrder]) }}"
                        class="inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('staf.packages.travel-orders.submit', [$package, $travelOrder]) }}"
                        onsubmit="return confirm('Ajukan SPPD ini ke Pimpinan untuk disetujui?');">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                            <i data-lucide="send" class="w-4 h-4"></i> Ajukan SPPD
                        </button>
                    </form>
                </div>
            @endif
        </section>
    </aside>
</div>
