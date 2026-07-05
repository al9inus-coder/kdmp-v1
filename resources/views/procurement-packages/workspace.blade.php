@component('layouts.kdmp')
@section('title', 'Workspace Persiapan Pengadaan')

<div class="space-y-6" x-data="{ step: 1 }">
    <!-- Header KDMP Style -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                <i data-lucide="hash" class="w-3.5 h-3.5"></i> <p class="font-semibold text-slate-800">{{ $procurementPackage->package->id_rup ?? '-' }}</p>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">      
                 <i data-lucide="package" class="w-3.5 h-3.5"></i><p class="font-medium text-slate-800 leading-snug">{{ $procurementPackage->package->nama_paket ?? '-' }}</p>
            </span>
        </div>
    </div>

    <!-- Global Workflow Progress -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 overflow-x-auto">
        <div class="flex items-center justify-between min-w-max">
            
            <!-- Step 1 (Active) -->
            <div class="flex items-center relative">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-blue-600 text-white shadow-md ring-4 ring-blue-50 z-10 shrink-0">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                </div>
                <div class="ml-3 mr-4 z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600">Tahap 1</p>
                    <p class="text-sm font-bold text-slate-800">Persiapan Pengadaan</p>
                </div>
            </div>
            
            <!-- Connecting Line -->
            <div class="flex-1 h-1 bg-slate-100 mx-2 relative shrink-0 min-w-[40px] rounded-full overflow-hidden">
                <div class="absolute top-0 left-0 h-full bg-blue-500 w-1/3 rounded-full"></div>
            </div>
            
            <!-- Step 2 -->
            <div class="flex items-center relative opacity-50">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 text-slate-400 ring-4 ring-white z-10 shrink-0">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
                <div class="ml-3 mr-4 z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Tahap 2</p>
                    <p class="text-sm font-semibold text-slate-700">Pemilihan Penyedia</p>
                </div>
            </div>

            <!-- Connecting Line -->
            <div class="flex-1 h-1 bg-slate-100 mx-2 relative shrink-0 min-w-[40px] rounded-full"></div>

            <!-- Step 3 -->
            <div class="flex items-center relative opacity-50">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 text-slate-400 ring-4 ring-white z-10 shrink-0">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                </div>
                <div class="ml-3 mr-4 z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Tahap 3</p>
                    <p class="text-sm font-semibold text-slate-700">Pelaksanaan Kontrak</p>
                </div>
            </div>

            <!-- Connecting Line -->
            <div class="flex-1 h-1 bg-slate-100 mx-2 relative shrink-0 min-w-[40px] rounded-full"></div>

            <!-- Step 4 -->
            <div class="flex items-center relative opacity-50">
                <div class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-100 text-slate-400 ring-4 ring-white z-10 shrink-0">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <div class="ml-3 pr-2 z-10">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Tahap 4</p>
                    <p class="text-sm font-semibold text-slate-700">Pembayaran (Selesai)</p>
                </div>
            </div>
            
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        <!-- Sidebar Navigation (Floating Style) -->
        <div class="w-full lg:w-60 shrink-0 sticky top-24 z-10">
            <div class="bg-white border border-slate-200 shadow-xl shadow-slate-200/50 rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-slate-300/60">
                <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Alur Persiapan</h3>
                </div>
                <div class="p-2 space-y-1">
                    @php
                        $steps = [
                            1 => ['title' => 'Informasi Kontrak', 'icon' => 'file-text'],
                            2 => ['title' => 'Barang / Jasa', 'icon' => 'package'],
                            3 => ['title' => 'Spesifikasi Teknis', 'icon' => 'check-circle'],
                            4 => ['title' => 'Referensi Harga', 'icon' => 'tag'],
                            5 => ['title' => 'Surat Permohonan', 'icon' => 'mail'],
                            6 => ['title' => 'Selesaikan', 'icon' => 'flag']
                        ];
                    @endphp
                    
                    @foreach($steps as $idx => $s)
                    <button 
                        @click="step = {{ $idx }}"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-colors text-left"
                        :class="step === {{ $idx }} ? 'bg-blue-50 text-blue-700 font-semibold' : (step > {{ $idx }} ? 'text-slate-700 hover:bg-slate-50 font-medium' : 'text-slate-500 hover:bg-slate-50 font-medium')"
                    >
                        <div class="flex items-center gap-3">
                            <div 
                                class="w-8 h-8 rounded-full flex items-center justify-center transition-all duration-300 shadow-sm border shrink-0"
                                :class="step === {{ $idx }} ? 'bg-blue-600 text-white border-blue-700 shadow-blue-500/20' : (step > {{ $idx }} ? 'bg-emerald-500 text-white border-emerald-600 shadow-emerald-500/20' : 'bg-white text-slate-400 border-slate-200')"
                            >
                                <span x-show="step <= {{ $idx }}" class="text-xs font-bold">{{ $idx }}</span>
                                <span x-show="step > {{ $idx }}" style="display: none;">
                                    <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                                </span>
                            </div>
                            <span :class="step === {{ $idx }} ? 'font-bold text-blue-700' : (step > {{ $idx }} ? 'font-semibold text-slate-700' : 'font-medium text-slate-500')">{{ $s['title'] }}</span>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content (KDMP Card Style) -->
        <div class="flex-1 w-full flex flex-col min-h-[600px]">
            <x-ui.card padding="none" class="flex-1 flex flex-col overflow-hidden">
                <div class="flex-1">
                    <div x-show="step === 1">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i data-lucide="file-text" class="w-5 h-5 text-slate-400"></i>
                                Informasi Paket dan Kontrak
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">Lengkapi data Pejabat Pembuat Komitmen dan detail kontrak untuk paket ini.</p>
                        </div>
                        <div class="p-6">
                            <form id="form-step-1">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    
                                    <!-- Informasi Paket & PPK -->
                                    <div class="space-y-6">
                                        <div>
                                            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2">
                                                <i data-lucide="box" class="w-4 h-4 text-blue-500"></i> Informasi Paket
                                            </h3>
                                            
                                            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                                                <div class="grid grid-cols-2 border-b border-slate-100">
                                                    <div class="p-4 border-r border-slate-100 bg-slate-50/50">
                                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">ID RUP</p>
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                                                                <i data-lucide="hash" class="w-3.5 h-3.5"></i>
                                                            </div>
                                                            <p class="font-semibold text-slate-800">{{ $procurementPackage->package->id_rup ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="p-4 bg-slate-50/50">
                                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pagu Anggaran</p>
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-6 h-6 rounded bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                                                <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                                            </div>
                                                            <p class="font-bold text-emerald-600">Rp {{ number_format($procurementPackage->package->pagu ?? 0, 0, ',', '.') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="p-4 border-b border-slate-100">
                                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Paket</p>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-6 h-6 rounded bg-indigo-100 flex items-center justify-center text-indigo-600 shrink-0 mt-0.5">
                                                            <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <p class="font-medium text-slate-800 leading-snug">{{ $procurementPackage->package->nama_paket ?? '-' }}</p>
                                                    </div>
                                                </div>
                                                <div class="p-4">
                                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sub Kegiatan</p>
                                                    <div class="flex items-start gap-2">
                                                        <div class="w-6 h-6 rounded bg-amber-100 flex items-center justify-center text-amber-600 shrink-0 mt-0.5">
                                                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                                                        </div>
                                                        <p class="text-sm font-medium text-slate-600 leading-snug">{{ $procurementPackage->package->subActivity->nama ?? '-' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 flex items-center gap-2 pt-2 border-t border-slate-100">
                                                <i data-lucide="user" class="w-4 h-4 text-blue-500"></i> Data PPK
                                            </h3>
                                            
                                            <div class="bg-slate-50 border border-slate-200 rounded-xl shadow-sm overflow-hidden">
                                                <div class="p-4 border-b border-slate-200">
                                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Lengkap PPK</p>
                                                    <p class="font-medium text-slate-800">{{ $procurementPackage->nama_ppk ?? '-' }}</p>
                                                    <input type="hidden" name="nama_ppk" value="{{ old('nama_ppk', $procurementPackage->nama_ppk) }}">
                                                </div>
                                                <div class="grid grid-cols-2">
                                                    <div class="p-4 border-r border-slate-200">
                                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">NIP</p>
                                                        <p class="font-medium text-slate-800">{{ $procurementPackage->nip_ppk ?? '-' }}</p>
                                                        <input type="hidden" name="nip_ppk" value="{{ old('nip_ppk', $procurementPackage->nip_ppk) }}">
                                                    </div>
                                                    <div class="p-4">
                                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">User PPK</p>
                                                        <p class="font-medium text-slate-800">{{ $procurementPackage->user_ppk ?? '-' }}</p>
                                                        <input type="hidden" name="user_ppk" value="{{ old('user_ppk', $procurementPackage->user_ppk) }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Informasi Kontrak -->
                                    <div class="space-y-4">
                                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-4 border-b border-slate-100 pb-2">Detail Kontrak & Pelaksanaan</h3>
                                        
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-slate-700">Jenis Kontrak</label>
                                            <select name="jenis_kontrak" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                <option value="">-- Pilih Jenis Kontrak --</option>
                                                @foreach(['Harga Satuan', 'Lump Sum'] as $jenis)
                                                    <option value="{{ $jenis }}" @selected(old('jenis_kontrak', $procurementPackage->jenis_kontrak) == $jenis)>
                                                        {{ $jenis }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-slate-700">Tanggal Barang Diterima / Pekerjaan Selesai</label>
                                            <input type="date" name="tanggal_barang_diterima" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('tanggal_barang_diterima', $procurementPackage->tanggal_barang_diterima) }}">
                                        </div>
                                        
                                        <div class="space-y-1">
                                            <label class="text-sm font-medium text-slate-700">
                                                @if($procurementPackage->package->jenis_pengadaan == 'Barang')
                                                    Jangka Waktu Pengiriman Barang
                                                @else
                                                    Jangka Waktu Pelaksanaan Pekerjaan
                                                @endif
                                            </label>
                                            <div class="flex gap-2">
                                                <input type="number" name="jangka_waktu_nilai" class="w-2/3 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('jangka_waktu_nilai', $procurementPackage->jangka_waktu_nilai) }}" placeholder="Angka">
                                                <select name="jangka_waktu_satuan" class="w-1/3 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                    <option value="hari" @selected(old('jangka_waktu_satuan', $procurementPackage->jangka_waktu_satuan) == 'hari')>Hari</option>
                                                    <option value="bulan" @selected(old('jangka_waktu_satuan', $procurementPackage->jangka_waktu_satuan) == 'bulan')>Bulan</option>
                                                    <option value="tahun" @selected(old('jangka_waktu_satuan', $procurementPackage->jangka_waktu_satuan) == 'tahun')>Tahun</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 mt-4 pt-4 border-t border-slate-100">
                                            <label class="text-sm font-medium text-slate-700">Garansi</label>
                                            <div class="flex items-center gap-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="ada_garansi" value="0" class="text-blue-600 focus:ring-blue-500 border-slate-300" {{ !$procurementPackage->ada_garansi ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-slate-700">Tidak Ada</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="ada_garansi" value="1" class="text-blue-600 focus:ring-blue-500 border-slate-300" {{ $procurementPackage->ada_garansi ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-slate-700">Ada Garansi</span>
                                                </label>
                                            </div>
                                            
                                            <div class="flex gap-2 mt-2">
                                                <input type="number" name="garansi_nilai" class="w-2/3 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('garansi_nilai', $procurementPackage->garansi_nilai) }}" placeholder="Masa Garansi">
                                                <select name="garansi_satuan" class="w-1/3 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                    <option value="hari" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) == 'hari')>Hari</option>
                                                    <option value="bulan" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) == 'bulan')>Bulan</option>
                                                    <option value="tahun" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) == 'tahun')>Tahun</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 mt-4 pt-4 border-t border-slate-100">
                                            <label class="text-sm font-medium text-slate-700">Layanan Purna Jual</label>
                                            <div class="flex items-center gap-4">
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="layanan_purna_jual" value="1" class="text-blue-600 focus:ring-blue-500 border-slate-300" {{ $procurementPackage->layanan_purna_jual ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-slate-700">Ya</span>
                                                </label>
                                                <label class="inline-flex items-center">
                                                    <input type="radio" name="layanan_purna_jual" value="0" class="text-blue-600 focus:ring-blue-500 border-slate-300" {{ !$procurementPackage->layanan_purna_jual ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm text-slate-700">Tidak</span>
                                                </label>
                                            </div>
                                        </div>


                                    </div>
                                    
                                </div>
                            </form>
                        </div>
                    </div>

                    <div x-show="step === 2" style="display:none;">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i data-lucide="package" class="w-5 h-5 text-indigo-500"></i>
                                    Daftar Barang & Jasa
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">Kelola item pengadaan yang akan dibuatkan spesifikasinya.</p>
                            </div>
                            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Item
                            </button>
                        </div>
                        <div class="p-0 overflow-x-auto">
                            <table class="w-full text-sm text-left whitespace-nowrap">
                                <thead class="text-xs text-slate-500 bg-slate-50 border-b border-slate-200 uppercase font-semibold">
                                    <tr>
                                        <th class="px-4 py-3 text-center">No</th>
                                        <th class="px-4 py-3">Nama Barang/Jasa</th>
                                        <th class="px-4 py-3">Spesifikasi</th>
                                        <th class="px-4 py-3 text-center">Volume</th>
                                        <th class="px-4 py-3 text-center">Satuan</th>
                                        <th class="px-4 py-3 text-right">Harga DPA</th>
                                        <th class="px-4 py-3 text-center">PDN</th>
                                        <th class="px-4 py-3 text-center">TKDN (%)</th>
                                        <th class="px-4 py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @if($procurementPackage->technicalSpecification && $procurementPackage->technicalSpecification->items->count())
                                        @foreach($procurementPackage->technicalSpecification->items as $index => $item)
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="px-4 py-3 text-center text-slate-500">{{ $index + 1 }}</td>
                                            <td class="px-4 py-3">
                                                <input type="text" name="items[{{ $index + 1 }}][nama_barang_jasa]" value="{{ $item->nama_barang_jasa }}" class="w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3">
                                                <textarea name="items[{{ $index + 1 }}][spesifikasi]" rows="1" class="w-full rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ $item->spesifikasi }}</textarea>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" name="items[{{ $index + 1 }}][volume]" value="{{ $item->volume }}" class="w-20 text-center rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" name="items[{{ $index + 1 }}][satuan]" value="{{ $item->satuan }}" class="w-24 text-center rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" name="items[{{ $index + 1 }}][harga_satuan_dpa]" value="{{ number_format((float) ($item->harga_satuan_dpa ?? 0), 0, ',', '.') }}" class="w-32 text-right rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <input type="checkbox" name="items[{{ $index + 1 }}][pdn]" value="1" @checked($item->pdn) class="rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" name="items[{{ $index + 1 }}][tkdn]" value="{{ $item->tkdn }}" class="w-20 text-center rounded-md border-slate-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <button type="button" class="p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors" title="Hapus Item">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="px-4 py-12 text-center text-slate-500">
                                                <div class="flex flex-col items-center justify-center">
                                                    <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-3">
                                                        <i data-lucide="package-open" class="w-6 h-6 text-slate-400"></i>
                                                    </div>
                                                    <p class="font-medium">Belum ada barang/jasa yang ditambahkan.</p>
                                                    <p class="text-xs mt-1">Silakan klik tombol "Tambah Item" di kanan atas.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="step === 3" style="display:none;" class="h-full flex flex-col bg-white">
                        @include('procurement-packages.partials.technical-specification-form')
                    </div>

                    <!-- Step 4: Referensi Harga -->
                    <div x-show="step === 4" style="display:none;" class="h-full flex flex-col">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i data-lucide="tag" class="w-5 h-5 text-amber-500"></i>
                                    Referensi Harga
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">Periksa dan sesuaikan Referensi Harga sebelum dicetak.</p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="document.querySelector('#iframe-step-4').contentWindow.print()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
                                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak PDF
                                </button>
                            </div>
                        </div>
                        
                        <div class="p-0 flex-1 bg-slate-200">
                            <iframe id="iframe-step-4" src="{{ route('procurement-packages.price-references.print', $procurementPackage) }}?embed=1&t={{ time() }}" class="w-full border-0 block" style="height: 100%; min-height: 800px;"></iframe>
                        </div>
                    </div>

                    <!-- Step 5: Surat Permohonan -->
                    <div x-show="step === 5" style="display:none;" class="h-full flex flex-col">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                    <i data-lucide="mail" class="w-5 h-5 text-rose-500"></i>
                                    Surat Permohonan
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">Periksa dan sesuaikan Surat Permohonan sebelum dicetak.</p>
                            </div>
                            <div class="flex gap-2">
                                @if($procurementPackage->procurementRequest)
                                <button type="button" onclick="document.querySelector('#iframe-step-5').contentWindow.print()" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
                                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak PDF
                                </button>
                                @endif
                            </div>
                        </div>
                        
                        <div class="p-0 flex-1 bg-slate-200">
                            @if($procurementPackage->procurementRequest)
                                <iframe id="iframe-step-5" src="{{ route('procurement-packages.procurement-request.print', $procurementPackage) }}?embed=1&t={{ time() }}" class="w-full border-0 block" style="height: 100%; min-height: 800px;"></iframe>
                            @else
                                <div class="flex items-center justify-center h-full text-slate-500" style="min-height: 800px;">
                                    Surat Permohonan belum dibuat.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Step 6: Selesaikan -->
                    <div x-show="step === 6" style="display:none;">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i data-lucide="flag" class="w-5 h-5 text-slate-400"></i>
                                Selesaikan
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">Lengkapi data untuk Selesaikan pada form di bawah ini.</p>
                        </div>
                        <div class="p-6">
                            <div class="border-2 border-dashed border-slate-200 rounded-xl p-12 flex flex-col items-center justify-center text-center bg-slate-50/50">
                                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center mb-4 text-slate-400">
                                    <i data-lucide="flag" class="w-8 h-8"></i>
                                </div>
                                <h3 class="text-md font-bold text-slate-700 mb-1">Area Kerja Selesaikan</h3>
                                <p class="text-sm text-slate-500 max-w-sm">Di sini akan memuat form input standar dari KDMP.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-slate-200 p-4 bg-slate-50/50 flex justify-between items-center mt-auto">
                    <button type="button" @click="step > 1 ? step-- : null" :disabled="step === 1" class="inline-flex items-center justify-center font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed px-4 py-2 text-sm rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-slate-900 shadow-sm">
                        <i data-lucide="chevron-left" class="w-4 h-4 mr-1"></i> Sebelumnya
                    </button>
                    
                    <button type="button" @click="step < 6 ? step++ : null" class="inline-flex items-center justify-center font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed px-4 py-2 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-sm">
                        <span x-text="step === 6 ? 'Selesaikan Persiapan' : 'Simpan & Lanjut'"></span>
                        <i data-lucide="chevron-right" class="w-4 h-4 ml-1" x-show="step < 6"></i>
                    </button>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:initialized', () => {
        Alpine.effect(() => {
            setTimeout(() => {
                if(typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }, 50);
        });
    });
</script>
@endcomponent
