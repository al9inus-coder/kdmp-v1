@php $package = $package ?? new \App\Models\Package(); @endphp
@csrf

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
        <div class="p-1.5 rounded-full bg-rose-100 shrink-0"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i></div>
        <div>
            <p class="text-sm font-bold text-rose-800">Terjadi kesalahan validasi</p>
            <ul class="mt-1 text-xs text-rose-600 list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    </div>
@endif

{{-- Banner status (edit) --}}
@if($package->exists)
    <div class="mb-6 flex items-center justify-between gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
        <div class="flex items-center gap-3">
            <span class="text-sm font-semibold text-slate-600">Status Paket:</span>
            @switch($package->status)
                @case('needs_review') <x-ui.badge variant="danger">Needs Review</x-ui.badge> @break
                @case('draft') <x-ui.badge variant="warning">Draft</x-ui.badge> @break
                @case('submitted') <x-ui.badge variant="info">Diajukan</x-ui.badge> @break
                @case('approved') <x-ui.badge variant="success">Approved</x-ui.badge> @break
            @endswitch
        </div>
        @if($package->id_rup)
            <div class="text-right">
                <span class="block text-xs text-slate-400">ID RUP</span>
                <strong class="text-sm text-slate-800 font-mono">{{ $package->id_rup }}</strong>
            </div>
        @endif
    </div>
@endif

