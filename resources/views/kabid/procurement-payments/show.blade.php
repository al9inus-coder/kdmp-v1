@component('layouts.kdmp')
@section('title', 'Tahap Pembayaran')

@php
    $package = $procurementPackage->package;
    $kodeProgram = $package->program?->kode ?? '2.11.04';
    $completed = $procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED;

    $docs = [
        'all' => ['label' => 'Semua Dokumen', 'icon' => 'files'],
        'bap' => ['label' => 'BAP', 'icon' => 'file-check-2'],
        'kwitansi' => ['label' => 'Kwitansi', 'icon' => 'receipt'],
        'ringkasan-kontrak' => ['label' => 'Ringkasan Kontrak', 'icon' => 'file-text'],
    ];
    if ($payment->is_non_pkp) {
        $docs['non-pkp'] = ['label' => 'Surat Non-PKP', 'icon' => 'file-badge'];
    }

    $printBase = route('procurement-payments.print-document', $package);
@endphp

<div class="space-y-6" x-data="{
        showConfirmSelesai: false,
        docType: 'all',
        previewLoading: true,
        printBase: @js($printBase),
        loadDoc(type) {
            this.docType = type;
            this.previewLoading = true;
            this.$refs.docFrame.src = this.printBase + '?embed=1&type=' + type + '&t=' + Date.now();
        },
    }"
    x-init="loadDoc('all')">
    <x-ui.toast />

    {{-- Identitas Paket --}}
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
        <a href="{{ route('kabid.procurement-packages.execution.show', $package) }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Pelaksanaan
        </a>
    </div>

    {{-- Progress Workflow Pengadaan --}}
    <x-kabid.workflow-progress :procurement-package="$procurementPackage" />

    @if($completed)
        {{-- Perayaan selesai --}}
        <div class="relative overflow-hidden bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 rounded-2xl px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg shadow-emerald-200">
            <span class="absolute w-1.5 h-1.5 bg-white/50 rounded-full top-4 left-16 kdmp-twinkle-pay"></span>
            <span class="absolute w-1 h-1 bg-white/40 rounded-full bottom-5 left-1/3 kdmp-twinkle-pay" style="animation-delay: .7s;"></span>
            <span class="absolute w-1.5 h-1.5 bg-white/50 rounded-full top-6 right-24 kdmp-twinkle-pay" style="animation-delay: 1.2s;"></span>
            <div class="relative flex items-center gap-4">
                <span class="w-12 h-12 rounded-2xl bg-white/15 border border-white/25 backdrop-blur-sm flex items-center justify-center text-white shrink-0">
                    <i data-lucide="party-popper" class="w-6 h-6"></i>
                </span>
                <div>
                    <p class="font-extrabold text-white text-lg">Seluruh Proses Pengadaan Selesai!</p>
                    <p class="text-xs text-emerald-50 mt-0.5">
                        Paket telah melewati keempat tahap. Dokumen pembayaran tetap dapat dicetak kapan saja.
                    </p>
                </div>
            </div>
            <a href="{{ route('procurement-packages.index') }}"
                class="relative inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-emerald-700 bg-white hover:bg-emerald-50 rounded-xl shadow-sm transition-colors shrink-0">
                <i data-lucide="list" class="w-4 h-4"></i>
                Daftar Paket Pengadaan
            </a>
        </div>
    @endif

    <div class="flex flex-col-reverse lg:flex-row gap-6 items-start">

        {{-- Kiri: pratinjau dokumen pembayaran --}}
        <div class="flex-1 w-full min-w-0 bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            {{-- Tab dokumen --}}
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
                    <i data-lucide="printer" class="w-3.5 h-3.5 text-emerald-500"></i>
                    Dokumen dirender dari data penagihan tersimpan.
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

        {{-- Kanan: data penagihan + aksi --}}
        <aside class="w-full lg:w-80 shrink-0 lg:sticky lg:top-20 space-y-5">

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
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Tagihan (Sesuai Kontrak)</p>
                        <p class="font-extrabold text-emerald-600 text-lg mt-0.5">Rp {{ number_format((float) $process->nilai_kontrak, 0, ',', '.') }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">BAST</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $payment->nomor_bast ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400">{{ optional($payment->tanggal_bast)->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Invoice</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">{{ $payment->nomor_invoice ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400">{{ optional($payment->tanggal_invoice)->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">BAP</p>
                        <p class="text-[11px] font-bold text-slate-700 font-mono mt-0.5 break-all">{{ $payment->nomor_bap ?? '-' }}/BAP/{{ $kodeProgram }}/PERKIMPLH-C</p>
                        <p class="text-[11px] text-slate-400">{{ optional($payment->tanggal_bap)->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kwitansi</p>
                        <p class="text-[11px] font-bold text-slate-700 font-mono mt-0.5 break-all">{{ $payment->nomor_kwitansi ?? '-' }}/KWT/{{ $kodeProgram }}/PERKIMPLH-C</p>
                        <p class="text-[11px] text-slate-400">{{ optional($payment->tanggal_kwitansi)->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="px-4 py-2.5 flex items-center justify-between gap-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Non-PKP</p>
                        @if($payment->is_non_pkp)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i data-lucide="file-badge" class="w-2.5 h-2.5"></i>
                                Dilampirkan
                            </span>
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
                @if(!$completed)
                    <div class="px-4 py-3 bg-slate-50/70 border-t border-slate-100">
                        <p class="text-[11px] text-slate-400 leading-snug">
                            <i data-lucide="info" class="w-3 h-3 inline-block -mt-0.5"></i>
                            Ada kesalahan data? Minta Admin membuka kunci tahap Pelaksanaan untuk memperbaikinya.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Aksi selesaikan --}}
            @if(!$completed)
                <div class="rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50/80 to-teal-50/50 p-5">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i data-lucide="flag" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Tutup proses pengadaan</p>
                            <p class="text-xs text-slate-500 mt-0.5 leading-snug">
                                Pastikan seluruh dokumen sudah dicetak. Setelah ditutup, paket berstatus <strong>Selesai</strong>.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showConfirmSelesai = true"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all hover:-translate-y-0.5">
                        <i data-lucide="flag" class="w-4 h-4"></i>
                        Selesaikan Pengadaan
                    </button>
                </div>
            @endif
        </aside>
    </div>

    {{-- Modal konfirmasi selesai --}}
    <div x-show="showConfirmSelesai" style="display: none;"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @keydown.escape.window="showConfirmSelesai = false">

        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showConfirmSelesai = false"></div>

        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden text-center"
            x-transition:enter="transition ease-out duration-200 delay-75"
            x-transition:enter-start="opacity-0 scale-90 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0">

            <div class="pt-8 px-6">
                <div class="relative w-16 h-16 mx-auto mb-4">
                    <span class="absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 rotate-6 opacity-20"></span>
                    <span class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200">
                        <i data-lucide="party-popper" class="w-7 h-7"></i>
                    </span>
                </div>

                <h3 class="text-lg font-extrabold text-slate-800">Selesaikan Pengadaan?</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Paket <strong>{{ Str::limit($package->nama_paket, 40) }}</strong> akan berstatus
                    <strong class="text-emerald-600">Selesai</strong> &mdash; ini langkah terakhir dari seluruh siklus pengadaan.
                </p>
                <p class="text-xs text-slate-400 mt-2 flex items-center justify-center gap-1.5">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    Pastikan semua dokumen pembayaran sudah dicetak.
                </p>
            </div>

            <div class="p-5 mt-3 flex items-stretch gap-2">
                <button type="button" @click="showConfirmSelesai = false"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap shrink-0">
                    Batal
                </button>
                <form method="POST" action="{{ route('kabid.procurement-packages.payment.complete', $package) }}" class="flex-1">
                    @csrf
                    <button type="submit"
                        class="w-full h-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all whitespace-nowrap">
                        <i data-lucide="flag" class="w-4 h-4 shrink-0"></i>
                        Ya, Selesaikan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes kdmp-twinkle-pay-kf { 0%, 100% { opacity: .2; } 50% { opacity: 1; } }
    .kdmp-twinkle-pay { animation: kdmp-twinkle-pay-kf 2s ease-in-out infinite; }
</style>
@endcomponent
