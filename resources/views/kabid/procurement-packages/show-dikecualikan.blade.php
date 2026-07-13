@component('layouts.kdmp')
@section('title', 'Detail Paket (Dikecualikan)')

@php
    $package = $procurementPackage->package;
    $money = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $locked = $procurementPackage->status === 'complete';
    $totalRealisasi = $procurementPackage->externalRecords->sum('nilai_kontrak');
    $tanggal = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '-';
    $rolePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'kabid';
@endphp

<div class="space-y-6"
    x-data="{ tipe: @js($procurementPackage->dikecualikan_type ?? ''), showTx: false, showKw: false }">
    <x-ui.toast />

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 min-w-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-sky-500"></i>{{ $package->id_rup ?? '-' }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-violet-700 bg-violet-50 border border-violet-100 rounded-lg">
                <i data-lucide="file-warning" class="w-3.5 h-3.5"></i>Dikecualikan
            </span>
            @if($locked)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>Selesai
                </span>
            @endif
        </div>
        <a href="{{ route($rolePrefix . '.dikecualikan.index') }}"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali
        </a>
    </div>

    {{-- Info paket --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wide flex items-center gap-2">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-blue-500"></i>Informasi Utama
                </h3>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                <div class="px-5 py-3 flex justify-between gap-3"><span class="text-slate-400">ID RUP</span><span class="font-bold text-slate-800 font-mono">{{ $package->id_rup ?? '-' }}</span></div>
                <div class="px-5 py-3 flex justify-between gap-3"><span class="text-slate-400">Nama Paket</span><span class="font-semibold text-slate-700 text-right">{{ $package->nama_paket }}</span></div>
                <div class="px-5 py-3 flex justify-between gap-3"><span class="text-slate-400 shrink-0">Sub Kegiatan</span><span class="font-semibold text-slate-700 text-right">{{ $package->subActivity?->kode }} {{ $package->subActivity ? '- ' . $package->subActivity->nama : '-' }}</span></div>
                <div class="px-5 py-3 flex justify-between gap-3"><span class="text-slate-400">Pagu</span><span class="font-black text-emerald-600">{{ $money($package->pagu) }}</span></div>
            </div>
        </section>
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xs font-black text-slate-700 uppercase tracking-wide flex items-center gap-2">
                    <i data-lucide="tags" class="w-3.5 h-3.5 text-emerald-500"></i>Klasifikasi &amp; Realisasi
                </h3>
            </div>
            <div class="divide-y divide-slate-100 text-sm">
                <div class="px-5 py-3 flex justify-between gap-3"><span class="text-slate-400">Jenis Pengadaan</span><span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">{{ $package->jenis_pengadaan ?? '-' }}</span></div>
                <div class="px-5 py-3 flex justify-between gap-3"><span class="text-slate-400">Metode Pengadaan</span><span class="font-semibold text-slate-700">{{ $package->metode_pengadaan ?? '-' }}</span></div>
                <div class="px-5 py-3 flex justify-between gap-3"><span class="text-slate-400">Total Realisasi</span><span class="font-black {{ $totalRealisasi > (float) $package->pagu ? 'text-rose-600' : 'text-slate-800' }}">{{ $money($totalRealisasi) }}</span></div>
            </div>
        </section>
    </div>

    {{-- Pengaturan Dikecualikan --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="settings-2" class="w-4 h-4 text-amber-500"></i>Pengaturan Dikecualikan
            </h2>
            <p class="text-sm text-slate-500 mt-1">Pilih tipe pencatatan pengadaan yang dikecualikan.</p>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.dikecualikan.update', $procurementPackage) }}" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                @csrf
                @method('PATCH')
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tipe Dikecualikan</label>
                    <select name="dikecualikan_type" x-model="tipe" required {{ $locked ? 'disabled' : '' }}
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500 disabled:bg-slate-50">
                        <option value="">-- Pilih --</option>
                        <option value="di_luar_sistem">Di Luar Sistem</option>
                        <option value="di_dalam_sistem">Di Dalam Sistem</option>
                    </select>
                </div>
                @unless($locked)
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow-sm shadow-amber-200 transition-colors shrink-0">
                        <i data-lucide="save" class="w-4 h-4"></i>Simpan Tipe
                    </button>
                @endunless
            </form>
        </div>
    </section>

    {{-- ============ DI LUAR SISTEM ============ --}}
    <section x-show="tipe === 'di_luar_sistem'" style="display:none;" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="list-checks" class="w-4 h-4 text-blue-500"></i>Riwayat Transaksi Eksternal
            </h2>
            @unless($locked)
                <button type="button" @click="showTx = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>Tambah Transaksi
                </button>
            @endunless
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Surat Pesanan</th>
                        <th class="px-4 py-3 text-left">Tagihan</th>
                        <th class="px-4 py-3 text-left">BAST</th>
                        <th class="px-4 py-3 text-left">BAP</th>
                        <th class="px-4 py-3 text-left">Kwitansi</th>
                        <th class="px-4 py-3 text-right">Nilai</th>
                        @unless($locked)<th class="px-4 py-3 text-center w-14">Aksi</th>@endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($procurementPackage->externalRecords as $index => $record)
                        <tr class="hover:bg-slate-50/50 align-top">
                            <td class="px-4 py-3 text-center text-slate-400">{{ $index + 1 }}</td>
                            @foreach(['surat_pesanan' => 'surat_pesanan', 'surat_tagihan' => 'surat_tagihan', 'bast' => 'bast', 'bap' => 'bap', 'kwitansi' => 'kwitansi'] as $noKey => $prefix)
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <p class="text-xs text-slate-700"><span class="text-slate-400">No:</span> {{ $record->{$prefix.'_no'} ?? '-' }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $tanggal($record->{$prefix.'_tgl'}) }}</p>
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-right font-bold text-emerald-700 whitespace-nowrap">{{ $money($record->nilai_kontrak) }}</td>
                            @unless($locked)
                                <td class="px-4 py-3 text-center">
                                    <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.destroy', [$procurementPackage, $record]) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-md transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </td>
                            @endunless
                        </tr>
                    @empty
                        <tr><td colspan="{{ $locked ? 7 : 8 }}" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat transaksi.</td></tr>
                    @endforelse
                </tbody>
                @if($procurementPackage->externalRecords->count() > 0)
                    <tfoot class="bg-slate-50 font-bold">
                        <tr>
                            <td colspan="6" class="px-4 py-3 text-right text-slate-700">Total Realisasi</td>
                            <td class="px-4 py-3 text-right text-blue-700 whitespace-nowrap">{{ $money($totalRealisasi) }}</td>
                            @unless($locked)<td></td>@endunless
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 flex justify-end">
            @include('kabid.procurement-packages.partials.dikecualikan-selesai', ['procurementPackage' => $procurementPackage, 'locked' => $locked])
        </div>
    </section>

    {{-- ============ DI DALAM SISTEM ============ --}}
    <section x-show="tipe === 'di_dalam_sistem'" style="display:none;" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-emerald-500"></i>Modul Kwitansi
            </h2>
            @unless($locked)
                <button type="button" @click="showKw = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>Buat Kwitansi
                </button>
            @endunless
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3 text-left">Nomor Kwitansi</th>
                        <th class="px-4 py-3 text-center">Tanggal</th>
                        <th class="px-4 py-3 text-right">Nilai Kontrak</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($procurementPackage->externalRecords as $index => $record)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 text-center text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-700">{{ $record->kwitansi_no ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $tanggal($record->kwitansi_tgl) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700 whitespace-nowrap">{{ $money($record->nilai_kontrak) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="printKwitansi('{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.print', [$procurementPackage, $record]) }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors" title="Cetak">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak
                                    </button>
                                    @unless($locked)
                                        <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.destroy', [$procurementPackage, $record]) }}" method="POST" onsubmit="return confirm('Hapus kwitansi ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-md transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat kwitansi.</td></tr>
                    @endforelse
                </tbody>
                @if($procurementPackage->externalRecords->count() > 0)
                    <tfoot class="bg-slate-50 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-slate-700">Total Realisasi</td>
                            <td class="px-4 py-3 text-right text-emerald-700 whitespace-nowrap">{{ $money($totalRealisasi) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-100 flex justify-end">
            @include('kabid.procurement-packages.partials.dikecualikan-selesai', ['procurementPackage' => $procurementPackage, 'locked' => $locked])
        </div>
    </section>

    {{-- Placeholder saat tipe belum dipilih --}}
    <div x-show="!tipe" style="display:none;" class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-10 text-center">
        <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mx-auto mb-3 text-slate-300">
            <i data-lucide="file-warning" class="w-7 h-7"></i>
        </div>
        <p class="font-bold text-slate-600">Pilih tipe dikecualikan terlebih dahulu.</p>
        <p class="text-sm text-slate-400 mt-1">Di Luar Sistem (riwayat transaksi) atau Di Dalam Sistem (kwitansi).</p>
    </div>

    {{-- ============ MODAL TAMBAH TRANSAKSI ============ --}}
    <div x-show="showTx" style="display:none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        @keydown.escape.window="showTx = false">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showTx = false"></div>
        <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col" style="max-height:90vh;"
            x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.store', $procurementPackage) }}" method="POST" class="flex flex-col min-h-0">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 bg-blue-50/60 flex items-center justify-between shrink-0">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2"><i data-lucide="plus-circle" class="w-4 h-4 text-blue-500"></i>Tambah Transaksi Eksternal</h3>
                    <button type="button" @click="showTx = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <div class="p-5 space-y-4 overflow-y-auto min-h-0">
                    @foreach([['surat_pesanan_no','No. Surat Pesanan','surat_pesanan_tgl','Tanggal Surat Pesanan'],['surat_tagihan_no','No. Surat Tagihan','surat_tagihan_tgl','Tanggal Surat Tagihan'],['bast_no','No. BAST','bast_tgl','Tanggal BAST'],['bap_no','No. BAP','bap_tgl','Tanggal BAP'],['kwitansi_no','No. Kwitansi','kwitansi_tgl','Tanggal Kwitansi']] as $pair)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ $pair[1] }}</label>
                                <input type="text" name="{{ $pair[0] }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">{{ $pair[3] }}</label>
                                <input type="date" name="{{ $pair[2] }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Kontrak / Tagihan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="nilai_kontrak" required class="w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2 shrink-0">
                    <button type="button" @click="showTx = false" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm"><i data-lucide="save" class="w-4 h-4"></i>Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ MODAL BUAT KWITANSI ============ --}}
    <div x-show="showKw" style="display:none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        @keydown.escape.window="showKw = false">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showKw = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
            x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-external-records.store', $procurementPackage) }}" method="POST">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 bg-emerald-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2"><i data-lucide="receipt" class="w-4 h-4 text-emerald-500"></i>Buat Kwitansi Baru</h3>
                    <button type="button" @click="showKw = false" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">No. Kwitansi</label>
                        <input type="text" name="kwitansi_no" placeholder="Contoh: 001/PERKIMPLH-C/2026" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Kwitansi</label>
                        <input type="date" name="kwitansi_tgl" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai Kontrak / Uang Sejumlah (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="nilai_kontrak" required class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="showKw = false" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm"><i data-lucide="save" class="w-4 h-4"></i>Simpan Kwitansi</button>
                </div>
            </form>
        </div>
    </div>

    <iframe id="print_iframe" style="display:none;"></iframe>
</div>

<script>
    function printKwitansi(url) { document.getElementById('print_iframe').src = url; }
</script>
@endcomponent
