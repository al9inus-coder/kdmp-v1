@csrf

{{-- BANNER STATUS & ID RUP --}}
@if(isset($package) && $package->exists)
<div class="callout callout-info shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1"><i class="fas fa-info-circle text-info mr-2"></i>Status Paket</h5>
            @if($package->status === 'needs_review')
                <span class="badge badge-danger px-3 py-2 text-md elevation-1"><i class="fas fa-exclamation-triangle mr-1"></i> Needs Review</span>
            @elseif($package->status === 'draft')
                <span class="badge badge-warning px-3 py-2 text-md elevation-1"><i class="fas fa-pencil-alt mr-1"></i> Draft</span>
            @elseif($package->status === 'submitted')
                <span class="badge badge-primary px-3 py-2 text-md elevation-1"><i class="fas fa-paper-plane mr-1"></i> Diajukan</span>
            @elseif($package->status === 'approved')
                <span class="badge badge-success px-3 py-2 text-md elevation-1"><i class="fas fa-check-circle mr-1"></i> Approved</span>
            @endif
        </div>

        @if($package->id_rup)
            <div class="text-right">
                <span class="d-block text-muted text-sm"><i class="fas fa-hashtag mr-1"></i>ID RUP</span>
                <strong class="text-lg text-dark">{{ $package->id_rup }}</strong>
            </div>
        @endif
    </div>
</div>
@endif

{{-- INFORMASI PAKET --}}
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-box text-primary mr-2"></i> Informasi Paket
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="fiscal_year_id">
                        <i class="fas fa-calendar-alt text-muted mr-1"></i> Tahun Anggaran <span class="text-danger">*</span>
                    </label>
                    <select id="fiscal_year_id"
                            name="fiscal_year_id"
                            class="form-control select2 @error('fiscal_year_id') is-invalid @enderror"
                            required>
                        <option value="">-- Pilih Tahun Anggaran --</option>
                        @foreach($fiscalYears as $fiscalYear)
                            <option value="{{ $fiscalYear->id }}"
                                @selected((string) old('fiscal_year_id', $package->fiscal_year_id) === (string) $fiscalYear->id)>
                                {{ $fiscalYear->tahun }} @if($fiscalYear->is_active) (Aktif) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('fiscal_year_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="id_rup">
                        <i class="fas fa-fingerprint text-muted mr-1"></i> ID RUP
                    </label>
                    <div class="input-group">
                        <input type="text"
                               id="id_rup"
                               name="id_rup"
                               class="form-control @error('id_rup') is-invalid @enderror"
                               value="{{ old('id_rup', $package->id_rup) }}"
                               placeholder="Masukkan ID RUP (opsional)">
                    </div>
                    @error('id_rup')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="nama_paket">
                        <i class="fas fa-file-signature text-muted mr-1"></i> Nama Paket <span class="text-danger">*</span>
                    </label>
                    <input type="text"
                           id="nama_paket"
                           name="nama_paket"
                           class="form-control @error('nama_paket') is-invalid @enderror"
                           value="{{ old('nama_paket', $package->nama_paket) }}"
                           placeholder="Contoh: Pengadaan Komputer Kantor..."
                           required>
                    @error('nama_paket')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="pagu">
                        <i class="fas fa-money-bill-wave text-muted mr-1"></i> Pagu <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light font-weight-bold">Rp</span>
                        </div>
                        <input type="text"
                               id="pagu"
                               name="pagu"
                               class="form-control font-weight-bold text-primary @error('pagu') is-invalid @enderror"
                               value="{{ old('pagu', number_format((float)($package->pagu ?? 0), 0, ',', '.')) }}"
                               placeholder="0"
                               required>
                    </div>
                    @error('pagu')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- KLASIFIKASI & ANGGARAN --}}
