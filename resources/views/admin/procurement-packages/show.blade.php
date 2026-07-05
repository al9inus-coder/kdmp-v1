@component('layouts.kdmp')
@section('title', 'Monitoring Persiapan Pengadaan')

@php
    $package = $procurementPackage->package;
    $ts = $procurementPackage->technicalSpecification;
    $surat = $procurementPackage->procurementRequest;
    $refs = $procurementPackage->priceReferences;
    $items = $ts?->items ?? collect();

    $lockedSelection = in_array($procurementPackage->workflow_status, [
        \App\Models\ProcurementPackage::WORKFLOW_EXECUTION,
        \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
        \App\Models\ProcurementPackage::WORKFLOW_COMPLETED,
    ]);
    $locked = $procurementPackage->workflow_status !== \App\Models\ProcurementPackage::WORKFLOW_DRAFT;
    $statusLabel = \App\Models\ProcurementPackage::getWorkflowStatuses()[$procurementPackage->workflow_status] ?? $procurementPackage->workflow_status;

    // Kartu & modal buka kunci menyesuaikan tahap yang terkunci paling akhir
    // (buka kunci selalu memundurkan satu tahap)
    $isCompleted = $procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED;
    $lockedExecution = $procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS;

    if ($isCompleted) {
        $unlockTitle  = 'Pengadaan Selesai';
        $unlockText   = 'Seluruh proses pengadaan telah ditutup. Buka status selesai hanya jika dokumen pembayaran perlu diperbaiki — paket akan kembali ke tahap Pembayaran.';
        $unlockRoute  = route('admin.procurement-packages.unlock-payment', $package);
        $unlockButtonLabel = 'Buka Status Selesai';
        $unlockModalTitle = 'Buka Status Selesai?';
        $unlockModalText  = 'akan kembali ke tahap Pembayaran dan Kabid dapat memeriksa dokumen pembayaran lagi.';
        $unlockModalNote  = 'Indikator progres akan mundur ke Tahap 4.';
    } elseif ($lockedExecution) {
        $unlockTitle  = 'Pelaksanaan Terkunci';
        $unlockText   = 'Pekerjaan telah dinyatakan selesai dan data pelaksanaan terkunci. Buka kunci hanya jika data BAST/tagihan perlu diperbaiki — paket akan kembali ke tahap Pelaksanaan Kontrak.';
        $unlockRoute  = route('admin.procurement-packages.unlock-execution', $package);
        $unlockButtonLabel = 'Buka Kunci Pelaksanaan';
        $unlockModalTitle = 'Buka Kunci Pelaksanaan?';
        $unlockModalText  = 'akan kembali ke tahap Pelaksanaan Kontrak dan Kabid dapat mengubah data penyelesaian pekerjaan lagi.';
        $unlockModalNote  = 'Indikator progres akan mundur ke Tahap 3.';
    } elseif ($lockedSelection) {
        $unlockTitle  = 'Pemilihan Terkunci';
        $unlockText   = 'Tahap pemilihan penyedia telah diselesaikan dan datanya terkunci. Buka kunci hanya jika data surat pesanan/penyedia perlu diperbaiki — paket akan kembali ke tahap Pemilihan Penyedia.';
        $unlockRoute  = route('admin.procurement-packages.unlock-selection', $package);
        $unlockButtonLabel = 'Buka Kunci Pemilihan';
        $unlockModalTitle = 'Buka Kunci Pemilihan?';
        $unlockModalText  = 'akan kembali ke tahap Pemilihan Penyedia dan Kabid dapat mengubah data surat pesanan & penyedia lagi.';
        $unlockModalNote  = 'Indikator progres akan mundur ke Tahap 2.';
    } else {
        $unlockTitle  = 'Persiapan Terkunci';
        $unlockText   = 'Persiapan telah diselesaikan oleh Kabid dan datanya terkunci. Buka kunci hanya jika ada data persiapan yang perlu diperbaiki — paket akan kembali ke tahap Persiapan Pengadaan.';
        $unlockRoute  = route('admin.procurement-packages.unlock', $package);
        $unlockButtonLabel = 'Buka Kunci Persiapan';
        $unlockModalTitle = 'Buka Kunci Persiapan?';
        $unlockModalText  = 'akan kembali ke tahap Persiapan Pengadaan dan Kabid dapat mengubah datanya lagi.';
        $unlockModalNote  = 'Indikator progres akan mundur ke Tahap 1.';
    }

    $checks = [
        ['icon' => 'file-signature',  'label' => 'Informasi Kontrak',
         'done' => filled($procurementPackage->jenis_kontrak) && filled($procurementPackage->jangka_waktu_nilai)],
        ['icon' => 'shopping-basket', 'label' => 'Barang / Jasa (' . $items->count() . ' item)',
         'done' => $items->isNotEmpty()],
        ['icon' => 'file-text',       'label' => 'Spesifikasi Teknis',
         'done' => $ts && filled($ts->latar_belakang) && filled($ts->uraian_pekerjaan)],
        ['icon' => 'tags',            'label' => 'Referensi Harga (' . $refs->count() . ' referensi)',
         'done' => $refs->isNotEmpty()],
        ['icon' => 'mail',            'label' => 'Surat Permohonan',
         'done' => (bool) $surat],
    ];
    $doneCount = collect($checks)->where('done', true)->count();

    $dokumen = [
        ['label' => 'Spesifikasi Teknis', 'icon' => 'file-text',
         'url' => $ts ? route('technical-specifications.print', $ts) : null],
        ['label' => 'Referensi Harga', 'icon' => 'tags',
         'url' => $refs->isNotEmpty() ? route('procurement-packages.price-references.print', $package) : null],
        ['label' => 'Surat Permohonan', 'icon' => 'mail',
         'url' => $surat ? route('procurement-packages.procurement-request.print', $package) : null],
    ];
