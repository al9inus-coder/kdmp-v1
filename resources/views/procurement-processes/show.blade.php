@component('layouts.kdmp')
    @section('title', 'Pemilihan Penyedia')

    <div class="space-y-6" x-data="{ step: {{ (int) session('panel', 1) }} }">
        <x-ui.toast />

        {{-- Identitas Paket --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
                    <i data-lucide="hash" class="w-3.5 h-3.5 text-blue-500"></i>
                    {{ $procurementPackage->package->id_rup ?? '-' }}
                </span>
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg">
                    <i data-lucide="package" class="w-3.5 h-3.5 text-blue-500"></i>
                    {{ $procurementPackage->package->nama_paket }}
                </span>
            </div>
            <a href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.show', $procurementPackage->package) }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke Persiapan
            </a>
        </div>

        {{-- Progress Workflow Pengadaan --}}
        <x-kabid.workflow-progress :procurement-package="$procurementPackage" />

        {{-- Konten (kiri) + Alur Pemilihan (kanan) --}}
        <div class="flex flex-col-reverse lg:flex-row gap-6 items-start">

            {{-- Kolom kiri: panel konten per langkah --}}
            <div class="flex-1 w-full min-w-0">
                <div
                    class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden flex flex-col min-h-[480px]">
                    @php
                        // Pemilihan terkunci setelah paket masuk tahap Pelaksanaan, atau jika user bukan Kabid (read-only).
                        $locked = in_array($procurementPackage->workflow_status, [
                            \App\Models\ProcurementPackage::WORKFLOW_EXECUTION,
                            \App\Models\ProcurementPackage::WORKFLOW_PAYMENT_PROCESS,
                            \App\Models\ProcurementPackage::WORKFLOW_COMPLETED,
                        ]) || !auth()->user()->hasRole('Kabid');

                        $panels = [
                            1 => [
                                'title' => 'Surat Pesanan',
                                'icon' => 'file-text',
                                'desc' => 'Nomor, tanggal, nilai kontrak, dan jadwal pengiriman.',
                                'view' => 'kabid.procurement-processes.panels.surat-pesanan',
                                'save_form' => 'form-surat-pesanan',
                                'save_label' => 'Simpan',
                                'lock_wrap' => true,
                            ],
                            2 => [
                                'title' => 'Data Penyedia',
                                'icon' => 'store',
                                'desc' => 'Identitas, NPWP, wakil sah, dan rekening penyedia.',
                                'view' => 'kabid.procurement-processes.panels.data-penyedia',
                                'save_form' => 'form-data-penyedia',
                                'save_label' => 'Simpan',
                                'lock_wrap' => true,
                            ],
                            3 => [
                                'title' => 'Dokumen SSUK & SSKK',
                                'icon' => 'file-check-2',
                                'desc' => 'Pratinjau dan cetak dokumen syarat umum & khusus kontrak.',
                                'view' => 'kabid.procurement-processes.panels.dokumen',
                            ],
                            4 => [
                                'title' => 'Selesaikan',
                                'icon' => 'flag',
                                'desc' => 'Periksa kelengkapan lalu mulai tahap pelaksanaan kontrak.',
                                'view' => 'kabid.procurement-processes.panels.selesaikan',
                            ],
                        ];
                    @endphp

                    {{-- Banner kunci --}}
                    @if ($locked)
                        <div class="px-6 py-3 bg-amber-50/80 border-b border-amber-100 flex items-center gap-2.5">
                            <i data-lucide="lock" class="w-4 h-4 text-amber-500 shrink-0"></i>
                            <p class="text-xs text-amber-800 font-semibold">
                                @if(!auth()->user()->hasRole('Kabid'))
                                    Anda sedang melihat draf pemilihan penyedia (mode pratinjau).
                                @else
                                    Pemilihan penyedia sudah diselesaikan — data terkunci (hanya bisa dilihat). Hubungi Admin untuk membuka kembali.
                                @endif
                            </p>
                        </div>
                    @endif

                    <div class="flex-1">
                        @foreach ($panels as $no => $panel)
                            <div x-show="step === {{ $no }}"
                                @if ($no > 1) style="display: none;" @endif>
                                <div
                                    class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                            <i data-lucide="{{ $panel['icon'] }}" class="w-5 h-5 text-slate-400"></i>
                                            {{ $panel['title'] }}
                                        </h2>
                                        <p class="text-sm text-slate-500 mt-1">{{ $panel['desc'] }}</p>
                                    </div>
                                    @if (isset($panel['save_form']) && !$locked)
                                        <button type="submit" form="{{ $panel['save_form'] }}"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-slate-900 hover:bg-black rounded-lg shadow-sm shadow-slate-300 transition-colors shrink-0">
                                            <i data-lucide="save" class="w-4 h-4"></i>
                                            {{ $panel['save_label'] }}
                                        </button>
                                    @endif
                                </div>
                                <div class="p-6">
                                    @if ($locked && ($panel['lock_wrap'] ?? false))
                                        <div class="pointer-events-none opacity-70 select-none" aria-disabled="true">
                                            @include($panel['view'])
                                        </div>
                                    @else
                                        @include($panel['view'])
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Footer navigasi antar panel --}}
                    <div class="border-t border-slate-200 p-4 bg-slate-50/50 flex justify-between items-center mt-auto">
                        <button type="button" @click="step > 1 ? step-- : null" :disabled="step === 1"
                            class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i> Sebelumnya
                        </button>
                        <button type="button" @click="step < 4 ? step++ : null" :disabled="step === 4"
                            class="inline-flex items-center gap-1 px-4 py-2 text-sm font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            Selanjutnya <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: Alur Pemilihan (tanpa background, sticky) --}}
            <aside class="w-full lg:w-60 shrink-0 lg:sticky lg:top-20">
                <x-kabid.selection-nav :procurement-package="$procurementPackage" :process="$process" />
            </aside>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:initialized', () => {
            Alpine.effect(() => {
                setTimeout(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 50);
            });
        });
    </script>
@endcomponent
