@php
    $package = $procurementPackage->package;

    $tanggalDiterimaValue = filled($procurementPackage->tanggal_barang_diterima)
        ? \Illuminate\Support\Carbon::parse($procurementPackage->tanggal_barang_diterima)->format('Y-m-d')
        : null;

    $inisialPpk =
        collect(explode(' ', trim($procurementPackage->nama_ppk ?? '')))
            ->filter(fn($k) => $k !== '' && ctype_alpha($k[0]))
            ->map(fn($k) => strtoupper($k[0]))
            ->take(2)
            ->implode('') ?:
        '?';
@endphp

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- KOLOM KIRI: Informasi Paket + Data PPK --}}
    <div class="space-y-6">

        {{-- Informasi Paket --}}
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i data-lucide="box" class="w-4 h-4 text-blue-500"></i> Informasi Paket
            </h3>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                <div class="grid grid-cols-2 border-b border-slate-100">
                    <div class="p-4 border-r border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">ID RUP</p>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                <i data-lucide="hash" class="w-3.5 h-3.5"></i>
                            </span>
                            <p class="font-semibold text-slate-800 font-mono">{{ $package->id_rup ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Tahun Anggaran
                        </p>
                        <div class="flex items-center gap-2">
                            <span
                                class="w-6 h-6 rounded bg-sky-100 flex items-center justify-center text-sky-600 shrink-0">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            </span>
                            <p class="font-semibold text-slate-800">{{ $package->fiscalYear->tahun ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 border-b border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Nama Paket</p>
                    <div class="flex items-start gap-2">
                        <span
                            class="w-6 h-6 rounded bg-violet-100 flex items-center justify-center text-violet-600 shrink-0 mt-0.5">
                            <i data-lucide="package" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-sm font-semibold text-slate-800 leading-snug">
                            {{ $package->nama_paket ?? '-' }}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 border-b border-slate-100">
                    <div class="p-4 border-r border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Jenis Pengadaan
                        </p>
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i data-lucide="shapes" class="w-3 h-3 text-slate-400"></i>
                            {{ $package->jenis_pengadaan ?? '-' }}
                        </span>
                    </div>
                    <div class="p-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Metode
                            Pengadaan</p>
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i data-lucide="route" class="w-3 h-3 text-slate-400"></i>
                            {{ $package->metode_pengadaan ?? '-' }}
                        </span>
                    </div>
                </div>
                <div class="p-4 border-b border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Pagu Anggaran</p>
                    <div class="flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                            <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="font-bold text-emerald-600 text-base">Rp
                            {{ number_format((float) ($package->pagu ?? 0), 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="p-4 border-b border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Sub Kegiatan</p>
                    <div class="flex items-start gap-2">
                        <span
                            class="w-6 h-6 rounded bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 mt-0.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-sm font-medium text-slate-700 leading-snug">
                            {{ $package->subActivity?->kode }}
                            {{ $package->subActivity ? '- ' . $package->subActivity->nama : '-' }}
                        </p>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Rekening Belanja</p>
                    <div class="flex items-start gap-2">
                        <span
                            class="w-6 h-6 rounded bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 mt-0.5">
                            <i data-lucide="hash" class="w-3.5 h-3.5"></i>
                        </span>
                        <p class="text-sm font-medium text-slate-700 leading-snug">
                            {{ $package->account?->kode }}
                            {{ $package->account ? '- ' . $package->account->nama : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data PPK --}}
        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4 text-blue-500"></i> Pejabat Pembuat Komitmen
            </h3>
            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                {{-- Kartu identitas PPK --}}
                <div
                    class="p-4 flex items-center gap-3 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100">
                    <span
                        class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shadow-blue-200 shrink-0">
                        {{ $inisialPpk }}
                    </span>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800 truncate">{{ $procurementPackage->nama_ppk ?? '-' }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $procurementPackage->pangkat_gol_ppk ?? 'Pangkat/Gol. belum diisi' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 text-sm">
                    <div class="p-3.5 border-b sm:border-r border-slate-100 flex items-center gap-2.5">
                        <i data-lucide="id-card" class="w-4 h-4 text-slate-300 shrink-0"></i>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NIP</p>
                            <p class="font-medium text-slate-700 truncate">{{ $procurementPackage->nip_ppk ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="p-3.5 border-b border-slate-100 flex items-center gap-2.5">
                        <i data-lucide="phone" class="w-4 h-4 text-slate-300 shrink-0"></i>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. Telepon</p>
                            <p class="font-medium text-slate-700 truncate">
                                {{ $procurementPackage->no_telp_ppk ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="p-3.5 border-b sm:border-b-0 sm:border-r border-slate-100 flex items-center gap-2.5">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-300 shrink-0"></i>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</p>
                            <p class="font-medium text-slate-700 truncate">{{ $procurementPackage->email_ppk ?? '-' }}
                            </p>
                        </div>
                    </div>
                    <div class="p-3.5 flex items-center gap-2.5">
                        <i data-lucide="building-2" class="w-4 h-4 text-slate-300 shrink-0"></i>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NPWP Instansi</p>
                            <p class="font-medium text-slate-700 truncate">
                                {{ $procurementPackage->npwp_instansi ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KOLOM KANAN: Detail Kontrak & Pelaksanaan (diisi Kabid) --}}
    <div x-data="{ adaGaransi: {{ old('ada_garansi', $procurementPackage->ada_garansi) ? 'true' : 'false' }} }">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-2">
            <i data-lucide="file-signature" class="w-4 h-4 text-blue-500"></i> Detail Kontrak & Pelaksanaan
        </h3>

        <form method="POST" id="form-detail-kontrak"
            action="{{ route('kabid.procurement-packages.contract.update', $package) }}"
            class="bg-white border border-slate-200 rounded-xl shadow-sm divide-y divide-slate-100">
            @csrf
            @method('PUT')

            {{-- Jadwal Pengiriman: kalender klik rentang --}}
            @php
                $isBarang = $package->jenis_pengadaan === 'Barang';
                $labelMulai = $isBarang ? 'Mulai Dikirim' : 'Mulai Pekerjaan';
                $labelSelesai = $isBarang ? 'Barang Diterima' : 'Pekerjaan Selesai';
            @endphp
            <div class="p-4" x-data="kabidJadwalKirim({
                end: @js(old('tanggal_barang_diterima', $tanggalDiterimaValue)),
                nilai: @js((int) old('jangka_waktu_nilai', $procurementPackage->jangka_waktu_nilai ?? 0)),
                satuan: @js(old('jangka_waktu_satuan', $procurementPackage->jangka_waktu_satuan ?? 'hari')),
            })">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 flex items-center gap-1.5">
                    <i data-lucide="calendar-days" class="w-3.5 h-3.5 text-slate-400"></i>
                    {{ $isBarang ? 'Jadwal Pengiriman Barang' : 'Jadwal Pelaksanaan Pekerjaan' }}
                    <span class="font-normal text-slate-400">&mdash; klik tanggal {{ strtolower($labelMulai) }}, lalu
                        tanggal {{ strtolower($labelSelesai) }}</span>
                </label>

                {{-- Kalender --}}
                <div class="border border-slate-200 rounded-xl overflow-hidden select-none">
                    <div class="flex items-center justify-between px-3 py-2 bg-slate-50/70 border-b border-slate-100">
                        <button type="button" @click="prevMonth()"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <p class="text-sm font-bold text-slate-700" x-text="monthLabel"></p>
                        <button type="button" @click="nextMonth()"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div
                        class="grid grid-cols-7 text-center text-[10px] font-bold text-slate-400 uppercase tracking-wide px-2 pt-2">
                        <template x-for="h in ['Min','Sen','Sel','Rab','Kam','Jum','Sab']"><span x-text="h"
                                class="py-1"></span></template>
                    </div>
                    <div class="grid grid-cols-7 gap-y-0.5 px-2 pb-2">
                        <template x-for="(cell, idx) in cells" :key="idx">
                            <div class="flex items-center justify-center">
                                <button type="button" x-show="cell.d" @click="pick(cell.iso)"
                                    class="w-8 h-8 rounded-full text-xs font-semibold transition-all"
                                    :class="cellClass(cell.iso)" x-text="cell.d"></button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Ringkasan pilihan --}}
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border"
                        :class="start ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                            'bg-slate-50 text-slate-400 border-slate-200'">
                        <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                        {{ $labelMulai }}: <span x-text="start ? displayDate(start) : 'belum dipilih'"></span>
                    </span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-300"></i>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border"
                        :class="end ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                            'bg-slate-50 text-slate-400 border-slate-200'">
                        <i data-lucide="package-check" class="w-3.5 h-3.5"></i>
                        {{ $labelSelesai }}: <span x-text="end ? displayDate(end) : 'belum dipilih'"></span>
                    </span>
                    <span x-show="durasi > 0" x-transition.opacity
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                        <i data-lucide="timer" class="w-3.5 h-3.5"></i>
                        <span x-text="durasi"></span> hari
                    </span>
                </div>

                {{-- Legenda warna kalender --}}
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 pt-3 border-t border-dashed border-slate-200">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full ring-2 ring-inset ring-amber-400 bg-amber-50"></span> Hari ini
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-emerald-600"></span> {{ $labelMulai }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-indigo-600"></span> {{ $labelSelesai }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-emerald-100"></span> Rentang pengiriman
                    </span>
                </div>

                {{-- Nilai tersinkron kalender yang dikirim ke server --}}
                <input type="hidden" name="jangka_waktu_nilai" :value="nilai">
                <input type="hidden" name="jangka_waktu_satuan" :value="satuan">
                <input type="hidden" name="tanggal_barang_diterima" :value="end">
            </div>

            {{-- Jenis Kontrak: kartu pilihan --}}
            <div class="p-4" x-data="{ jenis: @js(old('jenis_kontrak', $procurementPackage->jenis_kontrak)) }">
                <label class="block text-xs font-semibold text-slate-600 mb-2 flex items-center gap-1.5">
                    <i data-lucide="file-signature" class="w-3.5 h-3.5" :class="jenis ? 'text-emerald-500' : 'text-slate-300'"></i>
                    Jenis Kontrak
                </label>
                <input type="hidden" name="jenis_kontrak" :value="jenis">
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="jenis = 'Harga Satuan'"
                        class="relative flex flex-col items-start gap-1 p-3 rounded-xl border text-left transition-all"
                        :class="jenis === 'Harga Satuan'
                            ? 'bg-emerald-50 border-emerald-400 ring-1 ring-emerald-400'
                            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 absolute top-2.5 right-2.5" x-show="jenis === 'Harga Satuan'"></i>
                        <i data-lucide="calculator" class="w-4 h-4" :class="jenis === 'Harga Satuan' ? 'text-emerald-600' : 'text-slate-400'"></i>
                        <span class="text-sm font-bold" :class="jenis === 'Harga Satuan' ? 'text-emerald-800' : 'text-slate-700'">Harga Satuan</span>
                        <span class="text-[10px] leading-snug" :class="jenis === 'Harga Satuan' ? 'text-emerald-600' : 'text-slate-400'">
                            Dibayar sesuai volume aktual &times; harga satuan
                        </span>
                    </button>
                    <button type="button" @click="jenis = 'Lump Sum'"
                        class="relative flex flex-col items-start gap-1 p-3 rounded-xl border text-left transition-all"
                        :class="jenis === 'Lump Sum'
                            ? 'bg-emerald-50 border-emerald-400 ring-1 ring-emerald-400'
                            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 absolute top-2.5 right-2.5" x-show="jenis === 'Lump Sum'"></i>
                        <i data-lucide="banknote" class="w-4 h-4" :class="jenis === 'Lump Sum' ? 'text-emerald-600' : 'text-slate-400'"></i>
                        <span class="text-sm font-bold" :class="jenis === 'Lump Sum' ? 'text-emerald-800' : 'text-slate-700'">Lump Sum</span>
                        <span class="text-[10px] leading-snug" :class="jenis === 'Lump Sum' ? 'text-emerald-600' : 'text-slate-400'">
                            Nilai kontrak tetap untuk keseluruhan pekerjaan
                        </span>
                    </button>
                </div>
            </div>

            {{-- Garansi + Layanan Purna Jual (berdampingan) --}}
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4"
                x-data="{ purnaJual: {{ old('layanan_purna_jual', $procurementPackage->layanan_purna_jual) ? 'true' : 'false' }} }">

                {{-- Garansi --}}
                <div class="rounded-xl border p-3 transition-colors"
                    :class="adaGaransi ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-white'">
                    <label class="block text-xs font-semibold text-slate-600 mb-2 flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"
                            :class="adaGaransi ? 'text-emerald-500' : 'text-slate-300'"></i>
                        Garansi
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <label
                            class="flex items-center justify-center px-2 py-1.5 rounded-lg border cursor-pointer text-xs font-semibold transition-colors"
                            :class="!adaGaransi ? 'bg-slate-700 border-slate-700 text-white shadow-sm' :
                                'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
                            <input type="radio" name="ada_garansi" value="0" class="sr-only"
                                @change="adaGaransi = false" @checked(!old('ada_garansi', $procurementPackage->ada_garansi))>
                            Tidak Ada
                        </label>
                        <label
                            class="flex items-center justify-center px-2 py-1.5 rounded-lg border cursor-pointer text-xs font-semibold transition-colors"
                            :class="adaGaransi ? 'bg-emerald-600 border-emerald-600 text-white shadow-sm shadow-emerald-200' :
                                'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
                            <input type="radio" name="ada_garansi" value="1" class="sr-only"
                                @change="adaGaransi = true" @checked(old('ada_garansi', $procurementPackage->ada_garansi))>
                            Ada Garansi
                        </label>
                    </div>
                    <div x-show="adaGaransi" x-transition.opacity.duration.150ms class="flex gap-1.5 mt-2"
                        @if (!old('ada_garansi', $procurementPackage->ada_garansi)) style="display: none;" @endif>
                        <input type="number" min="1" name="garansi_nilai"
                            value="{{ old('garansi_nilai', $procurementPackage->garansi_nilai) }}"
                            placeholder="Masa" :disabled="!adaGaransi"
                            class="w-1/2 rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-xs">
                        <select name="garansi_satuan" :disabled="!adaGaransi"
                            class="w-1/2 rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 text-xs">
                            @foreach (['hari' => 'Hari', 'bulan' => 'Bulan', 'tahun' => 'Tahun'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan ?? 'hari') == $val)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Layanan Purna Jual --}}
                <div class="rounded-xl border p-3 transition-colors"
                    :class="purnaJual ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-white'">
                    <label class="block text-xs font-semibold text-slate-600 mb-2 flex items-center gap-1.5">
                        <i data-lucide="wrench" class="w-3.5 h-3.5"
                            :class="purnaJual ? 'text-emerald-500' : 'text-slate-300'"></i>
                        Layanan Purna Jual
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        <label
                            class="flex items-center justify-center px-2 py-1.5 rounded-lg border cursor-pointer text-xs font-semibold transition-colors"
                            :class="purnaJual ? 'bg-emerald-600 border-emerald-600 text-white shadow-sm shadow-emerald-200' :
                                'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
                            <input type="radio" name="layanan_purna_jual" value="1" class="sr-only"
                                @change="purnaJual = true" @checked(old('layanan_purna_jual', $procurementPackage->layanan_purna_jual))>
                            Ya, Tersedia
                        </label>
                        <label
                            class="flex items-center justify-center px-2 py-1.5 rounded-lg border cursor-pointer text-xs font-semibold transition-colors"
                            :class="!purnaJual ? 'bg-slate-700 border-slate-700 text-white shadow-sm' :
                                'bg-white border-slate-200 text-slate-500 hover:bg-slate-50'">
                            <input type="radio" name="layanan_purna_jual" value="0" class="sr-only"
                                @change="purnaJual = false" @checked(!old('layanan_purna_jual', $procurementPackage->layanan_purna_jual))>
                            Tidak
                        </label>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="p-4 bg-slate-50/70 rounded-b-xl">
                <p class="text-[11px] text-slate-400 leading-snug">
                    <i data-lucide="info" class="w-3 h-3 inline-block -mt-0.5"></i>
                    Informasi Paket &amp; PPK di kolom kiri terisi otomatis dari master.
                </p>
            </div>

            @once
                <script>
                    function kabidJadwalKirim(init) {
                        const toIso = (d) => {
                            const p = (n) => String(n).padStart(2, '0');
                            return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`;
                        };
                        const fromIso = (s) => new Date(s + 'T00:00:00');
                        const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September',
                            'Oktober', 'November', 'Desember'
                        ];

                        // Turunkan tanggal mulai dari tanggal diterima.
                        // Hitungan inklusif: mulai tgl 1 diterima tgl 7 = 7 hari.
                        let initStart = null;
                        if (init.end && init.nilai > 0 && init.satuan === 'hari') {
                            const d = fromIso(init.end);
                            d.setDate(d.getDate() - (init.nilai - 1));
                            initStart = toIso(d);
                        }

                        const anchor = init.end ? fromIso(init.end) : new Date();

                        return {
                            start: initStart,
                            end: init.end || null,
                            nilai: init.nilai || null,
                            satuan: init.satuan || 'hari',
                            viewYear: anchor.getFullYear(),
                            viewMonth: anchor.getMonth(),
                            todayIso: toIso(new Date()),

                            get monthLabel() {
                                return bulan[this.viewMonth] + ' ' + this.viewYear;
                            },
                            get cells() {
                                const first = new Date(this.viewYear, this.viewMonth, 1);
                                const total = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                                const cells = [];
                                for (let i = 0; i < first.getDay(); i++) cells.push({
                                    d: null,
                                    iso: null
                                });
                                for (let d = 1; d <= total; d++) {
                                    cells.push({
                                        d,
                                        iso: toIso(new Date(this.viewYear, this.viewMonth, d))
                                    });
                                }
                                return cells;
                            },
                            get durasi() {
                                if (!this.start || !this.end) return 0;
                                // Inklusif: hari mulai dan hari diterima ikut dihitung
                                return Math.round((fromIso(this.end) - fromIso(this.start)) / 86400000) + 1;
                            },

                            prevMonth() {
                                if (--this.viewMonth < 0) {
                                    this.viewMonth = 11;
                                    this.viewYear--;
                                }
                            },
                            nextMonth() {
                                if (++this.viewMonth > 11) {
                                    this.viewMonth = 0;
                                    this.viewYear++;
                                }
                            },

                            pick(iso) {
                                if (!this.start || (this.start && this.end)) {
                                    // Mulai pemilihan baru
                                    this.start = iso;
                                    this.end = null;
                                } else if (iso < this.start) {
                                    this.start = iso;
                                } else {
                                    // Boleh sama dengan tanggal mulai (kirim & terima di hari yang sama = 1 hari)
                                    this.end = iso;
                                }
                                this.sync();
                            },

                            sync() {
                                if (this.start && this.end) {
                                    this.nilai = this.durasi;
                                    this.satuan = 'hari';
                                }
                            },

                            syncFromInput() {
                                // Tanggal diterima diubah manual: pertahankan start jika masih valid
                                if (this.start && this.end && this.end < this.start) this.start = null;
                                this.sync();
                            },

                            cellClass(iso) {
                                const isToday = iso === this.todayIso;

                                // Tanggal mulai dikirim: emerald solid
                                if (iso === this.start) {
                                    return 'bg-emerald-600 text-white shadow-sm shadow-emerald-200 font-bold' +
                                        (isToday ? ' ring-2 ring-offset-1 ring-amber-400' : '');
                                }
                                // Tanggal barang diterima: indigo solid
                                if (iso === this.end) {
                                    return 'bg-indigo-600 text-white shadow-sm shadow-indigo-200 font-bold' +
                                        (isToday ? ' ring-2 ring-offset-1 ring-amber-400' : '');
                                }
                                // Rentang di antara: emerald muda
                                if (this.start && this.end && iso > this.start && iso < this.end) {
                                    return 'bg-emerald-100 text-emerald-700' +
                                        (isToday ? ' ring-2 ring-inset ring-amber-400 font-bold' : '');
                                }
                                // Hari ini: aksen amber
                                if (isToday) {
                                    return 'ring-2 ring-inset ring-amber-400 bg-amber-50 text-amber-700 font-bold hover:bg-amber-100';
                                }
                                return 'text-slate-600 hover:bg-slate-100';
                            },

                            displayDate(iso) {
                                const d = fromIso(iso);
                                return d.getDate() + ' ' + bulan[d.getMonth()].slice(0, 3) + ' ' + d.getFullYear();
                            },
                        };
                    }
                </script>
            @endonce
        </form>
    </div>
</div>