@endphp

<div class="space-y-6" x-data="{ showUnlockModal: false }">
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
        <a href="{{ route('procurement-packages.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali ke Daftar
        </a>
    </div>

    {{-- Progress Workflow --}}
    <x-kabid.workflow-progress :procurement-package="$procurementPackage" />

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

        {{-- Kolom kiri (2/3): checklist + dokumen --}}
        <div class="xl:col-span-2 space-y-6">
            {{-- Checklist persiapan --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-blue-500"></i>
                        Kelengkapan Persiapan (diisi Kabid)
                    </h3>
                    <span class="text-[11px] font-bold {{ $doneCount === count($checks) ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $doneCount }}/{{ count($checks) }} lengkap
                    </span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($checks as $check)
                        <div class="flex items-center gap-3 px-5 py-3">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center shrink-0
                                {{ $check['done']
                                    ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white'
                                    : 'bg-amber-50 border-2 border-amber-300 text-amber-500' }}">
                                @if($check['done'])
                                    <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                                @else
                                    <i data-lucide="{{ $check['icon'] }}" class="w-3 h-3"></i>
                                @endif
                            </span>
                            <span class="text-sm font-semibold {{ $check['done'] ? 'text-slate-700' : 'text-amber-700' }}">
                                {{ $check['label'] }}
                            </span>
                            <span class="ml-auto text-[10px] font-bold uppercase tracking-wide {{ $check['done'] ? 'text-emerald-500' : 'text-amber-500' }}">
                                {{ $check['done'] ? 'Lengkap' : 'Belum' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Dokumen --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm">
                        <i data-lucide="files" class="w-4 h-4 text-blue-500"></i>
                        Dokumen Persiapan
                    </h3>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach($dokumen as $doc)
                        <a href="{{ $doc['url'] ?? '#' }}" @if($doc['url']) target="_blank" @endif
                            class="flex items-center gap-2.5 px-4 py-3 rounded-xl border transition-all
                                {{ $doc['url'] ? 'bg-white border-slate-200 hover:border-emerald-300 hover:shadow-sm' : 'bg-slate-50 border-slate-100 opacity-50 cursor-not-allowed' }}">
                            <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                <i data-lucide="{{ $doc['icon'] }}" class="w-4 h-4"></i>
                            </span>
                            <span class="min-w-0">
                                <span class="block text-xs font-bold text-slate-700 truncate">{{ $doc['label'] }}</span>
                                <span class="block text-[10px] font-semibold text-slate-400 uppercase tracking-wide">
                                    {{ $doc['url'] ? 'Buka dokumen' : 'Belum ada' }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Kolom kanan (1/3): status kunci --}}
        <div class="space-y-6">
            @if($locked)
                <div class="bg-white border border-amber-200 shadow-sm rounded-2xl overflow-hidden">
                    <div class="px-5 py-4 bg-amber-50/70 border-b border-amber-100 flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                            <i data-lucide="lock" class="w-4.5 h-4.5"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">{{ $unlockTitle }}</p>
                            <p class="text-[11px] text-amber-700 font-semibold">Tahap: {{ $statusLabel }}</p>
                        </div>
                    </div>
                    <div class="p-5">
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $unlockText }}</p>
                        <button type="button" @click="showUnlockModal = true"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 rounded-xl shadow-md shadow-amber-200 transition-all hover:-translate-y-0.5">
                            <i data-lucide="key-round" class="w-4 h-4"></i>
                            {{ $unlockButtonLabel }}
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 shrink-0">
                            <i data-lucide="lock-open" class="w-4.5 h-4.5"></i>
                        </span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Tidak Terkunci</p>
                            <p class="text-[11px] text-slate-400 font-semibold">Tahap: {{ $statusLabel }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Persiapan masih berjalan dan dapat diubah oleh Kabid.
                        Kunci aktif otomatis setelah Kabid menekan <strong>Selesaikan Persiapan</strong>.
                    </p>
                </div>
            @endif

            {{-- Dokumen pembayaran (setelah pekerjaan selesai) --}}
            @if(in_array($procurementPackage->workflow_status, [
                \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
                \App\Models\ProcurementPackage::WORKFLOW_COMPLETED,
            ]) && $procurementPackage->payment)
                <a href="{{ route('admin.procurement-packages.payment', $package) }}"
                    class="flex items-center gap-3 bg-white border border-slate-200 shadow-sm rounded-2xl p-4 hover:border-emerald-300 hover:shadow-md transition-all group">
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-500 shrink-0">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">Monitoring Pembayaran</span>
                        <span class="block text-[11px] font-semibold text-slate-400">BAP, kwitansi, ringkasan kontrak &amp; kontrol kunci</span>
                    </span>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-slate-300 group-hover:text-emerald-500 transition-colors shrink-0"></i>
                </a>
            @endif

            {{-- Info pembuat --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Info Paket Pengadaan</p>
                <div class="space-y-2 text-sm">
                    <p class="flex justify-between gap-2">
                        <span class="text-slate-400">Dibuat oleh</span>
                        <span class="font-semibold text-slate-700 truncate">{{ $procurementPackage->creator->name ?? '-' }}</span>
                    </p>
                    <p class="flex justify-between gap-2">
                        <span class="text-slate-400">Pagu</span>
                        <span class="font-bold text-emerald-600">Rp {{ number_format((float) ($package->pagu ?? 0), 0, ',', '.') }}</span>
                    </p>
                    <p class="flex justify-between gap-2">
                        <span class="text-slate-400">Metode</span>
                        <span class="font-semibold text-slate-700">{{ $package->metode_pengadaan ?? '-' }}</span>
                    </p>
                </div>
            </div>
        </div>
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

                <h3 class="text-lg font-extrabold text-slate-800">{{ $unlockModalTitle }}</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                    Paket <strong>{{ Str::limit($package->nama_paket, 40) }}</strong> {{ $unlockModalText }}
                </p>
                <p class="text-xs text-slate-400 mt-2 flex items-center justify-center gap-1.5">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    {{ $unlockModalNote }}
                </p>
            </div>

            <div class="p-5 mt-3 flex items-stretch gap-2">
                <button type="button" @click="showUnlockModal = false"
                    class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors whitespace-nowrap shrink-0">
                    Batal
                </button>
                <form method="POST" action="{{ $unlockRoute }}" class="flex-1">
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
