@php
    $package = $procurementPackage->package;
    $pagu = (float) ($package->pagu ?? 0);
    $nilai = (float) ($process->nilai_kontrak ?? 0);

    $checks = [
        ['step' => 1, 'icon' => 'file-text', 'label' => 'Surat Pesanan',
         'desc' => filled($process->nomor_surat_pesanan) ? 'No. ' . $process->nomor_surat_pesanan : 'Nomor, tanggal, dan nilai kontrak.',
         'done' => filled($process->nomor_surat_pesanan) && filled($process->tanggal_surat_pesanan) && filled($process->tanggal_barang_diterima) && $nilai > 0],
        // Rekening & NPWP tidak diperiksa di sini — keduanya milik tahap Pembayaran.
        ['step' => 2, 'icon' => 'store', 'label' => 'Data Penyedia',
         'desc' => $process->nama_penyedia ?? 'Identitas dan wakil sah penyedia.',
         'done' => filled($process->nama_penyedia) && filled($process->alamat_penyedia)
                   && filled($process->nama_pic) && filled($process->jabatan_pic)],
    ];
    $doneCount = collect($checks)->where('done', true)->count();
    $allDone = $doneCount === count($checks);

    $locked = in_array($procurementPackage->workflow_status, [
        \App\Models\ProcurementPackage::WORKFLOW_EXECUTION,
        \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
        \App\Models\ProcurementPackage::WORKFLOW_COMPLETED,
    ]);

    $durasi = ($process->tanggal_surat_pesanan && $process->tanggal_barang_diterima)
        ? $process->tanggal_surat_pesanan->diffInDays($process->tanggal_barang_diterima) + 1
        : null;
@endphp

