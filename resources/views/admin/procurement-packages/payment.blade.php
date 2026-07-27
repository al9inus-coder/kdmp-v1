@component('layouts.kdmp')
@section('title', 'Monitoring Pembayaran')

@php
    $package = $procurementPackage->package;
    $kodeProgram = $package->program?->kode ?? '2.11.04';
    $completed = $procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED;

    $docs = [
        'bap' => ['label' => 'BAP', 'icon' => 'file-check-2'],
        'kwitansi' => ['label' => 'Kwitansi', 'icon' => 'receipt'],
        'ringkasan-kontrak' => ['label' => 'Ringkasan Kontrak', 'icon' => 'file-text'],
    ];
    if ($payment->is_non_pkp) {
        $docs['non-pkp'] = ['label' => 'Surat Non-PKP', 'icon' => 'file-badge'];
    }

    $printBase = route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.print-document', $package);
@endphp

<div class="space-y-6" x-data="{
        showUnlockModal: false,
        docType: 'bap',
        previewLoading: true,
        printBase: @js($printBase),
        loadDoc(type) {
            this.docType = type;
            this.previewLoading = true;
            this.$refs.docFrame.src = this.printBase + '?embed=1&type=' + type + '&t=' + Date.now();
        },
    }"
    x-init="loadDoc('bap')">
    <x-ui.toast />

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-blue-500"></i>
                {{ $package->id_rup ?? '-' }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
                <i data-lucide="package" class="w-3.5 h-3.5 text-blue-500"></i>
                {{ $package->nama_paket }}
            </span>
        </div>
        <a href="{{ route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.show', $package) }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Monitoring Paket
        </a>
    </div>

    {{-- Progress Workflow --}}
    <x-kabid.workflow-progress :procurement-package="$procurementPackage" />

    <div class="flex flex-col-reverse lg:flex-row gap-6 items-start">

        {{-- Kiri: pratinjau dokumen pembayaran --}}
        <div class="flex-1 w-full min-w-0 bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-4 pt-3 pb-0 bg-slate-50/50 border-b border-slate-200 flex items-end gap-1 overflow-x-auto">
                @foreach($docs as $type => $doc)
                    <button type="button" @click="loadDoc('{{ $type }}')"
                        class="flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-bold whitespace-nowrap rounded-t-xl border-b-2 transition-colors"
                        :class="docType === '{{ $type }}'
                            ? 'text-emerald-700 border-emerald-600 bg-white'
                            : 'text-slate-500 border-transparent hover:text-slate-700 hover:bg-white/60'">
                        <i data-lucide="{{ $doc['icon'] }}" class="w-3.5 h-3.5"></i>
                        {{ $doc['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i data-lucide="eye" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Mode monitoring &mdash; dokumen dirender dari data penagihan yang diinput Kabid.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="$refs.docFrame.contentWindow.print()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak PDF
                    </button>
                    <a :href="printBase + '?type=' + docType" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-sm transition-colors">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Tab Baru
                    </a>
                </div>
            </div>

            <div class="relative bg-slate-200" style="min-height: 850px;">
                <div x-show="previewLoading" x-transition.opacity
                    class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-100/80 backdrop-blur-sm">
                    <span class="relative flex w-10 h-10 mb-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-300 opacity-60"></span>
                        <span class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white shadow text-emerald-600">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </span>
                    </span>
                    <p class="text-sm font-semibold text-slate-600">Memuat dokumen pembayaran...</p>
                </div>
                <iframe x-ref="docFrame" @load="previewLoading = false"
                    class="w-full border-0 block bg-white" style="height: 850px;"></iframe>
            </div>
        </div>

        {{-- Kanan: data penagihan + kontrol kunci --}}
        <aside class="w-full lg:w-80 shrink-0 lg:sticky lg:top-20 space-y-5">

            {{-- Kontrol kunci --}}
            @if($completed)
                <div class="bg-white border border-emerald-200 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 bg-emerald-50/70 border-b border-emerald-100 flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <i data-lucide="check-circle-2" class="w-4.5 h-4.5"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Pengadaan Selesai</p>
                            <p class="text-[11px] text-emerald-700 font-semibold">Seluruh tahap telah ditutup</p>
                        </div>
                    </div>
                    <div class="p-5">
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Buka status selesai hanya jika dokumen pembayaran perlu diperbaiki &mdash;
                            paket akan <strong>kembali ke tahap Pembayaran</strong>.
                        </p>
                        <button type="button" @click="showUnlockModal = true"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl shadow-md shadow-amber-200 transition-all hover:-translate-y-0.5">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                            Buka Status Selesai
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                            <i data-lucide="hourglass" class="w-4.5 h-4.5"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Tahap Pembayaran Berjalan</p>
                            <p class="text-[11px] text-slate-400 font-semibold">Menunggu ditutup oleh Kabid</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Jika data BAST/tagihan perlu diperbaiki, buka kunci tahap Pelaksanaan &mdash;
                        paket akan mundur satu tahap.
                    </p>
                    <button type="button" @click="showUnlockModal = true"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl shadow-md shadow-amber-200 transition-all hover:-translate-y-0.5">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                        Buka Kunci Pelaksanaan
                    </button>
                </div>
            @endif

            {{-- Data penagihan --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-xs uppercase tracking-widest">
                        <i data-lucide="file-invoice" class="w-3.5 h-3.5 text-blue-500"></i>
                        Data Penagihan
                    </h3>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Tagihan</p>
                        <p class="font-extrabold text-emerald-600 text-lg mt-0.5">Rp {{ number_format((float) $process->nilai_kontrak, 0, ',', '.') }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penyedia</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $process->nama_penyedia ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $process->nama_bank }} &bull; {{ $process->nomor_rekening }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">BAST</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $payment->nomor_bast ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $payment->tanggal_bast?->locale('id')->translatedFormat('d F Y') ?? '-' }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Invoice</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $payment->nomor_invoice ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $payment->tanggal_invoice?->locale('id')->translatedFormat('d F Y') ?? '-' }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">BAP</p>
                        <p class="text-[11px] font-bold text-slate-700 font-mono mt-0.5 break-all">{{ $payment->nomor_bap ?? '-' }}/BAP/{{ $kodeProgram }}/PERKIMPLH-C</p>
                        <p class="text-[11px] text-slate-400">{{ $payment->tanggal_bap?->locale('id')->translatedFormat('d F Y') ?? '-' }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kwitansi</p>
                        <p class="text-[11px] font-bold text-slate-700 font-mono mt-0.5 break-all">{{ $payment->nomor_kwitansi ?? '-' }}/KWT/{{ $kodeProgram }}/PERKIMPLH-C</p>
                        <p class="text-[11px] text-slate-400">{{ $payment->tanggal_kwitansi?->locale('id')->translatedFormat('d F Y') ?? '-' }}</p>
                    </div>
                    <div class="px-4 py-2.5 flex items-center justify-between gap-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Non-PKP</p>
                        @if($payment->is_non_pkp)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Dilampirkan</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Tidak / PKP</span>
                        @endif
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PPTK</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $payment->nama_pptk ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400">NIP {{ $payment->nip_pptk ?? '-' }} &bull; {{ $payment->pangkat_golongan_pptk ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    {{-- Modal konfirmasi buka kunci --}}
    <div x-show="showUnlockModal" style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @keydown.escape.window="showUnlockModal = false">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showUnlockModal = false"></div>

        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden text-center"
            x-transition:enter="transition ease-out duration-200 delay-75"
            x-transition:enter-start="opacity-0 scale-90 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            <div class="pt-8 px-6">
                <div class="relative w-16 h-16 mx-auto mb-4">
                    <span class="absolute inset-0 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 rotate-6 opacity-20"></span>
                    <span class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-amber-200">
                        <i data-lucide="key-round" class="w-7 h-7"></i>
                    </span>
                </div>

                <h3 class="text-lg font-extrabold text-slate-800">{{ $completed ? 'Buka Status Selesai?' : 'Buka Kunci Pelaksanaan?' }}</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Paket <strong>{{ Str::limit($package->nama_paket, 40) }}</strong>
                    {{ $completed
                        ? 'akan kembali ke tahap Pembayaran dan Kabid dapat memeriksa dokumen pembayaran lagi.'
                        : 'akan kembali ke tahap Pelaksanaan Kontrak dan Kabid dapat memperbaiki data BAST/tagihan.' }}
                </p>
                <p class="text-xs text-slate-400 mt-2 flex items-center justify-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    Indikator progres akan mundur ke {{ $completed ? 'Tahap 4' : 'Tahap 3' }}.
                </p>
            </div>

            <div class="p-5 mt-3 flex items-stretch gap-2">
                <button type="button" @click="showUnlockModal = false"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap shrink-0">
                    Batal
                </button>
                <form method="POST"
                    action="{{ $completed ? route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.unlock-payment', $package) : route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.unlock-execution', $package) }}"
                    class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full h-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl shadow-md shadow-amber-200 transition-all whitespace-nowrap">
                        <i data-lucide="key-round" class="w-4 h-4 shrink-0"></i>
                        Ya, Buka Kunci
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcomponent
