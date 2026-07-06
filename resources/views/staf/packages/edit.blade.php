@component('layouts.kdmp')
@section('title', 'Edit Paket Pengadaan (Staf)')

<div class="space-y-6">

    @if(session('success'))
        <div id="success-toast" class="fixed top-20 right-6 z-50 bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl shadow-xl flex items-center gap-3 transition-opacity duration-500">
            <div class="bg-emerald-100 p-2 rounded-full">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm">Berhasil!</h4>
                <p class="text-xs text-emerald-600 mt-0.5">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="document.getElementById('success-toast').style.display='none'" class="ml-4 text-emerald-400 hover:text-emerald-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.getElementById('success-toast');
                if(toast) {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.style.display = 'none', 500);
                }
            }, 4000);
        </script>
    @endif
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                Edit <span class="text-indigo-600">Paket Pengadaan</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui informasi paket pengadaan di bawah ini.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('staf.packages.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <form action="{{ route('packages.update', $package) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="source" value="staf">

        <div class="space-y-6">
            <!-- Card: Informasi Paket -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="box" class="w-5 h-5 text-indigo-500"></i>
                        Informasi Paket
                    </h3>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="fiscal_year_id" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="calendar-days" class="w-4 h-4 text-slate-400"></i> Tahun Anggaran <span class="text-rose-500">*</span>
                        </label>
                        <select id="fiscal_year_id" name="fiscal_year_id" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('fiscal_year_id') border-rose-500 @enderror">
                            <option value="">-- Pilih Tahun Anggaran --</option>
                            @foreach($fiscalYears as $fiscalYear)
                                <option value="{{ $fiscalYear->id }}" @selected(old('fiscal_year_id', $package->fiscal_year_id) == $fiscalYear->id)>{{ $fiscalYear->tahun }} @if($fiscalYear->is_active) (Aktif) @endif</option>
                            @endforeach
                        </select>
                        @error('fiscal_year_id') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="id_rup" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="hash" class="w-4 h-4 text-slate-400"></i> ID RUP <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="id_rup" name="id_rup" value="{{ old('id_rup', $package->id_rup) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm @error('id_rup') border-rose-500 @enderror" placeholder="Contoh: 45678912">
                        @error('id_rup') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="nama_paket" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="file-signature" class="w-4 h-4 text-slate-400"></i> Nama Paket <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nama_paket" name="nama_paket" value="{{ old('nama_paket', $package->nama_paket) }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm @error('nama_paket') border-rose-500 @enderror" placeholder="Contoh: Pengadaan Komputer Kantor...">
                        @error('nama_paket') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="pagu_display" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="banknote" class="w-4 h-4 text-slate-400"></i> Pagu Anggaran <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <span class="text-slate-500 text-sm font-bold">Rp</span>
                            </div>
                            <!-- Hidden input for actual numeric value -->
                            <input type="hidden" id="pagu" name="pagu" value="{{ old('pagu', (int)$package->pagu) }}">
                            <!-- Visible display input -->
                            <input type="text" id="pagu_display" required value="{{ old('pagu', (int)$package->pagu) ? number_format((float)old('pagu', (int)$package->pagu), 0, ',', '.') : '' }}" class="w-full pl-12 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm font-bold text-slate-800 @error('pagu') border-rose-500 @enderror" placeholder="0">
                        </div>
                        @error('pagu') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Card: Klasifikasi Paket -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="tags" class="w-5 h-5 text-indigo-500"></i>
                        Klasifikasi Paket
                    </h3>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="sub_activity_id" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="network" class="w-4 h-4 text-slate-400"></i> Sub Kegiatan
                        </label>
                        <select id="sub_activity_id" name="sub_activity_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('sub_activity_id') border-rose-500 @enderror">
                            <option value="">-- Pilih Sub Kegiatan --</option>
                            @foreach($subActivities as $subActivity)
                                <option value="{{ $subActivity->id }}" @selected(old('sub_activity_id', $package->sub_activity_id) == $subActivity->id)>{{ $subActivity->kode }} - {{ $subActivity->nama }}</option>
                            @endforeach
                        </select>
                        @error('sub_activity_id') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="account_id" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="wallet" class="w-4 h-4 text-slate-400"></i> Rekening Belanja
                        </label>
                        <select id="account_id" name="account_id" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('account_id') border-rose-500 @enderror">
                            <option value="">-- Pilih Rekening --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('account_id', $package->account_id) == $account->id)>{{ $account->kode }} - {{ $account->nama }}</option>
                            @endforeach
                        </select>
                        @error('account_id') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="jenis_pengadaan" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="layers" class="w-4 h-4 text-slate-400"></i> Jenis Pengadaan
                        </label>
                        <select id="jenis_pengadaan" name="jenis_pengadaan" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('jenis_pengadaan') border-rose-500 @enderror">
                            <option value="">-- Pilih Jenis Pengadaan --</option>
                            <option value="Barang" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Barang')>Pengadaan Barang</option>
                            <option value="Jasa Konsultansi" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Jasa Konsultansi')>Jasa Konsultansi</option>
                            <option value="Jasa Lainnya" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Jasa Lainnya')>Jasa Lainnya</option>
                            <option value="Pekerjaan Konstruksi" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Pekerjaan Konstruksi')>Pekerjaan Konstruksi</option>
                            <option value="Swakelola" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Swakelola')>Swakelola</option>
                        </select>
                        @error('jenis_pengadaan') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="metode_pengadaan" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="shopping-cart" class="w-4 h-4 text-slate-400"></i> Metode Pengadaan
                        </label>
                        <select id="metode_pengadaan" name="metode_pengadaan" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('metode_pengadaan') border-rose-500 @enderror">
                            <option value="">-- Pilih Metode --</option>
                            <option value="E-Purchasing" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'E-Purchasing')>E-Purchasing</option>
                            <option value="Pengadaan Langsung" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'Pengadaan Langsung')>Pengadaan Langsung</option>
                            <option value="Dikecualikan" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'Dikecualikan')>Dikecualikan</option>
                        </select>
                        @error('metode_pengadaan') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Card: Jadwal Pelaksanaan -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="calendar" class="w-5 h-5 text-indigo-500"></i>
                        Jadwal Pelaksanaan
                    </h3>
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp

                    <div class="space-y-2">
                        <label for="pemilihan_mulai_bulan" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="calendar-clock" class="w-4 h-4 text-slate-400"></i> Pemilihan Mulai
                        </label>
                        <select name="pemilihan_mulai_bulan" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('pemilihan_mulai_bulan') border-rose-500 @enderror">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" @selected(old('pemilihan_mulai_bulan', $package->pemilihan_mulai_bulan) == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('pemilihan_mulai_bulan') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="pemilihan_selesai_bulan" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="calendar-check" class="w-4 h-4 text-slate-400"></i> Pemilihan Selesai
                        </label>
                        <select name="pemilihan_selesai_bulan" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('pemilihan_selesai_bulan') border-rose-500 @enderror">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" @selected(old('pemilihan_selesai_bulan', $package->pemilihan_selesai_bulan) == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('pemilihan_selesai_bulan') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="kontrak_mulai_bulan" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="handshake" class="w-4 h-4 text-slate-400"></i> Kontrak Mulai
                        </label>
                        <select name="kontrak_mulai_bulan" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('kontrak_mulai_bulan') border-rose-500 @enderror">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" @selected(old('kontrak_mulai_bulan', $package->kontrak_mulai_bulan) == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kontrak_mulai_bulan') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="kontrak_selesai_bulan" class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <i data-lucide="flag" class="w-4 h-4 text-slate-400"></i> Kontrak Selesai
                        </label>
                        <select name="kontrak_selesai_bulan" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm appearance-none @error('kontrak_selesai_bulan') border-rose-500 @enderror">
                            <option value="">-- Pilih Bulan --</option>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}" @selected(old('kontrak_selesai_bulan', $package->kontrak_selesai_bulan) == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('kontrak_selesai_bulan') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                </div>
            </div>
            
            <!-- Floating Action Bar / Submit Area -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-4 flex items-center justify-between mt-6">
                <p class="text-sm text-slate-500">Pastikan semua data bertanda <span class="text-rose-500">*</span> sudah terisi dengan benar.</p>
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-200/50 hover:-translate-y-0.5">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Script to handle formatting Pagu Anggaran dynamically -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paguDisplay = document.getElementById('pagu_display');
        const paguHidden = document.getElementById('pagu');
        
        paguDisplay.addEventListener('input', function(e) {
            // Remove everything except numbers
            let rawValue = e.target.value.replace(/[^0-9]/g, '');
            
            // Set hidden field to purely numeric
            paguHidden.value = rawValue;
            
            // Set display field to formatted string
            if (rawValue) {
                e.target.value = parseInt(rawValue, 10).toLocaleString('id-ID');
            } else {
                e.target.value = '';
            }
        });

        // Jenis Pengadaan -> Metode Pengadaan (dinamis)
        const jenis = document.getElementById('jenis_pengadaan');
        const metode = document.getElementById('metode_pengadaan');
        let currentMetode = @json(old('metode_pengadaan', $package->metode_pengadaan ?? ''));

        const standardOptions = [['', '-- Pilih Metode --'], ['E-Purchasing', 'E-Purchasing'], ['Pengadaan Langsung', 'Pengadaan Langsung'], ['Dikecualikan', 'Dikecualikan']];
        const swakelolaOptions = [['', '-- Pilih Metode --'], ['Swakelola Tipe 1', 'Swakelola Tipe 1'], ['Swakelola Tipe 2', 'Swakelola Tipe 2'], ['Swakelola Tipe 3', 'Swakelola Tipe 3'], ['Swakelola Tipe 4', 'Swakelola Tipe 4']];

        function renderMetode() {
            if (!jenis || !metode) return;
            const opts = jenis.value === 'Swakelola' ? swakelolaOptions : standardOptions;
            metode.innerHTML = opts.map(([v, l]) => `<option value="${v}">${l}</option>`).join('');
            if (currentMetode) {
                const opt = Array.from(metode.options).find(o => o.value === currentMetode);
                if (opt) opt.selected = true;
            }
        }

        if (jenis && metode) {
            jenis.addEventListener('change', function () { currentMetode = ''; renderMetode(); });
            renderMetode();
        }
    });
</script>
@endcomponent