<div class="card card-outline card-info shadow-sm mb-4">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-tags text-info mr-2"></i> Klasifikasi Paket
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="sub_activity_id">
                        <i class="fas fa-project-diagram text-muted mr-1"></i> Sub Kegiatan
                    </label>
                    <select id="sub_activity_id"
                            name="sub_activity_id"
                            class="form-control select2 @error('sub_activity_id') is-invalid @enderror">
                        <option value="">-- Pilih Sub Kegiatan --</option>
                        @foreach($subActivities as $subActivity)
                            <option value="{{ $subActivity->id }}"
                                @selected((string) old('sub_activity_id', $package->sub_activity_id) === (string) $subActivity->id)>
                                {{ $subActivity->kode }} - {{ $subActivity->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('sub_activity_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="account_id">
                        <i class="fas fa-wallet text-muted mr-1"></i> Rekening Belanja
                    </label>
                    <select id="account_id"
                            name="account_id"
                            class="form-control select2 @error('account_id') is-invalid @enderror">
                        <option value="">-- Pilih Rekening --</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}"
                                @selected((string) old('account_id', $package->account_id) === (string) $account->id)>
                                {{ $account->kode }} - {{ $account->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="jenis_pengadaan">
                        <i class="fas fa-layer-group text-muted mr-1"></i> Jenis Pengadaan
                    </label>
                    <select id="jenis_pengadaan"
                            name="jenis_pengadaan"
                            class="form-control select2 @error('jenis_pengadaan') is-invalid @enderror">
                        <option value="">-- Pilih Jenis Pengadaan --</option>
                        <option value="Barang" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Barang')>Pengadaan Barang</option>
                        <option value="Jasa Konsultansi" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Jasa Konsultansi')>Jasa Konsultansi</option>
                        <option value="Jasa Lainnya" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Jasa Lainnya')>Jasa Lainnya</option>
                        <option value="Pekerjaan Konstruksi" @selected(old('jenis_pengadaan', $package->jenis_pengadaan) == 'Pekerjaan Konstruksi')>Pekerjaan Konstruksi</option>
                    </select>
                    @error('jenis_pengadaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="metode_pengadaan">
                        <i class="fas fa-shopping-cart text-muted mr-1"></i> Metode Pengadaan
                    </label>
                    <select id="metode_pengadaan"
                            name="metode_pengadaan"
                            class="form-control select2 @error('metode_pengadaan') is-invalid @enderror">
                        <option value="">-- Pilih Metode --</option>
                        <option value="E-Purchasing" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'E-Purchasing')>E-Purchasing</option>
                        <option value="Pengadaan Langsung" @selected(old('metode_pengadaan', $package->metode_pengadaan) == 'Pengadaan Langsung')>Pengadaan Langsung</option>
                    </select>
                    @error('metode_pengadaan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

    </div>
</div>

{{-- JADWAL PENGADAAN --}}
<div class="card card-outline card-success shadow-sm mb-4">
    <div class="card-header">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-calendar-check text-success mr-2"></i> Jadwal Pelaksanaan
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="pemilihan_mulai_bulan"><i class="far fa-calendar-alt text-muted mr-1"></i> Pemilihan Mulai</label>
                    <select name="pemilihan_mulai_bulan" class="form-control select2 @error('pemilihan_mulai_bulan') is-invalid @enderror">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach(daftarBulanIndonesia() as $value => $label)
                            <option value="{{ $value }}" @selected(old('pemilihan_mulai_bulan', $package->pemilihan_mulai_bulan) == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('pemilihan_mulai_bulan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="pemilihan_selesai_bulan"><i class="far fa-calendar-check text-muted mr-1"></i> Pemilihan Selesai</label>
                    <select name="pemilihan_selesai_bulan" class="form-control select2 @error('pemilihan_selesai_bulan') is-invalid @enderror">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach(daftarBulanIndonesia() as $value => $label)
                            <option value="{{ $value }}" @selected(old('pemilihan_selesai_bulan', $package->pemilihan_selesai_bulan) == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('pemilihan_selesai_bulan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="kontrak_mulai_bulan"><i class="far fa-handshake text-muted mr-1"></i> Kontrak Mulai</label>
                    <select name="kontrak_mulai_bulan" class="form-control select2 @error('kontrak_mulai_bulan') is-invalid @enderror">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach(daftarBulanIndonesia() as $value => $label)
                            <option value="{{ $value }}" @selected(old('kontrak_mulai_bulan', $package->kontrak_mulai_bulan) == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('kontrak_mulai_bulan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="kontrak_selesai_bulan"><i class="fas fa-flag-checkered text-muted mr-1"></i> Kontrak Selesai</label>
                    <select name="kontrak_selesai_bulan" class="form-control select2 @error('kontrak_selesai_bulan') is-invalid @enderror">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach(daftarBulanIndonesia() as $value => $label)
                            <option value="{{ $value }}" @selected(old('kontrak_selesai_bulan', $package->kontrak_selesai_bulan) == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('kontrak_selesai_bulan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TOMBOL AKSI --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center p-3 bg-white border rounded shadow-sm">
            <a href="{{ route('packages.index') }}" class="btn btn-default btn-lg shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            
            <button type="submit" class="btn btn-success btn-lg shadow-sm px-4">
                <i class="fas fa-save mr-1"></i> {{ $submitLabel ?? 'Simpan' }}
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Format Pagu Input
    const pagu = document.getElementById('pagu');
    if (pagu) {
        pagu.addEventListener('input', function () {
            let value = this.value.replace(/\D/g, '');
            this.value = new Intl.NumberFormat('id-ID').format(value);
        });

        // Hapus pemisah ribuan sebelum form disubmit agar database menerima angka asli
        pagu.form.addEventListener('submit', function () {
            pagu.value = pagu.value.replace(/\./g, '');
        });
    }

    // Inisialisasi Select2 jika library tersedia (opsional tapi disarankan untuk AdminLTE)
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
});
</script>