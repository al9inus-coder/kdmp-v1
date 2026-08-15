@component('layouts.kdmp')
@section('title', 'Tahap Pembayaran')

@php
    $package = $procurementPackage->package;
    $kodeProgram = $package->program?->kode ?? '2.11.04';
    $completed = $procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_COMPLETED;

    // Skema bawaan dari rekening belanja, dipakai sebagai keterangan pada
    // pilihan "Ikuti rekening belanja".
    $skemaRekening = $package->account?->skema_pajak ?: \App\Services\Pajak\SkemaPajak::STANDAR;

    // Kualifikasi diambil dari tarif PPh Final 4(2) yang terdaftar, bukan
    // daftar tetap — supaya menambah kualifikasi cukup lewat /admin/pajak.
    $kualifikasiKonstruksi = \App\Models\TarifPajak::aktif()
        ->where('jenis', \App\Models\TarifPajak::PPH4_2_KONSTRUKSI)
        ->orderBy('persen')
        ->pluck('kunci')
        ->filter()
        ->values();

    // Jenis PPh bawaan menurut jenis pengadaan paket. Dipakai dua hal:
    // keterangan pada pilihan "Ikuti jenis pengadaan", dan penanda bila
    // pembayaran ini memakai jenis yang berbeda dari paketnya.
    $pilihanJenisPph = \App\Services\Pajak\PajakPengadaan::pilihanJenisPph();
    $jenisPphTurunan = \App\Services\Pajak\PajakPengadaan::jenisPphTurunan($procurementPackage);
    $jenisPphTersimpan = $payment->jenis_pph;
    $jenisPphBeda = $jenisPphTersimpan
        && isset($pilihanJenisPph[$jenisPphTersimpan])
        && $jenisPphTersimpan !== $jenisPphTurunan;

    $rutePratinjauPajak = route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.pratinjau-pajak', $package);

    // Daftar pajak yang berlaku: yang tersimpan bila sudah pernah disimpan,
    // usulan sistem bila belum. Inilah yang diisikan ke form.
    $pilihanJenisPajak = \App\Services\Pajak\PajakPengadaan::pilihanJenis();
    $pilihanDasarPajak = \App\Services\Pajak\PajakPengadaan::pilihanDasar();
    $barisPajakAwal = collect(old('pajak') ?: array_map(fn ($b) => [
        'jenis' => $b['jenis'],
        'kunci' => $b['kunci'],
        'persen' => rtrim(rtrim(number_format((float) $b['persen'], 2, '.', ''), '0'), '.'),
        'dasar' => $b['dasar'],
    ], \App\Services\Pajak\PajakPengadaan::hitung($procurementPackage)['baris']))->values();

    // Nilai kontrak yang dipakai dokumen bisa tertinggal dari yang berlaku
    // sekarang bila ada adendum atau koreksi setelah pembayaran disimpan.
    $nilaiKontrakKini = (float) ($procurementPackage->procurementProcess->nilai_kontrak ?? 0);
    $nilaiKontrakBeku = $payment->nilai_kontrak_fix !== null ? (float) $payment->nilai_kontrak_fix : null;
    $nilaiKontrakBergeser = $nilaiKontrakBeku !== null
        && abs($nilaiKontrakBeku - $nilaiKontrakKini) >= 0.01;

    $docs = [
        'bap' => ['label' => 'BAP', 'icon' => 'file-check-2'],
        'kwitansi' => ['label' => 'Kwitansi', 'icon' => 'receipt'],
        'ringkasan-kontrak' => ['label' => 'Ringkasan Kontrak', 'icon' => 'file-text'],
    ];
    if ($payment->is_non_pkp) {
        $docs['non-pkp'] = ['label' => 'Surat Non-PKP', 'icon' => 'file-badge'];
    }

    $printBase = route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.payment.print-document', $package);

    // Dokumen baru boleh dicetak setelah seluruh datanya terisi — berkas
    // resmi tidak boleh keluar dengan bagian kosong.
    $siapCetak = ($kurang ?? []) === [];
    $bolehUbah = ! $completed;

    // Kunjungan pertama disambut form; sesudah disimpan, tampilan biasa.
    $bukaForm = $bolehUbah && ! ($pernahDiisi ?? true);
@endphp

