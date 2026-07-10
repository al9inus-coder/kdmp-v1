@component('layouts.kdmp')
    @section('title', 'Detail SPPD')

    @php
        $TO = \App\Models\TravelOrder::class;
        $isLuarDaerah = in_array(strtolower($travelOrder->tipe_perjalanan), ['luar daerah', 'luar_daerah'], true);
        $days = $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1;
        $nights = max(0, $days - 1);
        $meta = $travelOrder->statusMeta();
        $editable = $travelOrder->isEditableBySubmitter();
        $isSubmitted = $travelOrder->status === $TO::STATUS_SUBMITTED;
        $isApproved = $travelOrder->status === $TO::STATUS_APPROVED;
        $isRejected = $travelOrder->status === $TO::STATUS_REJECTED;
        $isRevision = $travelOrder->status === $TO::STATUS_REVISION;

        $spjStatus = $travelOrder->spjStatus();
        $spjMeta = $travelOrder->spjStatusMeta();
        $spjEditable = $travelOrder->isSpjEditable();
        $spjSubmitted = $spjStatus === $TO::SPJ_SUBMITTED;
        $spjApproved = $spjStatus === $TO::SPJ_APPROVED;
        $spjRevision = $spjStatus === $TO::SPJ_REVISION;

        // Fase pelaporan mengikuti stepper: SPPD disetujui dan pelaksanaan selesai
        // (H+1 tanggal kembali), atau SPJ memang sudah pernah berjalan.
        $today = \Illuminate\Support\Carbon::today();
        $tripDone = $isApproved && $travelOrder->tanggal_kembali
            && $today->gt($travelOrder->tanggal_kembali->copy()->startOfDay());
        $reporting = $isApproved && ($tripDone || $spjStatus !== $TO::SPJ_DRAFT || $travelOrder->spj_status !== null);

        $money = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

        // Komponen biaya per pelaksana (fase pelaporan): koefisien tetap (boleh 0,
        // mis. penginapan 0 malam saat perjalanan 1 hari), harga satuan bisa diedit
        // (default tarif standar), jumlah = koef x harga. 'std' = harga SBU per satuan.
        $alpineRows = [];
        if ($reporting) {
            $mk = function ($label, $koef, $satuan, $stored, $estAmt, $sbuRate = null, $riil = false) use ($travelOrder) {
                $koef = max(0, $koef);
                $divisor = $koef > 0 ? $koef : 1;
                $hasSaved = $travelOrder->spj_status !== null;
                $amount = (int) ($hasSaved ? $stored : ($estAmt ?? 0));
                return [
                    'label' => $label, 'koef' => $koef, 'satuan' => $satuan,
                    'rate' => $koef > 0 ? $amount / $divisor : 0,
                    'std'  => $sbuRate ?? (($estAmt ?? 0) / $divisor),
                    'riil' => (bool) $riil,
                ];
            };

            $spjTable = $travelOrder->personnels->mapWithKeys(function ($p) use ($estimates, $days, $nights, $isLuarDaerah, $mk) {
                $est = $estimates[$p->id] ?? [];
                $n = (int) ($est['nights'] ?? $nights);
                $comp = [];
                $comp['uang_harian'] = $mk('Uang harian', $days, 'hari', $p->uang_harian, $est['uang_harian'] ?? 0, $est['base_uang_harian'] ?? null);
                $comp['biaya_transport'] = $mk('Transport', 1, 'kali', $p->biaya_transport, $est['biaya_transport'] ?? 0, null, $p->transport_riil);
                if ($isLuarDaerah) {
                    $comp['biaya_taksi'] = $mk('Taksi', 1, 'kali', $p->biaya_taksi ?? 0, $est['biaya_taksi'] ?? 0, null, $p->taksi_riil);
                }
                $comp['biaya_penginapan'] = $mk('Penginapan', $n, 'malam', $p->biaya_penginapan, $est['biaya_penginapan'] ?? 0, $est['base_penginapan'] ?? null, $p->penginapan_riil);
                $comp['biaya_representasi'] = $mk('Representasi', $days, 'hari', $p->biaya_representasi, $est['biaya_representasi'] ?? 0, $est['base_representasi'] ?? null);
                return [$p->id => $comp];
            });

            $alpineRows = $spjTable->map(fn ($comp) => collect($comp)->map(fn ($c) => [
                'koef' => $c['koef'], 'rate' => $c['rate'], 'std' => $c['std'], 'riil' => $c['riil'],
            ]))->toArray();

            // Laporan perjalanan dinas: prefill dari data tersimpan / format nomor surat instansi.
            $report = $travelOrder->report;
            $tahunSurat = ($travelOrder->tanggal_surat ? \Illuminate\Support\Carbon::parse($travelOrder->tanggal_surat) : now())->format('Y');
            $tglSuratDefault = $travelOrder->tanggal_surat ? \Illuminate\Support\Carbon::parse($travelOrder->tanggal_surat)->format('Y-m-d') : null;
            $ketuaPersonnel = $travelOrder->personnels->sortBy('urutan')->first();
            $laporanFields = [
                'nomor_surat_tugas' => $report?->nomor_surat_tugas ?: '000.1.2.3/....../PERKIMPLH-C',
                'tanggal_surat_tugas' => $report?->tanggal_surat_tugas?->format('Y-m-d') ?: $tglSuratDefault,
                'nomor_spd' => $report?->nomor_spd ?: ($ketuaPersonnel?->nomor_sppd ?: '000.1.2.3/....../PERKIMPLH-C/' . $tahunSurat),
                'tanggal_spd' => $report?->tanggal_spd?->format('Y-m-d') ?: $tglSuratDefault,
                'prompt_latar_belakang' => $report?->prompt_latar_belakang ?? '',
                'prompt_kegiatan' => $report?->prompt_kegiatan ?? '',
                'prompt_hasil' => $report?->prompt_hasil ?? '',
                'prompt_kesimpulan' => $report?->prompt_kesimpulan ?? '',
                'prompt_penutup' => $report?->prompt_penutup ?? '',
                'hasil_latar_belakang' => $report?->hasil_latar_belakang ?? '',
                'hasil_kegiatan' => $report?->hasil_kegiatan ?? '',
                'hasil_dicapai' => $report?->hasil_dicapai ?? '',
                'hasil_kesimpulan' => $report?->hasil_kesimpulan ?? '',
                'hasil_penutup' => $report?->hasil_penutup ?? '',
            ];

            // System prompt AI: template aktif dari DB, default dari config (untuk reset).
            $laporanPromptDefault = config('ai_prompts.travel_report');
            $laporanPromptActive = \App\Models\AiPrompt::where('code', 'travel_report')
                ->where('is_active', true)
                ->value('prompt') ?: $laporanPromptDefault;
        }
    @endphp

    <div class="space-y-6"
        x-data="{
            tab: '{{ $reporting ? 'laporan' : 'informasi' }}',
            rows: @js($alpineRows),
            compTotal(pid, key) { const c = this.rows[pid][key]; return (Number(c.koef)||0) * (Number(c.rate)||0); },
            rowTotal(pid) { let s = 0; for (const k in this.rows[pid]) s += this.compTotal(pid, k); return s; },
            get grandTotal() { let s = 0; for (const pid in this.rows) s += this.rowTotal(pid); return s; },
            rp(n) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n) || 0); },
            fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n)) || 0); },
            parseNum(s) { return Number(String(s).replace(/[^\d]/g, '')) || 0; },
        }">
        <x-ui.toast />

        {{-- Header: badge ringkas + kembali ke daftar --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-500"></i>
                    {{ $travelOrder->tempat_tujuan }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                    <i data-lucide="calendar-days" class="w-3.5 h-3.5 text-emerald-500"></i>
                    {{ $days }} hari
                </span>
                @if ($reporting)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border {{ $spjMeta['badge'] }}">
                        <i data-lucide="{{ $spjMeta['icon'] }}" class="w-3.5 h-3.5"></i>
                        SPJ: {{ $spjMeta['label'] }}
                    </span>
                @endif
            </div>
            <a href="{{ route('staf.sppd.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Daftar SPPD
            </a>
        </div>

        {{-- Stepper lifecycle --}}
        <x-travel.stepper :travel-order="$travelOrder" :package="$package" :active="$reporting ? 'spj' : 'sppd'" />

        {{-- 2 kolom: workspace (kiri) | dokumen & aksi (kanan) --}}
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">

            {{-- ============ WORKSPACE (KIRI) ============ --}}
            <div class="space-y-4">

                {{-- Tab: Informasi / Laporan / Biaya --}}
                <div class="inline-flex items-center p-1 bg-slate-100 border border-slate-200 rounded-xl">
                    <button type="button" @click="tab = 'informasi'"
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg transition-all"
                        :class="tab === 'informasi' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i> Informasi Perjalanan
                    </button>
                    @if ($reporting)
                        <button type="button" @click="tab = 'laporan'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg transition-all"
                            :class="tab === 'laporan' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                            <i data-lucide="file-text" class="w-4 h-4"></i> Laporan
                        </button>
                        <button type="button" @click="tab = 'biaya'"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg transition-all"
                            :class="tab === 'biaya' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                            <i data-lucide="wallet" class="w-4 h-4"></i> Biaya
                        </button>
                    @else
                        <button type="button" disabled title="Tersedia saat tahap pelaporan"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg text-slate-300 cursor-not-allowed">
                            <i data-lucide="file-text" class="w-4 h-4"></i> Laporan
                            <i data-lucide="lock" class="w-3 h-3"></i>
                        </button>
                        <button type="button" disabled title="Tersedia saat tahap pelaporan"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-semibold rounded-lg text-slate-300 cursor-not-allowed">
                            <i data-lucide="wallet" class="w-4 h-4"></i> Biaya
                            <i data-lucide="lock" class="w-3 h-3"></i>
                        </button>
                    @endif
                </div>

                {{-- ===== Panel Informasi Perjalanan ===== --}}
                <div x-show="tab === 'informasi'" @if($reporting) style="display:none" @endif>
                    @include('staf.travel-orders.partials.detail')
                </div>

                @if ($reporting)
                    {{-- ===== Panel Laporan ===== --}}
                    <div x-show="tab === 'laporan'" style="display:none" x-data="laporanForm()">
                        <form method="POST" action="{{ route('staf.packages.travel-orders.laporan.store', [$package, $travelOrder]) }}">
                        @csrf
                        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                                        <i data-lucide="sparkles" class="w-5 h-5 text-indigo-500"></i>
                                        AI Laporan Generator
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">Berikan instruksi (prompt) di sebelah kiri.</p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <button type="button" @click="showPromptModal = true"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors shadow-sm">
                                        <i data-lucide="settings-2" class="w-4 h-4"></i>
                                        Edit Prompt
                                    </button>
                                    <button type="button" @click="generate()" :disabled="loading"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 disabled:cursor-wait rounded-xl transition-colors shadow-sm shadow-indigo-200">
                                        <span x-show="!loading" class="inline-flex items-center gap-2">
                                            <i data-lucide="bot" class="w-4 h-4"></i> Generate Laporan
                                        </span>
                                        <span x-show="loading" style="display:none" class="inline-flex items-center gap-2">
                                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Menyusun laporan&hellip;
                                        </span>
                                    </button>
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-slate-900 border border-slate-900 hover:bg-black rounded-xl transition-colors shadow-sm">
                                        <i data-lucide="save" class="w-4 h-4"></i>
                                        Save
                                    </button>
                                </div>
                            </div>
                            
                            {{-- Baris Input Manual Dasar --}}
                            <div class="p-6 bg-slate-50/80 border-b border-slate-200 shadow-sm relative overflow-hidden">
                                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwwLDAsMC4wNSkiLz48L3N2Zz4=')] [mask-image:linear-gradient(to_bottom,white,transparent)] opacity-50 pointer-events-none"></div>
                                <div class="relative">
                                    <div class="flex flex-col lg:flex-row divide-y lg:divide-y-0 lg:divide-x divide-slate-200/80 -mx-6 lg:mx-0">
                                        <div class="grid grid-cols-2 gap-4 flex-1 px-6 lg:pr-8 lg:pl-0 pb-6 lg:pb-0">
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Nomor Surat Tugas</label>
                                                <input type="text" name="nomor_surat_tugas" x-model="f.nomor_surat_tugas" class="w-full text-sm rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white shadow-sm" placeholder="000.1.2.3/....../PERKIMPLH-C">
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Tanggal Surat Tugas</label>
                                                <input type="date" name="tanggal_surat_tugas" x-model="f.tanggal_surat_tugas" class="w-full text-sm rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white shadow-sm">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 flex-1 px-6 lg:pl-8 lg:pr-0 pt-6 lg:pt-0">
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Nomor SPD</label>
                                                <input type="text" name="nomor_spd" x-model="f.nomor_spd" class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white shadow-sm" placeholder="000.1.2.3/....../PERKIMPLH-C/{{ $tahunSurat }}">
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Tanggal SPD</label>
                                                <input type="date" name="tanggal_spd" x-model="f.tanggal_spd" class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white shadow-sm">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-slate-100 relative">
                                {{-- Kolom Kiri: Input Prompt --}}
                                <div class="p-6 bg-slate-50/30 space-y-5">
                                    <div class="flex items-center gap-2 mb-2 pt-1">
                                        <div class="w-6 h-6 rounded-md bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0"><i data-lucide="terminal" class="w-3.5 h-3.5"></i></div>
                                        <h3 class="font-bold text-slate-700">Instruksi AI (Prompt)</h3>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600">Prompt Latar Belakang</label>
                                        <textarea rows="4" name="prompt_latar_belakang" x-model="f.prompt_latar_belakang" class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white resize-none shadow-sm" placeholder="Tuliskan latar belakang perjalanan dinas..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600">Prompt Kegiatan yang Dilaksanakan <span class="text-rose-500">*</span></label>
                                        <textarea rows="5" name="prompt_kegiatan" x-model="f.prompt_kegiatan" class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white resize-none shadow-sm" placeholder="Poin-poin kegiatan: siapa yang ditemui, apa yang dikerjakan..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600">Prompt Hasil yang Dicapai <span class="text-rose-500">*</span></label>
                                        <textarea rows="6" name="prompt_hasil" x-model="f.prompt_hasil" class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white resize-none shadow-sm" placeholder="Apa saja temuan, hasil rapat, atau tindak lanjut..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600">Prompt Kesimpulan & Saran</label>
                                        <textarea rows="4" name="prompt_kesimpulan" x-model="f.prompt_kesimpulan" class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white resize-none shadow-sm" placeholder="Tuliskan saran atau rekomendasi..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-xs font-semibold text-slate-600">Prompt Penutup</label>
                                        <textarea rows="3" name="prompt_penutup" x-model="f.prompt_penutup" class="w-full text-sm rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-white resize-none shadow-sm" placeholder="Tuliskan harapan ke depan atau ucapan terima kasih..."></textarea>
                                    </div>
                                </div>

                                {{-- Kolom Kanan: Hasil Generate --}}
                                <div class="p-6 bg-white space-y-5">
                                    <div class="flex items-center justify-between gap-2 mb-2 pt-1">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i data-lucide="file-check" class="w-3.5 h-3.5"></i></div>
                                            <h3 class="font-bold text-slate-700">Hasil Laporan</h3>
                                        </div>
                                    </div>
                                    
                                    <p class="text-xs text-slate-500 mb-4 hidden">Teks di bawah ini bisa Anda edit secara manual apabila hasil ketikan AI dirasa kurang pas.</p>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Latar Belakang</label>
                                        <textarea rows="4" name="hasil_latar_belakang" x-model="f.hasil_latar_belakang" :class="typingKey === 'hasil_latar_belakang' ? 'ring-2 ring-indigo-300 border-indigo-300' : ''" class="w-full text-sm rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/30 leading-relaxed shadow-sm" placeholder="Menunggu hasil generate AI..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kegiatan yang Dilaksanakan</label>
                                        <textarea rows="5" name="hasil_kegiatan" x-model="f.hasil_kegiatan" :class="typingKey === 'hasil_kegiatan' ? 'ring-2 ring-indigo-300 border-indigo-300' : ''" class="w-full text-sm rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/30 leading-relaxed shadow-sm" placeholder="Menunggu hasil generate AI..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hasil yang Dicapai</label>
                                        <textarea rows="6" name="hasil_dicapai" x-model="f.hasil_dicapai" :class="typingKey === 'hasil_dicapai' ? 'ring-2 ring-indigo-300 border-indigo-300' : ''" class="w-full text-sm rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/30 leading-relaxed shadow-sm" placeholder="Menunggu hasil generate AI..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kesimpulan & Saran</label>
                                        <textarea rows="4" name="hasil_kesimpulan" x-model="f.hasil_kesimpulan" :class="typingKey === 'hasil_kesimpulan' ? 'ring-2 ring-indigo-300 border-indigo-300' : ''" class="w-full text-sm rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/30 leading-relaxed shadow-sm" placeholder="Menunggu hasil generate AI..."></textarea>
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Penutup</label>
                                        <textarea rows="3" name="hasil_penutup" x-model="f.hasil_penutup" :class="typingKey === 'hasil_penutup' ? 'ring-2 ring-indigo-300 border-indigo-300' : ''" class="w-full text-sm rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/30 leading-relaxed shadow-sm" placeholder="Menunggu hasil generate AI..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- Modal proses AI --}}
                        <div x-show="showModal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                            @keydown.escape.window="if (error) showModal = false">
                            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden p-8 text-center"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-90"
                                x-transition:enter-end="opacity-100 scale-100">

                                {{-- Ikon bot berdenyut --}}
                                <div class="relative w-16 h-16 mx-auto mb-5">
                                    <span x-show="!error && !success" class="absolute inset-0 rounded-2xl bg-indigo-400 animate-ping opacity-30"></span>
                                    <span class="relative w-16 h-16 rounded-2xl flex items-center justify-center text-white transition-colors"
                                        :class="error ? 'bg-rose-500' : (success ? 'bg-emerald-500' : 'bg-indigo-600')">
                                        <i data-lucide="bot" class="w-8 h-8" x-show="!error && !success"></i>
                                        <i data-lucide="check" class="w-8 h-8" x-show="success" style="display:none"></i>
                                        <i data-lucide="alert-triangle" class="w-8 h-8" x-show="error" style="display:none"></i>
                                    </span>
                                </div>

                                <h3 class="font-black text-slate-900 text-lg"
                                    x-text="error ? 'Generate Gagal' : (success ? 'Laporan Selesai!' : 'AI Sedang Menyusun Laporan')"></h3>
                                <p class="text-xs text-slate-500 mt-1 mb-5"
                                    x-text="error ? '' : (success ? 'Menuliskan hasil ke kolom laporanâ€¦' : 'Mohon tunggu, biasanya 10â€“30 detik.')"></p>

                                {{-- Tahapan proses --}}
                                <div x-show="!error" class="text-left space-y-2.5 mb-5">
                                    @foreach (['Membaca data perjalanan dinas', 'Menganalisis poin kegiatan & hasil', 'Menyusun narasi laporan', 'Merapikan bahasa formal'] as $i => $step)
                                        <div class="flex items-center gap-2.5 text-xs font-semibold transition-colors"
                                            :class="stepIdx > {{ $i }} ? 'text-slate-700' : (stepIdx === {{ $i }} ? 'text-indigo-700' : 'text-slate-300')">
                                            <span class="w-4 h-4 flex items-center justify-center shrink-0">
                                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500" x-show="stepIdx > {{ $i }}" style="display:none"></i>
                                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-indigo-500" x-show="stepIdx === {{ $i }} && !success"></i>
                                                <i data-lucide="circle" class="w-4 h-4" x-show="stepIdx < {{ $i }}" style="display:none"></i>
                                            </span>
                                            {{ $step }}
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Progress bar --}}
                                <div x-show="!error" class="h-1.5 rounded-full bg-slate-100 overflow-hidden mb-1">
                                    <div class="h-full rounded-full transition-all duration-700 ease-out"
                                        :class="success ? 'bg-emerald-500' : 'bg-indigo-500'"
                                        :style="'width:' + progress + '%'"></div>
                                </div>

                                {{-- Error + aksi --}}
                                <div x-show="error" style="display:none" class="space-y-4">
                                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-semibold text-rose-700 text-left" x-text="error"></div>
                                    <div class="flex justify-center gap-2">
                                        <button type="button" @click="showModal = false"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm">
                                            Tutup
                                        </button>
                                        <button type="button" @click="generate()"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm">
                                            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Coba Lagi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </form>

                        {{-- Modal Edit Prompt AI --}}
                        <div x-show="showPromptModal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                            @keydown.escape.window="showPromptModal = false">
                            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showPromptModal = false"></div>
                            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100">
                                <form method="POST" action="{{ route('staf.packages.travel-orders.laporan.prompt', [$package, $travelOrder]) }}">
                                    @csrf
                                    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                                        <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                            <i data-lucide="settings-2" class="w-5 h-5"></i>
                                        </span>
                                        <div>
                                            <h3 class="font-black text-slate-900">Edit Prompt AI</h3>
                                            <p class="text-xs text-slate-500 mt-0.5">Aturan main AI saat menyusun laporan (system prompt).</p>
                                        </div>
                                    </div>

                                    <div class="p-6 space-y-3">
                                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-xs font-semibold text-blue-700 flex items-start gap-2">
                                            <i data-lucide="info" class="w-3.5 h-3.5 mt-0.5 shrink-0"></i>
                                            Data perjalanan (tujuan, tanggal, pelaksana, dsb.) dan poin-poin dari form tetap dikirim otomatis.
                                            Prompt ini hanya mengatur persona, gaya bahasa, dan aturan penulisan AI.
                                        </div>
                                        <textarea name="prompt" rows="14" x-model="promptText" required
                                            class="w-full text-xs font-mono leading-relaxed rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50/50"></textarea>
                                    </div>

                                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between gap-2">
                                        <button type="button" @click="promptText = promptDefault"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded-xl hover:bg-amber-100 transition-colors">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> Reset ke Default
                                        </button>
                                        <div class="flex gap-2">
                                            <button type="button" @click="showPromptModal = false"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                                                Batal
                                            </button>
                                            <button type="submit"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                                                <i data-lucide="save" class="w-4 h-4"></i> Simpan Prompt
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <script>
                            function laporanForm() {
                                return {
                                    loading: false,
                                    showModal: false,
                                    error: '',
                                    success: false,
                                    stepIdx: 0,
                                    stepTimer: null,
                                    typingKey: '',
                                    showPromptModal: false,
                                    promptText: {!! json_encode($laporanPromptActive) !!},
                                    promptDefault: {!! json_encode($laporanPromptDefault) !!},
                                    f: {!! json_encode($laporanFields) !!},

                                    get progress() {
                                        return this.success ? 100 : Math.min(90, (this.stepIdx + 1) * 22);
                                    },

                                    async generate() {
                                        if (this.loading) return;
                                        this.loading = true;
                                        this.error = '';
                                        this.success = false;
                                        this.stepIdx = 0;
                                        this.showModal = true;

                                        // Tahapan visual berjalan selama menunggu respons AI.
                                        this.stepTimer = setInterval(() => {
                                            if (this.stepIdx < 3) this.stepIdx++;
                                        }, 2500);

                                        try {
                                            const res = await fetch('{{ route('staf.packages.travel-orders.laporan.generate', [$package, $travelOrder]) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                },
                                                body: JSON.stringify(this.f),
                                            });
                                            const data = await res.json();
                                            if (!res.ok) {
                                                this.error = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Gagal generate laporan.');
                                                return;
                                            }

                                            clearInterval(this.stepTimer);
                                            this.stepIdx = 4;
                                            this.success = true;
                                            await new Promise(r => setTimeout(r, 1000));
                                            this.showModal = false;

                                            await this.typeResults(data.hasil);
                                        } catch (e) {
                                            this.error = 'Gagal terhubung ke server.';
                                        } finally {
                                            clearInterval(this.stepTimer);
                                            this.loading = false;
                                        }
                                    },

                                    // Efek mengetik: hasil AI mengisi kolom kanan bagian demi bagian.
                                    async typeResults(hasil) {
                                        const keys = ['hasil_latar_belakang', 'hasil_kegiatan', 'hasil_dicapai', 'hasil_kesimpulan', 'hasil_penutup'];
                                        for (const k of keys) {
                                            const text = hasil[k] || '';
                                            this.typingKey = k;
                                            this.f[k] = '';
                                            const el = document.querySelector(`textarea[name="${k}"]`);
                                            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                            for (let i = 0; i < text.length; i += 6) {
                                                this.f[k] = text.slice(0, i + 6);
                                                await new Promise(r => setTimeout(r, 8));
                                            }
                                            this.f[k] = text;
                                        }
                                        this.typingKey = '';
                                    },
                                };
                            }
                        </script>
                    </div>

                    {{-- ===== Panel Biaya ===== --}}
                    <div x-show="tab === 'biaya'" style="display:none">
                        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                                    <i data-lucide="wallet" class="w-5 h-5 text-indigo-500"></i>
                                    Input Biaya SPD
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">Harga satuan default mengikuti tarif standar â€” jumlah = koefisien &times; harga satuan.</p>
                            </div>

                            <div class="p-5 space-y-4">
                                @error('personnels')
                                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{{ $message }}</div>
                                @enderror

                                @unless($spjEditable)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-500 flex items-center gap-2">
                                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                        {{ $spjApproved ? 'SPJ sudah disetujui dan terkunci.' : 'SPJ sedang diajukan â€” tarik pengajuan untuk mengubah.' }}
                                    </div>
                                @endunless

                                <form id="spj-form" method="POST" action="{{ route('staf.packages.travel-orders.spj.store', [$package, $travelOrder]) }}">
                                    @csrf
                                    <input type="hidden" name="then_submit" id="spj-then-submit" value="0">

                                    <fieldset @unless($spjEditable) disabled @endunless class="space-y-4 {{ $spjEditable ? '' : 'opacity-90' }}">
                                        @foreach($travelOrder->personnels as $i => $personnel)
                                            @php $comp = $spjTable[$personnel->id]; @endphp
                                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-3">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $i === 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500' }}">
                                                            <i data-lucide="user" class="w-4 h-4"></i>
                                                        </span>
                                                        <div class="min-w-0">
                                                            <p class="font-bold {{ $i === 0 ? 'text-amber-700' : 'text-slate-900' }} leading-snug truncate">{{ $personnel->employee?->nama ?? 'Pegawai' }}</p>
                                                            <p class="text-xs text-slate-400">{{ $personnel->employee?->jabatan ?? '-' }} &bull; Gol. {{ $personnel->employee?->golongan ?? '-' }} &bull; {{ ucfirst($personnel->jenis_kendaraan ?? 'mobil') }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm">
                                                        <thead>
                                                            <tr class="text-[11px] text-slate-400 uppercase font-semibold bg-white border-b border-slate-100">
                                                                <th class="text-left font-semibold px-4 py-2.5">Nama Biaya</th>
                                                                <th class="text-center font-semibold px-3 py-2.5 w-24">Koefisien</th>
                                                                <th class="text-right font-semibold px-3 py-2.5 w-32">Harga SBU</th>
                                                                <th class="text-right font-semibold px-3 py-2.5 w-40">Harga Satuan (Rp)</th>
                                                                <th class="text-right font-semibold px-4 py-2.5 w-36">Jumlah</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-slate-50">
                                                            @foreach($comp as $key => $c)
                                                                <tr>
                                                                    <td class="px-4 py-2.5 font-semibold text-slate-700">
                                                                        {{ $c['label'] }}
                                                                        @if($key === 'biaya_representasi')
                                                                            <span class="block text-[10px] font-normal text-slate-400">Khusus Eselon II</span>
                                                                        @endif
                                                                        @if(in_array($key, ['biaya_transport', 'biaya_taksi'], true))
                                                                            <label class="mt-1 flex items-center gap-1.5 text-[10px] font-semibold text-slate-500 cursor-pointer">
                                                                                <input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                                                    x-model="rows[{{ $personnel->id }}]['{{ $key }}'].riil">
                                                                                Tanpa bukti &mdash; masuk pengeluaran riil
                                                                            </label>
                                                                        @elseif($key === 'biaya_penginapan' && $c['koef'] > 0)
                                                                            <label class="mt-1 flex items-center gap-1.5 text-[10px] font-semibold text-slate-500 cursor-pointer">
                                                                                <input type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                                                                    x-model="rows[{{ $personnel->id }}]['{{ $key }}'].riil"
                                                                                    @change="rows[{{ $personnel->id }}]['{{ $key }}'].rate = $event.target.checked
                                                                                        ? Math.round(rows[{{ $personnel->id }}]['{{ $key }}'].std * 0.3)
                                                                                        : rows[{{ $personnel->id }}]['{{ $key }}'].std">
                                                                                Tidak menginap di hotel &mdash; dibayar 30% tarif SBU
                                                                            </label>
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-3 py-2.5 text-center text-slate-500 whitespace-nowrap">
                                                                        {{ rtrim(rtrim(number_format($c['koef'], 2, ',', '.'), '0'), ',') }} {{ $c['satuan'] }}
                                                                    </td>
                                                                    <td class="px-3 py-2.5 text-right text-slate-500 whitespace-nowrap">
                                                                        {{ $money($c['std']) }}
                                                                    </td>
                                                                    <td class="px-3 py-2.5">
                                                                        <input type="text" inputmode="numeric" autocomplete="off"
                                                                            @if($c['koef'] <= 0) disabled @endif
                                                                            @if($key === 'biaya_penginapan' && $c['koef'] > 0) :disabled="rows[{{ $personnel->id }}]['{{ $key }}'].riil" @endif
                                                                            :value="fmt(rows[{{ $personnel->id }}]['{{ $key }}'].rate)"
                                                                            @input="rows[{{ $personnel->id }}]['{{ $key }}'].rate = parseNum($event.target.value); $event.target.value = fmt(rows[{{ $personnel->id }}]['{{ $key }}'].rate)"
                                                                            class="w-full text-right rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-500">
                                                                        <input type="hidden" name="personnels[{{ $personnel->id }}][{{ $key }}]"
                                                                            :value="Math.round(compTotal({{ $personnel->id }}, '{{ $key }}'))">
                                                                        @if(in_array($key, ['biaya_transport', 'biaya_taksi', 'biaya_penginapan'], true))
                                                                            <input type="hidden" name="personnels[{{ $personnel->id }}][{{ str_replace('biaya_', '', $key) }}_riil]"
                                                                                :value="rows[{{ $personnel->id }}]['{{ $key }}'].riil ? 1 : 0">
                                                                        @endif
                                                                    </td>
                                                                    <td class="px-4 py-2.5 text-right font-bold text-slate-800 whitespace-nowrap" x-text="rp(compTotal({{ $personnel->id }}, '{{ $key }}'))"></td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="border-t border-slate-100 bg-slate-50/50">
                                                                <td colspan="4" class="px-4 py-2.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wide">Total</td>
                                                                <td class="px-4 py-2.5 text-right font-extrabold text-emerald-700 whitespace-nowrap" x-text="rp(rowTotal({{ $personnel->id }}))"></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>

                                                @unless($isLuarDaerah)
                                                    <input type="hidden" name="personnels[{{ $personnel->id }}][biaya_taksi]" value="0">
                                                @endunless
                                            </div>
                                        @endforeach
                                    </fieldset>
                                </form>
                            </div>
                        </section>
                    </div>
                @endif
            </div>

            {{-- ============ DOKUMEN & AKSI (KANAN) ============ --}}
            <aside class="space-y-4 lg:sticky lg:top-20">

                {{-- Dokumen SPPD --}}
                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                        <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                            <i data-lucide="download" class="w-4 h-4 text-indigo-500"></i>
                            Dokumen SPPD
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">Dokumen terbuka mengikuti tahapan perjalanan dinas.</p>
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

                        {{-- Laporan: terbuka setelah laporan digenerate/tersimpan --}}
                        @if ($travelOrder->report)
                            <button type="button"
                                onclick="window.open('{{ route('packages.travel-orders.print-laporan', [$package, $travelOrder]) }}', 'cetak-laporan', 'width=900,height=700')"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-700">
                                <span class="inline-flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-indigo-500"></i> Laporan</span>
                                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                            </button>
                        @else
                            <button type="button" disabled title="Tersedia setelah laporan digenerate"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-400 cursor-not-allowed">
                                <span class="inline-flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4"></i> Laporan</span>
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </button>
                        @endif

                        {{-- Kwitansi & Pengeluaran Riil: terbuka setelah SPJ disetujui --}}
                        @if ($spjApproved)
                            <button type="button"
                                onclick="window.open('{{ route('packages.travel-orders.print-kuitansi', [$package, $travelOrder]) }}', 'cetak-kwitansi', 'width=900,height=700')"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-700">
                                <span class="inline-flex items-center gap-2"><i data-lucide="receipt" class="w-4 h-4 text-rose-500"></i> Kwitansi</span>
                                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                            </button>
                            <button type="button"
                                onclick="window.open('{{ route('packages.travel-orders.print-pengeluaran-riil', [$package, $travelOrder]) }}', 'cetak-pengeluaran-riil', 'width=900,height=700')"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-sm font-bold text-slate-700">
                                <span class="inline-flex items-center gap-2"><i data-lucide="receipt-text" class="w-4 h-4 text-violet-500"></i> Pengeluaran Riil</span>
                                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                            </button>
                        @else
                            <button type="button" disabled title="Tersedia setelah SPJ disetujui"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-400 cursor-not-allowed">
                                <span class="inline-flex items-center gap-2"><i data-lucide="receipt" class="w-4 h-4"></i> Kwitansi</span>
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </button>
                            <button type="button" disabled title="Tersedia setelah SPJ disetujui"
                                class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-bold text-slate-400 cursor-not-allowed">
                                <span class="inline-flex items-center gap-2"><i data-lucide="receipt-text" class="w-4 h-4"></i> Pengeluaran Riil</span>
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>
                </section>

                @if ($reporting)
                    {{-- Pengajuan SPJ --}}
                    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5 space-y-4">
                        @if($spjApproved)
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">SPJ disetujui</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $travelOrder->spjReviewer ? 'Oleh ' . $travelOrder->spjReviewer->name . '. ' : '' }}Kwitansi siap dicetak di panel Dokumen.</p>
                                </div>
                            </div>
                        @elseif($spjSubmitted)
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0"><i data-lucide="send" class="w-4 h-4"></i></span>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">Menunggu persetujuan</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Diajukan {{ $travelOrder->spj_submitted_at?->locale('id')->diffForHumans() }}. Bisa ditarik selama belum ditinjau.</p>
                                </div>
                            </div>
                        @elseif($spjRevision)
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0"><i data-lucide="file-warning" class="w-4 h-4"></i></span>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">Perlu revisi</p>
                                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $travelOrder->spj_catatan ?: 'Perbaiki biaya rampung lalu ajukan ulang.' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0"><i data-lucide="receipt-text" class="w-4 h-4"></i></span>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">Input biaya rampung</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Isi realisasi biaya di tab Biaya, lalu simpan atau ajukan.</p>
                                </div>
                            </div>
                        @endif

                        {{-- Total Realisasi --}}
                        <div class="border-t border-slate-100 pt-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Realisasi</p>
                            <p class="text-2xl font-extrabold text-emerald-700" x-text="rp(grandTotal)"></p>
                        </div>

                        {{-- Aksi --}}
                        @if($spjEditable)
                            <div class="space-y-2">
                                <button type="submit" form="spj-form"
                                    onclick="document.getElementById('spj-then-submit').value='1'"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition-colors">
                                    <i data-lucide="send" class="w-4 h-4"></i> Ajukan
                                </button>
                                <button type="submit" form="spj-form"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Draf
                                </button>
                            </div>
                        @elseif($spjSubmitted)
                            <form method="POST" action="{{ route('staf.packages.travel-orders.spj.withdraw', [$package, $travelOrder]) }}"
                                onsubmit="return confirm('Tarik kembali pengajuan SPJ ke Draf?');">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-colors">
                                    <i data-lucide="undo-2" class="w-4 h-4"></i> Tarik Pengajuan
                                </button>
                            </form>
                        @elseif($spjApproved)
                            <span class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <i data-lucide="lock" class="w-4 h-4"></i> Terkunci
                            </span>
                        @endif
                    </section>
                @else
                    {{-- Kartu aksi SPPD: status + tombol sesuai tahap pengajuan/pelaksanaan --}}
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
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
                                <div>
                                    <p class="font-bold text-slate-800 text-sm">SPPD disetujui</p>
                                    <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                                        Tahap pelaporan (Laporan &amp; Biaya) terbuka otomatis sehari setelah tanggal kembali
                                        ({{ $travelOrder->tanggal_kembali->copy()->addDay()->locale('id')->translatedFormat('d M Y') }}).
                                    </p>
                                </div>
                            </div>
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
                                    <p class="font-bold text-slate-800 text-sm">Draf â€” belum diajukan</p>
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
                @endif
            </aside>
        </div>
    </div>
@endcomponent