<div class="space-y-6 max-w-4xl">
    {{-- Informasi Paket --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="package" class="w-4 h-4"></i></div>
            <h3 class="text-sm font-bold text-slate-900">Informasi Paket</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="fiscal_year_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Anggaran <span class="text-rose-500">*</span></label>
                <x-ui.select name="fiscal_year_id" id="fiscal_year_id" :invalid="$errors->has('fiscal_year_id')" required>
                    <option value="">-- Pilih Tahun Anggaran --</option>
                    @foreach($fiscalYears as $fiscalYear)
                        <option value="{{ $fiscalYear->id }}" @selected((string) old('fiscal_year_id', $package->fiscal_year_id) === (string) $fiscalYear->id)>
                            {{ $fiscalYear->tahun }} @if($fiscalYear->is_active) (Aktif) @endif
                        </option>
                    @endforeach
                </x-ui.select>
                @error('fiscal_year_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="id_rup" class="block text-sm font-semibold text-slate-700 mb-1.5">ID RUP <span class="text-rose-500">*</span></label>
                <x-ui.input type="text" name="id_rup" id="id_rup" :value="old('id_rup', $package->id_rup)" :invalid="$errors->has('id_rup')" placeholder="Masukkan ID RUP" required />
                @error('id_rup') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="nama_paket" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Paket <span class="text-rose-500">*</span></label>
                <x-ui.input type="text" name="nama_paket" id="nama_paket" :value="old('nama_paket', $package->nama_paket)" :invalid="$errors->has('nama_paket')" placeholder="Contoh: Pengadaan Komputer Kantor..." required />
                @error('nama_paket') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="pagu" class="block text-sm font-semibold text-slate-700 mb-1.5">Pagu <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-semibold text-slate-400 pointer-events-none">Rp</span>
                    <input type="text" name="pagu" id="pagu" required
                        value="{{ old('pagu', number_format((float)($package->pagu ?? 0), 0, ',', '.')) }}" placeholder="0"
                        class="block w-full rounded-md shadow-sm sm:text-sm pl-9 pr-3 py-2 font-semibold text-emerald-700 border {{ $errors->has('pagu') ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-slate-300 focus:ring-emerald-500 focus:border-emerald-500' }} focus:outline-none focus:ring-2 bg-white transition-colors">
                </div>
                @error('pagu') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    {{-- Klasifikasi --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center"><i data-lucide="tags" class="w-4 h-4"></i></div>
            <h3 class="text-sm font-bold text-slate-900">Klasifikasi Paket</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="sub_activity_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Sub Kegiatan</label>
                <x-ui.select name="sub_activity_id" id="sub_activity_id" :invalid="$errors->has('sub_activity_id')">
                    <option value="">-- Pilih Sub Kegiatan --</option>
                    @foreach($subActivities as $subActivity)
                        <option value="{{ $subActivity->id }}" @selected((string) old('sub_activity_id', $package->sub_activity_id) === (string) $subActivity->id)>{{ $subActivity->kode }} - {{ $subActivity->nama }}</option>
                    @endforeach
                </x-ui.select>
                @error('sub_activity_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="account_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Rekening Belanja</label>
                <x-ui.select name="account_id" id="account_id" :invalid="$errors->has('account_id')">
                    <option value="">-- Pilih Rekening --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @selected((string) old('account_id', $package->account_id) === (string) $account->id)>{{ $account->kode }} - {{ $account->nama }}</option>
                    @endforeach
                </x-ui.select>
                @error('account_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jenis_pengadaan" class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis Pengadaan</label>
                <x-ui.select name="jenis_pengadaan" id="jenis_pengadaan" :invalid="$errors->has('jenis_pengadaan')">
                    <option value="">-- Pilih Jenis Pengadaan --</option>
                    <option value="Barang" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Barang')>Pengadaan Barang</option>
                    <option value="Jasa Konsultansi" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Jasa Konsultansi')>Jasa Konsultansi</option>
                    <option value="Jasa Lainnya" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Jasa Lainnya')>Jasa Lainnya</option>
                    <option value="Pekerjaan Konstruksi" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Pekerjaan Konstruksi')>Pekerjaan Konstruksi</option>
                    <option value="Swakelola" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Swakelola')>Swakelola</option>
                </x-ui.select>
                @error('jenis_pengadaan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="metode_pengadaan" class="block text-sm font-semibold text-slate-700 mb-1.5">Metode Pengadaan</label>
                <x-ui.select name="metode_pengadaan" id="metode_pengadaan" :invalid="$errors->has('metode_pengadaan')">
                    <option value="">-- Pilih Metode --</option>
                    <option value="E-Purchasing" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'E-Purchasing')>E-Purchasing</option>
                    <option value="Pengadaan Langsung" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'Pengadaan Langsung')>Pengadaan Langsung</option>
                    <option value="Dikecualikan" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'Dikecualikan')>Dikecualikan</option>
                </x-ui.select>
                @error('metode_pengadaan') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    {{-- Jadwal --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="calendar-check" class="w-4 h-4"></i></div>
            <h3 class="text-sm font-bold text-slate-900">Jadwal Pelaksanaan</h3>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach([
                'pemilihan_mulai_bulan' => 'Pemilihan Mulai',
                'pemilihan_selesai_bulan' => 'Pemilihan Selesai',
                'kontrak_mulai_bulan' => 'Kontrak Mulai',
                'kontrak_selesai_bulan' => 'Kontrak Selesai',
            ] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $label }}</label>
                    <x-ui.select name="{{ $field }}" id="{{ $field }}" :invalid="$errors->has($field)">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach(daftarBulanIndonesia() as $value => $bulan)
                            <option value="{{ $value }}" @selected(old($field, $package->{$field}) == $value)>{{ $bulan }}</option>
                        @endforeach
                    </x-ui.select>
                    @error($field) <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </section>

    {{-- Aksi --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-white border border-slate-200 rounded-2xl shadow-sm">
        <x-ui.button variant="secondary" size="md" href="{{ route('packages.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
        <x-ui.button variant="primary" size="lg" type="submit">
            <i data-lucide="save" class="w-4 h-4 mr-2"></i> {{ $submitLabel ?? 'Simpan' }}
        </x-ui.button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Format Pagu (pemisah ribuan), strip sebelum submit
    const pagu = document.getElementById('pagu');
    if (pagu) {
        pagu.addEventListener('input', function () {
            const value = this.value.replace(/\D/g, '');
            this.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
        });
        if (pagu.form) {
            pagu.form.addEventListener('submit', function () {
                pagu.value = pagu.value.replace(/\./g, '');
            });
        }
    }

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