<div class="space-y-6" x-data="{
        showConfirmSelesai: false,
        formTerbuka: {{ $bukaForm ? 'true' : 'false' }},
        docType: 'bap',
        previewLoading: true,
        nonPkp: {{ old('is_non_pkp', $payment->is_non_pkp) ? 'true' : 'false' }},
        skemaPajak: @js(old('skema_pajak', $payment->skema_pajak ?? '')),
        jenisPph: @js(old('jenis_pph', $jenisPphTersimpan ?? '')),
        barisPajak: @js($barisPajakAwal),
        pilihanJenisPajak: @js($pilihanJenisPajak),
        tambahBaris() {
            const terpakai = this.barisPajak.map(b => b.jenis);
            const jenis = Object.keys(this.pilihanJenisPajak).find(j => !terpakai.includes(j));
            if (!jenis) return;
            this.barisPajak.push({ jenis: jenis, kunci: '', persen: '0', dasar: 'bruto' });
            this.muatPajak();
        },
        hapusBaris(i) {
            this.barisPajak.splice(i, 1);
            this.muatPajak();
        },
        /** Acuan mengisi ulang daftarnya; sesudah itu operator bebas menimpa. */
        pakaiUsulan() {
            fetch(@js($rutePratinjauPajak), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                },
                body: JSON.stringify({
                    skema_pajak: this.skemaPajak || null,
                    jenis_pph: this.jenisPph || null,
                    kualifikasi_pajak: this.$refs.kualifikasiPajak?.value || null,
                }),
            })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(d => {
                this.barisPajak = d.usulan ?? [];
                this.muatPajak();
            })
            .catch(() => { this.pajakGagal = true; });
        },
        /** Nominal hasil hitungan server untuk satu jenis pajak. Satu jenis
            hanya boleh sekali per pembayaran, jadi jenis cukup jadi kuncinya. */
        nominalBaris(jenis) {
            return this.pajak?.baris?.find(x => x.jenis === jenis) ?? null;
        },
        pajak: null,
        pajakMemuat: false,
        pajakGagal: false,
        pajakTimer: null,
        /**
         * Angkanya dihitung server lewat PajakPengadaan — sengaja tidak
         * disalin ke sini. Rumus DPP terikat pada tarif yang sama dengan yang
         * memotong; menyalinnya ke JS akan menghidupkan lagi jebakan dua angka
         * lepas yang dulu bisa membuat BAP salah tanpa galat.
         */
        muatPajak() {
            clearTimeout(this.pajakTimer);
            this.pajakTimer = setTimeout(() => {
                this.pajakMemuat = true;
                this.pajakGagal = false;
                fetch(@js($rutePratinjauPajak), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                    body: JSON.stringify({
                        skema_pajak: this.skemaPajak || null,
                        jenis_pph: this.jenisPph || null,
                        kualifikasi_pajak: this.$refs.kualifikasiPajak?.value || null,
                        pajak: this.barisPajak.map(b => ({
                            jenis: b.jenis,
                            kunci: b.kunci || null,
                            persen: b.persen === '' ? 0 : b.persen,
                            dasar: b.dasar,
                        })),
                    }),
                })
                .then(r => r.ok ? r.json() : Promise.reject(r.status))
                .then(d => { this.pajak = d; })
                .catch(() => { this.pajakGagal = true; this.pajak = null; })
                .finally(() => { this.pajakMemuat = false; });
            }, 300);
        },
        bapNo: @js(old('nomor_bap', $payment->nomor_bap)),
        kwtNo: @js(old('nomor_kwitansi', $payment->nomor_kwitansi)),
        tglInvoice: @js(old('tanggal_invoice', optional($payment->tanggal_invoice)->format('Y-m-d'))),
        tglBap: @js(old('tanggal_bap', optional($payment->tanggal_bap)->format('Y-m-d'))),
        tglKwitansi: @js(old('tanggal_kwitansi', optional($payment->tanggal_kwitansi)->format('Y-m-d'))),
        /** Nomor dan tanggal kwitansi hampir selalu sama dengan BAP. */
        get kwitansiBedaDariBap() {
            return (this.bapNo || this.tglBap)
                && (String(this.kwtNo ?? '') !== String(this.bapNo ?? '') || this.tglKwitansi !== this.tglBap);
        },
        samakanKwitansi() {
            this.kwtNo = this.bapNo;
            this.tglKwitansi = this.tglBap;
        },
        tglLokal(s) {
            if (!s) return '';
            const [y, m, d] = s.split('-');
            return d + '/' + m + '/' + y;
        },
        /**
         * Urutan wajarnya invoice -> BAP -> kwitansi. Hanya memberi tahu,
         * tidak menahan simpan: bisa saja ada keadaan sah yang tidak terduga,
         * mis. invoice susulan, dan memblokir atas dugaan lebih merugikan.
         */
        get tanggalTakBerurut() {
            const p = [];
            if (this.tglInvoice && this.tglBap && this.tglBap < this.tglInvoice) {
                p.push('BAP (' + this.tglLokal(this.tglBap) + ') mendahului invoice (' + this.tglLokal(this.tglInvoice) + ')');
            }
            if (this.tglBap && this.tglKwitansi && this.tglKwitansi < this.tglBap) {
                p.push('Kwitansi (' + this.tglLokal(this.tglKwitansi) + ') mendahului BAP (' + this.tglLokal(this.tglBap) + ')');
            }
            return p;
        },
        printBase: @js($printBase),
        siapCetak: {{ $siapCetak ? 'true' : 'false' }},
        loadDoc(type) {
            if (!this.siapCetak) return;
            this.docType = type;
            this.previewLoading = true;
            this.$refs.docFrame.src = this.printBase + '?embed=1&type=' + type + '&t=' + Date.now();
        },
    }"
    x-init="loadDoc('bap'); muatPajak()">
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
            <a href="{{ route('kabid.penyedia.index') }}"
                class="relative inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-emerald-700 bg-white hover:bg-emerald-50 rounded-xl shadow-sm transition-colors shrink-0">
                <i data-lucide="list" class="w-4 h-4"></i>
                Daftar Paket Pengadaan
            </a>
        </div>
    @endif

    {{-- Peringatan data belum lengkap — hanya saat tampilan biasa --}}
    @if(! $siapCetak)
        <div x-show="!formTerbuka" class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
            <div class="p-1.5 rounded-full bg-amber-100 shrink-0">
                <i data-lucide="file-warning" class="w-4 h-4 text-amber-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold text-amber-800">Dokumen pembayaran belum bisa dicetak</p>
                <p class="mt-1 text-xs text-amber-700 leading-relaxed">
                    Masih kosong: <strong>{{ app(\App\Services\Pengadaan\KelengkapanTahap::class)->kalimat($kurang) }}</strong>.
                </p>
                @if($bolehUbah)
                    <button type="button" @click="formTerbuka = true"
                        class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Lengkapi sekarang
                    </button>
                @endif
            </div>
        </div>
    @endif

    {{-- Form data pembayaran --}}
    @if($bolehUbah)
        {{-- Tanpa x-transition: transisi di panel sebesar ini membuat x-show
             gagal menyembunyikannya dan menular ke binding di dalamnya. --}}
        <div x-show="formTerbuka"
             class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden" style="display:none">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-xs uppercase tracking-widest">
                        <i data-lucide="file-invoice" class="w-3.5 h-3.5 text-blue-500"></i>
                        Data Pembayaran
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Isi dokumen penagihan dan setoran penyedia. Setelah disimpan, dokumen bisa dipratinjau dan dicetak.
                    </p>
                </div>
                <button type="button" @click="formTerbuka = false" title="Tutup form"
                    class="text-slate-400 hover:text-slate-600 shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('kabid.procurement-packages.payment.store', $package) }}">
                @csrf

                {{-- Tiga kolom yang terisi penuh di seluruh bagian, jadi lebar
                     kartu terpakai habis tanpa menyisakan ruang kosong di kanan
                     dan tanpa memelarkan kolom pendek. Setiap dokumen tagihan
                     jadi satu blok berisi nomor + tanggalnya, supaya pasangannya
                     tetap terbaca sebagai satu kesatuan. --}}
                <div class="p-5 sm:p-6 space-y-7">

                    {{-- 1. Dokumen tagihan --}}
                    <section>
                        <div class="flex items-center gap-3 mb-3">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Dokumen Tagihan</p>
                            <span class="flex-1 h-px bg-slate-100"></span>
                        </div>

                        {{-- Satu baris per dokumen, bukan tiga kartu sejajar. Ketiganya tidak
                             setara: invoice diterbitkan penyedia dengan format nomornya sendiri,
                             sedangkan BAP dan kwitansi nomornya kita yang buat dengan buntut
                             tetap. Tanggalnya sekolom supaya urutannya terbaca sekaligus. --}}
                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-4 py-2 bg-slate-50 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <div class="col-span-4">Dokumen</div>
                                <div class="col-span-5">Nomor</div>
                                <div class="col-span-3">Tanggal</div>
                            </div>

                            {{-- 1. Invoice — nomornya bebas, mengikuti berkas penyedia --}}
                            <div class="px-4 py-3 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 sm:items-start">
                                <div class="sm:col-span-4 flex items-start gap-2.5">
                                    <span class="flex-none w-[19px] h-[19px] mt-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-black flex items-center justify-center">1</span>
                                    <span>
                                        <span class="block text-xs font-bold text-slate-700 leading-tight">Invoice</span>
                                        <span class="block text-[10px] text-slate-400">diterbitkan penyedia</span>
                                    </span>
                                </div>
                                <div class="sm:col-span-5">
                                    <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Nomor</label>
                                    <input type="text" name="nomor_invoice" value="{{ old('nomor_invoice', $payment->nomor_invoice) }}"
                                        placeholder="Nomor invoice penyedia"
                                        class="w-full rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Tanggal</label>
                                    <input type="date" name="tanggal_invoice" x-model="tglInvoice"
                                        class="w-full rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>

                            {{-- 2. BAP — nomor urut menyatu dengan buntutnya, terbaca kiri ke
                                 kanan persis seperti yang tercetak di dokumen. --}}
                            <div class="px-4 py-3 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 sm:items-start">
                                <div class="sm:col-span-4 flex items-start gap-2.5">
                                    <span class="flex-none w-[19px] h-[19px] mt-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-black flex items-center justify-center">2</span>
                                    <span>
                                        <span class="block text-xs font-bold text-slate-700 leading-tight">Berita Acara Pembayaran</span>
                                        <span class="block text-[10px] text-slate-400">penomoran internal</span>
                                    </span>
                                </div>
                                <div class="sm:col-span-5">
                                    <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Nomor</label>
                                    <div class="flex min-w-0">
                                        <input type="number" name="nomor_bap" x-model="bapNo" placeholder="15"
                                            class="relative w-16 flex-none rounded-l-lg rounded-r-none border-r-0 border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 focus:z-10 text-sm text-right">
                                        <span class="min-w-0 flex items-center px-2.5 rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 text-[11px] text-slate-500 font-mono truncate">/BAP/{{ $kodeProgram }}/PERKIMPLH-C</span>
                                    </div>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Tanggal</label>
                                    <input type="date" name="tanggal_bap" x-model="tglBap"
                                        class="w-full rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>

                            {{-- 3. Kwitansi — nomor dan tanggalnya hampir selalu sama dengan BAP --}}
                            <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 sm:items-start">
                                <div class="sm:col-span-4 flex items-start gap-2.5">
                                    <span class="flex-none w-[19px] h-[19px] mt-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-black flex items-center justify-center">3</span>
                                    <span>
                                        <span class="block text-xs font-bold text-slate-700 leading-tight">Kwitansi</span>
                                        <span class="block text-[10px] text-slate-400">penomoran internal</span>
                                    </span>
                                </div>
                                <div class="sm:col-span-5">
                                    <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Nomor</label>
                                    <div class="flex min-w-0">
                                        <input type="number" name="nomor_kwitansi" x-model="kwtNo" placeholder="15"
                                            class="relative w-16 flex-none rounded-l-lg rounded-r-none border-r-0 border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 focus:z-10 text-sm text-right">
                                        <span class="min-w-0 flex items-center px-2.5 rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 text-[11px] text-slate-500 font-mono truncate">/KWT/{{ $kodeProgram }}/PERKIMPLH-C</span>
                                    </div>
                                    <button type="button" x-show="kwitansiBedaDariBap" @click="samakanKwitansi()"
                                        style="display: none;"
                                        class="mt-1 text-[10px] font-bold text-emerald-700 hover:text-emerald-800 underline">Samakan dengan BAP</button>
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Tanggal</label>
                                    <input type="date" name="tanggal_kwitansi" x-model="tglKwitansi"
                                        class="w-full rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                </div>
                            </div>

                            {{-- Memberi tahu, tidak menahan simpan — lihat alasannya di
                                 getter tanggalTakBerurut. --}}
                            <template x-if="tanggalTakBerurut.length">
                                <div class="px-4 py-2.5 bg-amber-50 border-t border-amber-200 text-[11px] text-amber-800 leading-relaxed">
                                    <template x-for="pesan in tanggalTakBerurut" :key="pesan">
                                        <p><span class="font-bold" x-text="pesan"></span> &mdash; urutan lazimnya invoice, BAP, kemudian kwitansi. Mohon diperiksa kembali.</p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </section>

                    {{-- 2. Pajak & potongan.
                         Seksi tersendiri, bukan menumpang di Dokumen Tambahan: ini
                         satu-satunya bagian form yang menentukan berapa uang yang
                         benar-benar diterima penyedia. --}}
                    <section>
                        <div class="flex items-center gap-3 mb-3">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Pajak &amp; Potongan</p>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-[11px] text-slate-400 whitespace-nowrap hidden sm:inline">menentukan nilai yang dibayarkan kepada penyedia</span>
                        </div>

                        <div class="rounded-xl border border-slate-200 overflow-hidden">
                            {{-- Acuan hanya mengisikan usulan lalu berhenti berperan,
                                 jadi ia bilah tipis — bukan tiga kotak sejajar yang
                                 terlihat seperti isian utama. --}}
                            <div class="px-3.5 py-2 bg-slate-50 border-b border-slate-200 flex items-center gap-2 flex-wrap">
                                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Acuan</span>
                                <select name="skema_pajak" x-model="skemaPajak" @change="pakaiUsulan()"
                                    class="rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-[11px] py-1 pl-2 pr-7">
                                    <option value="">Mengikuti Rekening Belanja ({{ \App\Services\Pajak\SkemaPajak::pilihan()[$skemaRekening] ?? 'Standar' }})</option>
                                    @foreach(\App\Services\Pajak\SkemaPajak::pilihan() as $kode => $label)
                                        <option value="{{ $kode }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select name="jenis_pph" x-model="jenisPph" @change="pakaiUsulan()"
                                    x-show="skemaPajak !== 'konstruksi'"
                                    class="rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-[11px] py-1 pl-2 pr-7">
                                    <option value="">Mengikuti Jenis Pengadaan ({{ $pilihanJenisPph[$jenisPphTurunan] ?? 'PPh 23 — Jasa' }})</option>
                                    @foreach($pilihanJenisPph as $kode => $label)
                                        <option value="{{ $kode }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select name="kualifikasi_pajak" x-ref="kualifikasiPajak" @change="pakaiUsulan()"
                                    x-show="skemaPajak === 'konstruksi'" style="display: none;"
                                    class="rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-[11px] py-1 pl-2 pr-7">
                                    <option value="">Pilih Kualifikasi Penyedia</option>
                                    @foreach($kualifikasiKonstruksi as $k)
                                        <option value="{{ $k }}" @selected(old('kualifikasi_pajak', $payment->kualifikasi_pajak) === $k)>{{ $k }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="pakaiUsulan()"
                                    class="ml-auto text-[11px] font-bold text-slate-500 hover:text-slate-700 underline">Terapkan Usulan</button>
                            </div>

                            {{-- Penanda bahwa daftar pajak ikut dikirim. Isian pajak[] hanya
                                 ada di dalam x-for, jadi menghapus semua baris membuat
                                 formulir tidak mengirim apa pun — dan tanpa penanda ini
                                 server tidak bisa membedakannya dari "tidak ada data
                                 pajak di permintaan ini", lalu memungut usulan lagi. --}}
                            <input type="hidden" name="pajak_diisi" value="1">

                            <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-4 py-2 bg-slate-50/60 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <div class="col-span-4">Pajak</div>
                                <div class="col-span-2">Tarif</div>
                                <div class="col-span-3">Dasar pengenaan</div>
                                <div class="col-span-3 text-right">Potongan</div>
                            </div>

                            <template x-if="barisPajak.length === 0">
                                <p class="px-4 py-4 text-[11.5px] text-slate-400 text-center leading-relaxed">
                                    Tidak ada pajak yang dipungut untuk pembayaran ini.<br>
                                    <span class="text-[10.5px]">Baris potongan tidak akan muncul di BAP.</span>
                                </p>
                            </template>

                            <template x-for="(b, i) in barisPajak" :key="i">
                                <div class="px-4 py-2.5 border-b border-slate-100 grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-3 sm:items-center">
                                    <div class="sm:col-span-4">
                                        <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Pajak</label>
                                        <select :name="'pajak[' + i + '][jenis]'" x-model="b.jenis" @change="muatPajak()"
                                            class="w-full rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-xs">
                                            @foreach($pilihanJenisPajak as $kode => $label)
                                                <option value="{{ $kode }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Tarif</label>
                                        <div class="relative">
                                            <input type="number" step="0.01" min="0" max="100"
                                                :name="'pajak[' + i + '][persen]'" x-model="b.persen" @input="muatPajak()"
                                                class="w-full rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-xs pr-6 text-right">
                                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">%</span>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label class="sm:hidden block text-[10px] font-semibold text-slate-400 mb-1">Dasar pengenaan</label>
                                        <select :name="'pajak[' + i + '][dasar]'" x-model="b.dasar" @change="muatPajak()"
                                            class="w-full rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-xs">
                                            @foreach($pilihanDasarPajak as $kode => $label)
                                                <option value="{{ $kode }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Nominalnya di baris yang sedang diubah, bukan di panel
                                         terpisah — supaya angka yang sama tidak tampil dua kali
                                         dan akibat tiap perubahan langsung terlihat. --}}
                                    <div class="sm:col-span-3 flex items-center justify-between sm:justify-end gap-2">
                                        <span class="sm:hidden text-[10px] font-semibold text-slate-400">Potongan</span>
                                        <span class="text-right leading-tight">
                                            <span class="block text-xs font-bold text-slate-800 tabular-nums"
                                                x-text="nominalBaris(b.jenis)?.nominal ?? '—'"></span>
                                            <span class="block text-[10px] text-slate-400 tabular-nums"
                                                x-text="nominalBaris(b.jenis) ? 'dari ' + nominalBaris(b.jenis).nilaiDasar : ''"></span>
                                        </span>
                                        <button type="button" @click="hapusBaris(i)" title="Hapus pajak ini"
                                            class="text-slate-300 hover:text-rose-600 shrink-0">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </div>

                                    <input type="hidden" :name="'pajak[' + i + '][kunci]'" :value="b.kunci || ''">
                                </div>
                            </template>

                            <div class="px-4 py-2 border-b border-slate-100">
                                <button type="button" @click="tambahBaris()"
                                    class="text-[11.5px] font-black text-emerald-700 hover:text-emerald-800">Tambah Pajak</button>
                            </div>

                            {{-- Kaki menggantikan panel ringkasan yang dulu berdiri sendiri. --}}
                            <div class="px-4 py-3 bg-slate-50 space-y-1 text-xs">
                                <div class="flex justify-between text-slate-500">
                                    <span>Nilai Kontrak</span>
                                    <span class="tabular-nums" x-text="pajak?.nilaiKontrak ?? '—'"></span>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Total Potongan</span>
                                    <span class="tabular-nums" x-text="pajak?.totalPotongan ?? '—'"></span>
                                </div>
                                <div class="flex justify-between pt-1.5 border-t border-slate-200 font-black">
                                    <span class="text-slate-600">Diterima Penyedia</span>
                                    <span class="text-emerald-700 text-sm tabular-nums" x-text="pajak?.jumlahBayar ?? '—'"></span>
                                </div>
                                <p x-show="pajakMemuat" class="text-[10.5px] text-slate-400 pt-0.5">Menghitung&hellip;</p>
                                <p x-show="pajakGagal" style="display: none;" class="text-[10.5px] text-rose-600 pt-0.5">
                                    Pratinjau gagal dimuat. Angka pastinya tetap dihitung saat disimpan.
                                </p>
                            </div>
                        </div>

                        @if($jenisPphBeda)
                            <p class="mt-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
                                Jenis pengadaan paket ini <b>{{ $package->jenis_pengadaan }}</b>
                                (bawaannya {{ $pilihanJenisPph[$jenisPphTurunan] ?? '—' }}), tetapi pembayaran
                                ini memakai <b>{{ $pilihanJenisPph[$jenisPphTersimpan] }}</b>. Perbedaannya
                                dipertahankan — ubah di sini bila tidak sengaja.
                            </p>
                        @endif

                        @if($nilaiKontrakBergeser)
                            <p class="mt-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-[11px] text-amber-800 leading-relaxed">
                                Dokumen pembayaran ini memakai nilai kontrak
                                <b>Rp {{ number_format($nilaiKontrakBeku, 0, ',', '.') }}</b>,
                                sedangkan nilai kontrak yang berlaku sekarang
                                <b>Rp {{ number_format($nilaiKontrakKini, 0, ',', '.') }}</b>.
                                BAP yang sudah dicetak tidak ikut berubah — simpan ulang data
                                pembayaran bila memang ingin memakai nilai yang baru.
                            </p>
                        @endif

                        <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                            Pilihan di sini dikunci saat disimpan — beserta rupiahnya. Menyesuaikan
                            tarif, rekening, jenis pengadaan, bahkan nilai kontrak nanti tidak akan
                            mengubah dokumen yang sudah dicetak.
                        </p>
                    </section>

                    {{-- 3. Setoran penyedia — pindahan dari tahap Pemilihan Penyedia --}}
                    <section>
                        <div class="flex items-center gap-3 mb-3">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Setoran Penyedia</p>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-[11px] text-slate-400 whitespace-nowrap hidden sm:inline">dipakai BAP, Ringkasan Kontrak &amp; Non-PKP</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">NPWP Penyedia</label>
                                <input type="text" name="npwp_penyedia" value="{{ old('npwp_penyedia', $process->npwp_penyedia) }}"
                                    placeholder="00.000.000.0-000.000"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-mono">
                                <p class="text-[11px] text-amber-600 mt-1.5 leading-snug">
                                    NPWP <strong>badan usaha</strong>, bukan NPWP direktur.
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Bank</label>
                                <input type="text" name="nama_bank" value="{{ old('nama_bank', $process->nama_bank) }}"
                                    placeholder="Contoh: Bank Kalbar"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nomor Rekening</label>
                                <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $process->nomor_rekening) }}"
                                    placeholder="Rekening atas nama penyedia"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-mono">
                            </div>
                        </div>
                    </section>

                    {{-- 4. PPTK --}}
                    <section>
                        <div class="flex items-center gap-3 mb-3">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Data PPTK</p>
                            <span class="flex-1 h-px bg-slate-100"></span>
                            <span class="text-[11px] text-slate-400 whitespace-nowrap hidden sm:inline">penanda tangan BAP &amp; kwitansi, terisi dari master SKPD</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama PPTK</label>
                                <input type="text" name="nama_pptk" value="{{ old('nama_pptk', $pptkPrefill['nama_pptk']) }}"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">NIP PPTK</label>
                                <input type="text" name="nip_pptk" value="{{ old('nip_pptk', $pptkPrefill['nip_pptk']) }}"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pangkat / Golongan</label>
                                <input type="text" name="pangkat_golongan_pptk" value="{{ old('pangkat_golongan_pptk', $pptkPrefill['pangkat_golongan_pptk']) }}"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>
                        </div>
                    </section>

                    {{-- 5. Dokumen tambahan --}}
                    <section>
                        <div class="flex items-center gap-3 mb-3">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest whitespace-nowrap">Dokumen Tambahan</p>
                            <span class="flex-1 h-px bg-slate-100"></span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-5 gap-y-4 items-start">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Ringkasan Kontrak</label>
                                <input type="date" name="tanggal_ringkasan_kontrak" value="{{ old('tanggal_ringkasan_kontrak', optional($payment->tanggal_ringkasan_kontrak)->format('Y-m-d')) }}"
                                    class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            </div>

                            {{-- Sakelar dan tanggalnya disatukan dalam satu kotak supaya
                                 saat dicentang tata letaknya tidak melompat. --}}
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Surat Non-PKP</label>
                                <div class="rounded-xl border p-3 transition-colors"
                                     :class="nonPkp ? 'border-emerald-300 bg-emerald-50/60' : 'border-slate-200 bg-slate-50/60'">
                                    <div class="flex flex-wrap items-center gap-x-5 gap-y-3">
                                        <label class="flex items-center gap-2.5 cursor-pointer">
                                            <input type="checkbox" name="is_non_pkp" value="1" x-model="nonPkp"
                                                class="rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                            <span class="text-sm font-semibold" :class="nonPkp ? 'text-emerald-700' : 'text-slate-600'">
                                                Lampirkan surat Non-PKP
                                            </span>
                                        </label>

                                        {{-- Tanggalnya selalu dirender, hanya diredupkan saat tidak
                                             dicentang. Selain menjaga tinggi kotak tetap, ini
                                             menghindari x-show bersarang di dalam panel yang juga
                                             ber-transisi — kombinasi itu tidak dapat diandalkan. --}}
                                        <div class="flex items-center gap-2 transition-opacity"
                                             :class="nonPkp ? 'opacity-100' : 'opacity-50'">
                                            <span class="text-[11px] font-semibold text-slate-500 whitespace-nowrap">Tanggal surat</span>
                                            <input type="date" name="tanggal_non_pkp" :disabled="!nonPkp"
                                                value="{{ old('tanggal_non_pkp', optional($payment->tanggal_non_pkp)->format('Y-m-d')) }}"
                                                class="rounded-lg border-slate-300 bg-white focus:border-emerald-500 focus:ring-emerald-500 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    @if($errors->any())
                        <div class="px-3.5 py-2.5 rounded-xl bg-rose-50 border border-rose-200">
                            <ul class="text-xs text-rose-700 space-y-0.5">
                                @foreach($errors->all() as $pesan)
                                    <li>{{ $pesan }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>


                {{-- Tombol menempel di kaki kartu supaya posisinya tetap terduga --}}
                <div class="px-5 sm:px-6 py-4 bg-slate-50/70 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[11px] text-slate-400">
                        Kolom boleh diisi bertahap — dokumen baru bisa dicetak setelah semuanya lengkap.
                    </p>
                    <div class="flex items-center gap-2 ml-auto">
                        <button type="button" @click="formTerbuka = false"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                            <i data-lucide="save" class="w-4 h-4"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    {{-- Tampilan biasa: pratinjau dokumen + ringkasan. Disembunyikan selama
         form isian terbuka supaya layar tidak menampilkan dua hal sekaligus. --}}
    <div x-show="!formTerbuka" class="flex flex-col-reverse lg:flex-row gap-6 items-start">

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
                    @if($siapCetak)
                        <button type="button" @click="$refs.docFrame.contentWindow.print()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak PDF
                        </button>
                        <a :href="printBase + '?type=' + docType" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-sm transition-colors">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Tab Baru
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-400 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed"
                              title="Lengkapi data pembayaran dulu">
                            <i data-lucide="printer-off" class="w-3.5 h-3.5"></i> Cetak PDF
                        </span>
                    @endif
                </div>
            </div>

            <div class="relative bg-slate-200" style="min-height: 850px;">
                @if($siapCetak)
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
                @else
                    {{-- Pratinjau ditahan supaya tidak ada yang mencetak berkas
                         setengah jadi lewat menu cetak peramban. --}}
                    <div class="flex flex-col items-center justify-center text-center px-8" style="height: 850px;">
                        <span class="w-14 h-14 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-400">
                            <i data-lucide="file-lock-2" class="w-7 h-7"></i>
                        </span>
                        <p class="mt-4 text-sm font-bold text-slate-600">Dokumen belum dapat ditampilkan</p>
                        <p class="mt-1.5 text-xs text-slate-500 max-w-sm leading-relaxed">
                            Lengkapi data pembayaran dulu supaya dokumen tidak keluar dengan bagian kosong.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Kanan: data penagihan + aksi --}}
        <aside class="w-full lg:w-80 shrink-0 lg:sticky lg:top-20 space-y-5">

            {{-- Data penagihan --}}
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between gap-2">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 text-xs uppercase tracking-widest">
                        <i data-lucide="file-invoice" class="w-3.5 h-3.5 text-blue-500"></i>
                        Data Penagihan
                    </h3>
                    {{-- Selama masih di tahap Pembayaran, datanya harus tetap
                         bisa dikoreksi — bukan hanya saat masih kosong. --}}
                    @if($bolehUbah)
                        <button type="button" @click="formTerbuka = true; window.scrollTo({ top: 0, behavior: 'smooth' })"
                            class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-bold text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors shrink-0">
                            <i data-lucide="pencil" class="w-3 h-3"></i> Ubah
                        </button>
                    @endif
                </div>
                <div class="divide-y divide-slate-100">
                    @php
                        // Kartu ini yang pertama dilihat, jadi pajaknya ikut disebut —
                        // sebelumnya seluruh potongan hanya terlihat setelah menekan Ubah.
                        $pajakTampil = \App\Services\Pajak\PajakPengadaan::hitung($procurementPackage);
                    @endphp
                    <div class="px-4 py-3">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Tagihan (Sesuai Kontrak)</p>
                        <p class="font-extrabold text-slate-700 text-base mt-0.5">Rp {{ number_format((float) $pajakTampil['nilaiKontrak'], 0, ',', '.') }}</p>

                        <div class="mt-2.5 space-y-1">
                            @forelse($pajakTampil['baris'] as $b)
                                <div class="flex items-baseline justify-between gap-2 text-[11px]">
                                    <span class="text-slate-500">{{ $b['label'] }}</span>
                                    <span class="text-slate-600 tabular-nums whitespace-nowrap">&minus; {{ number_format($b['nominal'], 0, ',', '.') }}</span>
                                </div>
                                <p class="text-[10px] text-slate-400 -mt-0.5">
                                    Dasar pengenaan Rp {{ number_format($b['nilaiDasar'], 0, ',', '.') }}
                                    ({{ \App\Services\Pajak\PajakPengadaan::pilihanDasar()[$b['dasar']] ?? $b['dasar'] }})
                                </p>
                            @empty
                                <p class="text-[11px] text-slate-400">Tidak ada pajak yang dipungut.</p>
                            @endforelse
                        </div>

                        <div class="mt-2 pt-2 border-t border-slate-100 flex items-baseline justify-between gap-2">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Diterima Penyedia</p>
                            <p class="font-extrabold text-emerald-600 text-lg tabular-nums">Rp {{ number_format($pajakTampil['jumlahBayar'], 0, ',', '.') }}</p>
                        </div>
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
                    <div class="px-4 py-2.5">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Setoran Penyedia</p>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5 break-all">{{ $process->nama_bank ?? '-' }} &bull; {{ $process->nomor_rekening ?? '-' }}</p>
                        <p class="text-[11px] text-slate-400 font-mono break-all">NPWP {{ $process->npwp_penyedia ?? '-' }}</p>
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
                    @if($siapCetak)
                        <button type="button" @click="showConfirmSelesai = true"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md shadow-emerald-200 transition-all hover:-translate-y-0.5">
                            <i data-lucide="flag" class="w-4 h-4"></i>
                            Selesaikan Pengadaan
                        </button>
                    @else
                        <button type="button" disabled
                            title="Lengkapi data pembayaran dulu"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-slate-400 bg-slate-100 border border-slate-200 rounded-xl cursor-not-allowed">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            Lengkapi data dulu
                        </button>
                    @endif
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