@if($locked)
    {{-- ================= PEMILIHAN SELESAI ================= --}}
    <div class="flex flex-col items-center text-center py-6">
        <div class="relative w-20 h-20 mb-5">
            <span class="absolute inset-0 rounded-full bg-emerald-400 opacity-20 animate-ping" style="animation-duration: 2.5s;"></span>
            <span class="relative w-20 h-20 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                <i data-lucide="check" class="w-10 h-10 stroke-[3]"></i>
            </span>
        </div>
        <h3 class="text-xl font-extrabold text-slate-800">Pemilihan Penyedia Selesai</h3>
        <p class="text-sm text-slate-500 mt-2 max-w-md leading-relaxed">
            Kontrak dengan <strong class="text-slate-700">{{ $process->nama_penyedia ?? '-' }}</strong>
            senilai <strong class="text-emerald-600">Rp {{ number_format($nilai, 0, ',', '.') }}</strong>
            telah masuk tahap <strong class="text-slate-700">{{ \App\Models\ProcurementPackage::getWorkflowStatuses()[$procurementPackage->workflow_status] ?? 'Pelaksanaan' }}</strong>.
        </p>
        <p class="text-xs text-slate-400 mt-3 flex items-center gap-1.5">
            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
            Data pemilihan terkunci. Hubungi Admin jika perlu membuka kembali.
        </p>

        <div class="flex flex-wrap items-center justify-center gap-3 mt-6">
            <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.procurement-process.print-document', $package) }}" target="_blank"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl shadow-sm transition-colors">
                <i data-lucide="printer" class="w-4 h-4"></i>
                Cetak SSUK &amp; SSKK
            </a>
            <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.execution.show', $package) }}"
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all hover:-translate-y-0.5">
                Lanjut ke Pelaksanaan Kontrak
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
@else
    <div x-data="{ showConfirmMulai: false }">

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Kolom kiri: checklist --}}
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-blue-500"></i> Daftar Periksa
                    </h3>
                    <span class="text-[11px] font-bold {{ $allDone ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $doneCount }}/{{ count($checks) }} lengkap
                    </span>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100 overflow-hidden">
                    @foreach($checks as $check)
                        <button type="button" @click="step = {{ $check['step'] }}"
                            class="w-full flex items-center gap-3 px-4 py-3.5 text-left transition-colors hover:bg-slate-50 group">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0
                                {{ $check['done']
                                    ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm shadow-emerald-200'
                                    : 'bg-amber-50 border-2 border-amber-300 text-amber-500' }}">
                                @if($check['done'])
                                    <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                                @else
                                    <i data-lucide="{{ $check['icon'] }}" class="w-3.5 h-3.5"></i>
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-bold {{ $check['done'] ? 'text-slate-700' : 'text-amber-700' }}">
                                    {{ $check['label'] }}
                                </span>
                                <span class="block text-xs {{ $check['done'] ? 'text-slate-400' : 'text-amber-500 font-semibold' }} truncate">
                                    {{ $check['done'] ? $check['desc'] : 'Belum lengkap — klik untuk melengkapi.' }}
                                </span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors shrink-0"></i>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Kolom kanan: ringkasan kontrak --}}
            <div>
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-blue-500"></i> Ringkasan Kontrak
                </h3>
                <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                        <div class="p-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Kontrak</p>
                            <p class="font-bold mt-0.5 {{ $nilai > $pagu ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $nilai > 0 ? 'Rp ' . number_format($nilai, 0, ',', '.') : '-' }}
                            </p>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pagu Anggaran</p>
                            <p class="font-bold text-slate-800 mt-0.5">Rp {{ number_format($pagu, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 divide-x divide-slate-100 border-b border-slate-100">
                        <div class="p-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Surat Pesanan</p>
                            <p class="font-semibold text-slate-700 mt-0.5 text-sm">
                                {{ $process->tanggal_surat_pesanan?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                            </p>
                        </div>
                        <div class="p-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Barang Diterima</p>
                            <p class="font-semibold text-slate-700 mt-0.5 text-sm">
                                {{ $process->tanggal_barang_diterima?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                                @if($durasi)
                                    <span class="text-[10px] font-bold text-amber-600">({{ $durasi }} hari)</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penyedia</p>
                        <p class="font-semibold text-slate-700 mt-0.5 text-sm">{{ $process->nama_penyedia ?? '-' }}</p>
                        {{-- Rekening baru diisi di tahap Pembayaran, jadi di sini
                             yang relevan adalah wakil sahnya. --}}
                        @if($process->nama_pic)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $process->nama_pic }} &bull; {{ $process->jabatan_pic }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- CTA Mulai Pelaksanaan --}}
        <div class="mt-6 rounded-2xl border p-5 flex flex-col sm:flex-row items-center justify-between gap-4
            {{ $allDone ? 'border-emerald-200 bg-gradient-to-r from-emerald-50/80 to-teal-50/50' : 'border-slate-200 bg-slate-50/60' }}">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                    {{ $allDone ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-400' }}">
                    <i data-lucide="{{ $allDone ? 'flag' : 'flag-off' }}" class="w-5 h-5"></i>
                </span>
                <div>
                    <p class="font-bold text-slate-800 text-sm">
                        {{ $allDone ? 'Data lengkap — siap memulai pelaksanaan kontrak.' : 'Pelaksanaan belum bisa dimulai.' }}
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($allDone)
                            Setelah dimulai, data pemilihan <strong>terkunci</strong> dan paket masuk tahap Pelaksanaan Kontrak.
                        @else
                            Lengkapi {{ count($checks) - $doneCount }} bagian yang masih ditandai kuning.
                        @endif
                    </p>
                </div>
            </div>
            <button type="button" @click="showConfirmMulai = true" @if(!$allDone) disabled @endif
                class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white rounded-xl shadow-md transition-all shrink-0
                    {{ $allDone
                        ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-200 hover:-translate-y-0.5'
                        : 'bg-slate-300 cursor-not-allowed shadow-none' }}">
                <i data-lucide="truck" class="w-4 h-4"></i>
                Mulai Pelaksanaan Kontrak
            </button>
        </div>

        {{-- Modal konfirmasi --}}
        <div x-show="showConfirmMulai" style="display: none;"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @keydown.escape.window="showConfirmMulai = false">

            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmMulai = false"></div>

            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden text-center"
                x-transition:enter="transition ease-out duration-200 delay-75"
                x-transition:enter-start="opacity-0 scale-90 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0">

                <div class="pt-8 px-6">
                    <div class="relative w-16 h-16 mx-auto mb-4">
                        <span class="absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 rotate-6 opacity-20"></span>
                        <span class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                            <i data-lucide="truck" class="w-7 h-7"></i>
                        </span>
                        <span class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-amber-400 border-2 border-white flex items-center justify-center text-white">
                            <i data-lucide="lock" class="w-3 h-3"></i>
                        </span>
                    </div>

                    <h3 class="text-lg font-extrabold text-slate-800">Mulai Pelaksanaan Kontrak?</h3>
                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                        Kontrak dengan <strong>{{ Str::limit($process->nama_penyedia ?? '-', 30) }}</strong> akan masuk tahap
                        <strong>Pelaksanaan</strong> dan data pemilihan
                        <span class="font-bold text-rose-600">dikunci</span>.
                    </p>
                    <p class="text-xs text-slate-400 mt-2 flex items-center justify-center gap-1.5">
                        <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
                        Hanya Admin yang dapat membuka kunci kembali.
                    </p>
                </div>

                <div class="p-5 mt-3 flex items-stretch gap-2">
                    <button type="button" @click="showConfirmMulai = false"
                        class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap shrink-0">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('kabid.procurement-packages.procurement-process.start-execution', $package) }}" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full h-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all whitespace-nowrap">
                            <i data-lucide="truck" class="w-4 h-4 shrink-0"></i>
                            Ya, Mulai
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
