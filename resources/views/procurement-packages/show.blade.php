@extends('adminlte::page')

@section('title', 'Detail Paket')

@section('content_header')
    <h1 class="mb-1 text-dark font-weight-bold">
        <i class="fas fa-folder-open text-primary mr-2"></i> Detail Informasi Paket
    </h1>
@stop

@section('content')
    @if($procurementPackage->package->jenis_pengadaan !== 'Swakelola')
        @include('components.workflow-progress', ['procurementPackage' => $procurementPackage])
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-ban mr-1"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="icon fas fa-check mr-1"></i> {{ session('success') }}
        </div>
    @endif
<style>
    /* STYLE TOMBOL MODERN (NON-FLOATING) */
    .btn-modern {
        border-radius: 50px !important; /* Bentuk Pill / Kapsul */
        padding: 10px 24px !important;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.3px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.08); /* Bayangan Halus Default */
    }

    /* Efek terangkat saat disentuh kursor */
    .btn-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    /* Mempercantik kotak pembungkus (Action Bar) */
    .action-bar-container {
        border-left: 4px solid #007bff !important; /* Garis biru selaras dengan kotak Informasi Paket */
    }
</style>
    {{-- BARIS INFORMASI PAKET (2 CARD) --}}
<div class="row mb-4">
    
    {{-- CARD 1: INFORMASI UTAMA & ANGGARAN --}}
    <div class="col-md-6">
        <div class="card card-outline card-info shadow-sm h-100 mb-0">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-info-circle text-info mr-2"></i> Informasi Utama
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>

                            <tr>
                                <th style="width: 35%;" class="pl-4">ID RUP</th>
                                <td class="font-weight-bold">{{ $procurementPackage->package->id_rup ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-4">Nama Paket</th>
                                <td>{{ $procurementPackage->package->nama_paket }}</td>
                            </tr>
                            <tr>
                                <th class="pl-4">Tahun Anggaran</th>
                                <td>{{ $procurementPackage->package->fiscalYear->tahun ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-4">Pagu</th>
                                <td class="text-primary font-weight-bold">Rp {{ number_format((float) $procurementPackage->package->pagu, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2: KLASIFIKASI PENGADAAN --}}
    <div class="col-md-6 mt-4 mt-md-0">
        <div class="card card-outline card-success shadow-sm h-100 mb-0">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-tags text-success mr-2"></i> {{ $procurementPackage->package->jenis_pengadaan === 'Swakelola' ? 'Detail Anggaran' : 'Klasifikasi Paket' }}
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <tbody>
                            @if($procurementPackage->package->jenis_pengadaan === 'Swakelola')
                                <tr>
                                    <th style="width: 35%;" class="pl-4">Kegiatan</th>
                                    <td>{{ $procurementPackage->package->activity?->kode }} {{ $procurementPackage->package->activity ? '- '.$procurementPackage->package->activity->nama : '' }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Sub Kegiatan</th>
                                    <td>{{ $procurementPackage->package->subActivity?->kode }} {{ $procurementPackage->package->subActivity ? '- '.$procurementPackage->package->subActivity->nama : '' }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Rekening Belanja</th>
                                    <td>{{ $procurementPackage->package->account?->kode }} {{ $procurementPackage->package->account ? '- '.$procurementPackage->package->account->nama : '' }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4 border-bottom-0">Jenis Pengadaan</th>
                                    <td class="border-bottom-0">
                                        <span class="badge badge-secondary px-2 py-1">{{ $procurementPackage->package->jenis_pengadaan }}</span>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <th style="width: 35%;" class="pl-4">Program</th>
                                    <td>{{ $procurementPackage->package->program?->kode }} {{ $procurementPackage->package->program ? '- '.$procurementPackage->package->program->nama : '' }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Kegiatan</th>
                                    <td>{{ $procurementPackage->package->activity?->kode }} {{ $procurementPackage->package->activity ? '- '.$procurementPackage->package->activity->nama : '' }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Sub Kegiatan</th>
                                    <td>{{ $procurementPackage->package->subActivity?->kode }} {{ $procurementPackage->package->subActivity ? '- '.$procurementPackage->package->subActivity->nama : '' }}</td>
                                </tr>
                                <tr>
                                    <th class="pl-4">Jenis Pengadaan</th>
                                    <td>
                                        @if(isset($procurementPackage->package->jenis_pengadaan))
                                            <span class="badge badge-secondary px-2 py-1">{{ $procurementPackage->package->jenis_pengadaan }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="pl-4 border-bottom-0">Metode Pengadaan</th>
                                    <td class="border-bottom-0">{{ $procurementPackage->package->metode_pengadaan ?? '-' }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

    {{-- FORM METADATA --}}
    <form id="form-meta-update" method="POST" action="{{ route('procurement-packages.meta.update', $procurementPackage) }}">
        @csrf
        @method('PATCH')

        @if($procurementPackage->package->jenis_pengadaan !== 'Swakelola')
        <fieldset {{ $procurementPackage->workflow_status !== \App\Models\ProcurementPackage::WORKFLOW_DRAFT ? 'disabled' : '' }}>
        <div class="row">
            {{-- KOLOM 1: INFORMASI PPK --}}
            <div class="col-md-6">
                <div class="card card-outline card-primary shadow-sm h-100">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-user-tie text-primary mr-2"></i> Informasi PPK
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><i class="fas fa-user text-muted mr-1"></i> Nama PPK</label>
                            <input type="text" name="nama_ppk" class="form-control" value="{{ old('nama_ppk', $procurementPackage->nama_ppk) }}" placeholder="Masukkan Nama Lengkap PPK">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-id-badge text-muted mr-1"></i> Pangkat / Golongan</label>
                            <input type="text" name="pangkat_gol_ppk" class="form-control" value="{{ old('pangkat_gol_ppk', $procurementPackage->pangkat_gol_ppk) }}" placeholder="Contoh: Pembina / IV a">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-address-card text-muted mr-1"></i> NIP</label>
                            <input type="text" name="nip_ppk" class="form-control" value="{{ old('nip_ppk', $procurementPackage->nip_ppk) }}" placeholder="Masukkan NIP">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-phone-alt text-muted mr-1"></i> No. Telp</label>
                            <input type="text" name="no_telp_ppk" class="form-control" value="{{ old('no_telp_ppk', $procurementPackage->no_telp_ppk) }}" placeholder="Contoh: 08123456789">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-envelope text-muted mr-1"></i> Email</label>
                            <input type="email" name="email_ppk" class="form-control" value="{{ old('email_ppk', $procurementPackage->email_ppk) }}" placeholder="email@instansi.go.id">
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-user text-muted mr-1"></i> User PPK</label>
                            <input type="text" name="user_ppk" class="form-control" value="{{ old('user_ppk', $procurementPackage->user_ppk) }}" placeholder="Masukkan User PPK">
                        </div>

                        <div class="form-group mb-0">
                            <label><i class="fas fa-building text-muted mr-1"></i> NPWP Instansi</label>
                            <input type="text" name="npwp_instansi" class="form-control" value="{{ old('npwp_instansi', $procurementPackage->npwp_instansi) }}" placeholder="Nomor NPWP Instansi">
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM 2: INFORMASI KONTRAK --}}
            <div class="col-md-6">
                <div class="card card-outline card-success shadow-sm h-100">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-file-contract text-success mr-2"></i> Informasi Kontrak
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label><i class="fas fa-signature text-muted mr-1"></i> Jenis Kontrak</label>
                            <select name="jenis_kontrak" class="form-control custom-select">
                                <option value="">-- Pilih Jenis Kontrak --</option>
                                @foreach(['Harga Satuan', 'Lump Sum'] as $jenis)
                                    <option value="{{ $jenis }}" @selected(old('jenis_kontrak', $procurementPackage->jenis_kontrak) == $jenis)>
                                        {{ $jenis }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                <i class="fas fa-clock text-muted mr-1"></i> 
                                @if($procurementPackage->package->jenis_pengadaan == 'Barang')
                                    Jangka Waktu Pengiriman Barang
                                @else
                                    Jangka Waktu Pekerjaan
                                @endif
                            </label>
                            <div class="row">
                                <div class="col-sm-8 mb-2 mb-sm-0">
                                    <input type="number" class="form-control" name="jangka_waktu_nilai" value="{{ old('jangka_waktu_nilai', $procurementPackage->jangka_waktu_nilai) }}" placeholder="Angka">
                                </div>
                                <div class="col-sm-4">
                                    <select name="jangka_waktu_satuan" class="form-control custom-select">
                                        <option value="hari" @selected(old('jangka_waktu_satuan', $procurementPackage->jangka_waktu_satuan) == 'hari')>Hari</option>
                                        <option value="bulan" @selected(old('jangka_waktu_satuan', $procurementPackage->jangka_waktu_satuan) == 'bulan')>Bulan</option>
                                        <option value="tahun" @selected(old('jangka_waktu_satuan', $procurementPackage->jangka_waktu_satuan) == 'tahun')>Tahun</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
    <label>Tanggal Barang Diterima</label>

    <input type="date"
           name="tanggal_barang_diterima"
           value="{{ old(
                'tanggal_barang_diterima',
                $procurementPackage->tanggal_barang_diterima
            ) }}"
           class="form-control">
</div>

                        <hr class="mt-4 mb-4">

                        <div class="form-group">
                            <label class="d-block"><i class="fas fa-shield-alt text-muted mr-1"></i> Garansi</label>
                            <div class="mb-2">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" id="garansi_tidak" name="ada_garansi" value="0" {{ !$procurementPackage->ada_garansi ? 'checked' : '' }}>
                                    <label for="garansi_tidak" class="custom-control-label font-weight-normal">Tidak Ada Garansi</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" id="garansi_ada" name="ada_garansi" value="1" {{ $procurementPackage->ada_garansi ? 'checked' : '' }}>
                                    <label for="garansi_ada" class="custom-control-label font-weight-normal">Ada Garansi</label>
                                </div>
                            </div>
                            
                            {{-- Input Masa Garansi --}}
                            <div class="row mt-2">
                                <div class="col-sm-8 mb-2 mb-sm-0">
                                    <input type="number" class="form-control" name="garansi_nilai" value="{{ old('garansi_nilai', $procurementPackage->garansi_nilai) }}" placeholder="Masa Garansi">
                                </div>
                                <div class="col-sm-4">
                                    <select name="garansi_satuan" class="form-control custom-select">
                                        <option value="hari" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) == 'hari')>Hari</option>
                                        <option value="bulan" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) == 'bulan')>Bulan</option>
                                        <option value="tahun" @selected(old('garansi_satuan', $procurementPackage->garansi_satuan) == 'tahun')>Tahun</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0 mt-4">
                            <label class="d-block"><i class="fas fa-tools text-muted mr-1"></i> Layanan Purna Jual</label>
                            <div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" id="purna_jual_ya" name="layanan_purna_jual" value="1" {{ $procurementPackage->layanan_purna_jual ? 'checked' : '' }}>
                                    <label for="purna_jual_ya" class="custom-control-label font-weight-normal">Ya</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input class="custom-control-input" type="radio" id="purna_jual_tidak" name="layanan_purna_jual" value="0" {{ !$procurementPackage->layanan_purna_jual ? 'checked' : '' }}>
                                    <label for="purna_jual_tidak" class="custom-control-label font-weight-normal">Tidak</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0 mt-4">
                            <label><i class="fas fa-calendar-alt text-muted mr-1"></i> Tanggal Pembuatan Spesifikasi Teknis</label>
                            <input type="date" name="tanggal_spesifikasi" class="form-control" value="{{ old('tanggal_spesifikasi', $procurementPackage->technicalSpecification?->tanggal?->format('Y-m-d')) }}">
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- Rincian Barang/Jasa --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i>
                            Rincian Barang/Jasa
                        </h3>
                        <div class="card-tools">
                            <button type="button"
                                    id="btn-add-item"
                                    class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i>
                                Tambah Barang
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                            <tr>
                                <th width="3%">No</th>
                                <th width="18%">
                                    Nama Barang/Jasa
                                </th>
                                <th width="20%">
                                    Spesifikasi
                                </th>
                                <th width="8%">
                                    Volume
                                </th>
                                <th width="6%">
                                    Satuan
                                </th>
                                <th width="10%">
                                    Harga Satuan DPA
                                </th>
                                <th width="4%">
                                    PDN
                                </th>
                                <th width="8%">
                                    TKDN (%)
                                </th>
                                <th width="20%">
                                    Kode MAK
                                </th>
                                <th width="4%">
                                    Aksi
                                </th>
                            </tr>
                            </thead>
                            <tbody id="items-body">
                            @if(
                                $procurementPackage->technicalSpecification
                                &&
                                $procurementPackage->technicalSpecification->items->count()
                            )
                                @foreach(
                                    $procurementPackage->technicalSpecification->items
                                    as $index => $item
                                )
                                    <tr>
                                        <td>
                                            {{ $index + 1 }}
                                        </td>
                                        <td>
                                            <input type="text"
                                                name="items[{{ $index + 1 }}][nama_barang_jasa]"
                                                value="{{ $item->nama_barang_jasa }}"
                                                class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <textarea
                                                name="items[{{ $index + 1 }}][spesifikasi]"
                                                class="form-control form-control-sm"
                                                rows="2">{{ $item->spesifikasi }}</textarea>
                                        </td>
                                        <td>
                                            <input type="number"
                                                name="items[{{ $index + 1 }}][volume]"
                                                value="{{ $item->volume }}"
                                                class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <input type="text"
                                                name="items[{{ $index + 1 }}][satuan]"
                                                value="{{ $item->satuan }}"
                                                class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <input type="text"
                                                name="items[{{ $index + 1 }}][harga_satuan_dpa]"
                                                value="{{ number_format((float) ($item->harga_satuan_dpa ?? 0), 0, ',', '.') }}"
                                                class="form-control form-control-sm currency-input">
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                value="1"
                                                name="items[{{ $index + 1 }}][pdn]"
                                                @checked($item->pdn)>
                                        </td>
                                        <td>
                                            <input type="number"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                name="items[{{ $index + 1 }}][tkdn]"
                                                value="{{ $item->tkdn }}"
                                                class="form-control form-control-sm">
                                        </td>
                                        <td>
                                            <input type="text"
                                                name="items[{{ $index + 1 }}][kode_mak]"
                                                value="{{ $item->kode_mak }}"
                                                class="form-control form-control-sm">
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-danger btn-sm btn-remove-row">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr id="empty-row">
                                    <td colspan="10"
                                        class="text-center text-muted">
                                        Belum ada rincian barang/jasa.
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>
        </fieldset>
        @endif

        @if($procurementPackage->package->jenis_pengadaan === 'Swakelola')
            @php
                $accountName = strtolower($procurementPackage->package->account?->nama ?? '');
            @endphp
            @if(str_contains($accountName, 'lembur'))
            <div class="card card-outline card-warning mt-4 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-clock text-warning mr-2"></i> Pengelolaan Belanja Lembur
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Modul ini digunakan untuk mengelola administrasi pembayaran lembur, rekapitulasi jam kerja, dan pencetakan kwitansi lembur bulanan. Pilih bulan di bawah ini untuk menginput jam lembur melalui <strong>Sistem Kalender</strong>.</p>
                    
                    @php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                            4 => 'April', 5 => 'Mei', 6 => 'Juni', 
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
                            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $sbuRates = \App\Models\SbuLembur::all();
                        $overtimesList = \App\Models\Overtime::where('package_id', $procurementPackage->package->id)->with('details.employee')->get();
                        
                        $totalRealisasiLembur = 0;
                        $overtimeData = [];
                        foreach($months as $num => $name) {
                            $overtime = $overtimesList->where('bulan', $num)->first();
                            $totalBulanIni = 0;
                            if ($overtime) {
                                $totalBulanIni = $overtime->calculateTotalRealisasi($sbuRates);
                                $totalRealisasiLembur += $totalBulanIni;
                            }
                            $overtimeData[$num] = [
                                'exists' => $overtime != null,
                                'total' => $totalBulanIni,
                                'is_locked' => $overtime ? $overtime->is_locked : false
                            ];
                        }
                        $paguLembur = (float) $procurementPackage->package->pagu;
                        $sisaAnggaranLembur = $paguLembur - $totalRealisasiLembur;
                        $persenRealisasi = $paguLembur > 0 ? ($totalRealisasiLembur / $paguLembur) * 100 : 0;
                    @endphp

                    <!-- WIDGET REALISASI ANGGARAN (CREATIVE) -->
                    <div class="row mb-4 px-2">
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm" style="border-left: 4px solid #17a2b8; border-radius: 8px;">
                                <span class="info-box-icon text-info bg-white shadow-sm" style="border-radius: 8px;"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted font-weight-bold" style="font-size: 0.85rem; letter-spacing: 1px;">PAGU ANGGARAN</span>
                                    <span class="info-box-number text-dark" style="font-size: 1.3rem;">Rp {{ number_format($paguLembur, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm" style="border-left: 4px solid #28a745; border-radius: 8px;">
                                <span class="info-box-icon text-success bg-white shadow-sm" style="border-radius: 8px;"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted font-weight-bold" style="font-size: 0.85rem; letter-spacing: 1px;">TOTAL REALISASI</span>
                                    <span class="info-box-number text-success" style="font-size: 1.3rem;">Rp {{ number_format($totalRealisasiLembur, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm" style="border-left: 4px solid {{ $sisaAnggaranLembur < 0 ? '#dc3545' : '#ffc107' }}; border-radius: 8px;">
                                <span class="info-box-icon {{ $sisaAnggaranLembur < 0 ? 'text-danger' : 'text-warning' }} bg-white shadow-sm" style="border-radius: 8px;"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-muted font-weight-bold" style="font-size: 0.85rem; letter-spacing: 1px;">SISA ANGGARAN</span>
                                    <span class="info-box-number {{ $sisaAnggaranLembur < 0 ? 'text-danger' : 'text-warning' }}" style="font-size: 1.3rem;">Rp {{ number_format($sisaAnggaranLembur, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="font-weight-bold text-muted">Persentase Penyerapan</small>
                                <small class="font-weight-bold {{ $persenRealisasi > 90 ? 'text-danger' : 'text-success' }}">{{ number_format($persenRealisasi, 1) }}%</small>
                            </div>
                            <div class="progress shadow-sm" style="height: 8px; border-radius: 10px;">
                                <div class="progress-bar {{ $persenRealisasi > 90 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $persenRealisasi }}%" aria-valuenow="{{ $persenRealisasi }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($months as $num => $name)
                            @php
                                $data = $overtimeData[$num];
                                $exists = $data['exists'];
                            @endphp
                            <div class="col-md-2 col-sm-4 col-6 mb-3">
                                <a href="{{ route('packages.overtimes.show', [$procurementPackage->package, $num]) }}" class="text-decoration-none text-dark">
                                    <div class="card shadow-sm h-100 {{ $exists ? 'border-warning' : 'border-light' }}" style="transition: 0.3s; cursor: pointer; border-radius: 12px; background: {{ $exists ? '#fffbf0' : '#ffffff' }};" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 15px rgba(255,193,7,0.2)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)';">
                                        <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                                            <h3 class="text-{{ $exists ? 'warning' : 'secondary' }} mb-2" style="font-size: 1.8rem;">
                                                <i class="fas {{ $exists ? 'fa-calendar-check' : 'fa-calendar-alt' }}"></i>
                                            </h3>
                                            <span class="font-weight-bold d-block text-dark">{{ $name }}</span>
                                            @if($exists)
                                                @if($data['is_locked'])
                                                    <span class="badge badge-success mt-1 mx-auto" style="font-size: 0.7rem; border-radius: 20px; padding: 4px 8px;">Selesai Di-SPJ-kan (Kunci)</span>
                                                @else
                                                    <span class="badge badge-info mt-1 mx-auto" style="font-size: 0.7rem; border-radius: 20px; padding: 4px 8px;">Sedang Diproses (Draft)</span>
                                                @endif
                                                <div class="mt-2 text-success font-weight-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">Rp {{ number_format($data['total'], 0, ',', '.') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            @if(str_contains($accountName, 'perjalanan dinas'))
            <div class="card card-outline card-primary mt-4 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-plane text-primary mr-2"></i> Tabel Informasi Perjalanan Dinas
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('packages.travel-orders.create', $procurementPackage->package) }}" class="btn btn-primary btn-sm btn-modern">
                            <i class="fas fa-plus mr-1"></i> Tambah Baru
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Pegawai</th>
                                <th>Tujuan</th>
                                <th>Tgl Berangkat</th>
                                <th>Tgl Kembali</th>
                                <th>Biaya Perkiraan</th>
                                <th>Biaya Rampung</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procurementPackage->package->travelOrders as $index => $to)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @foreach($to->personnels as $personnel)
                                            <span class="badge badge-info">{{ $personnel->employee->nama }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $to->tempat_tujuan }}</td>
                                    <td>{{ $to->tanggal_berangkat->format('d/m/Y') }}</td>
                                    <td>{{ $to->tanggal_kembali->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $totalPerkiraanTo = 0;
                                            foreach($to->personnels as $p) {
                                                $est = $p->getEstimatedCosts();
                                                $totalPerkiraanTo += $est['uang_harian'] + $est['biaya_transport'] + $est['biaya_penginapan'] + $est['biaya_representasi'];
                                            }
                                        @endphp
                                        Rp {{ number_format($totalPerkiraanTo, 0, ',', '.') }}
                                    </td>
                                    <td>Rp {{ number_format($to->personnels->sum(function($p) { return $p->uang_harian + $p->biaya_transport + $p->biaya_penginapan + $p->biaya_representasi; }), 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('packages.travel-orders.show', [$procurementPackage->package, $to]) }}" class="btn btn-sm btn-info rounded-pill px-3">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada perjalanan dinas untuk paket ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light">
                            @php
                                $pagu = $procurementPackage->package->pagu ?? 0;
                                $realisasi = $procurementPackage->package->travelOrders->sum(function($to) { 
                                    return $to->personnels->sum(function($p) { 
                                        return $p->uang_harian + $p->biaya_transport + $p->biaya_penginapan + $p->biaya_representasi; 
                                    }); 
                                });
                                $sisa = $pagu - $realisasi;
                            @endphp
                            <tr>
                                <td colspan="5"></td>
                                <td class="text-right align-middle font-weight-bold" style="font-size: 1.1rem;">Total Realisasi:</td>
                                <td class="align-middle font-weight-bold" style="font-size: 1.1rem;">Rp {{ number_format($realisasi, 0, ',', '.') }}</td>
                                <td class="align-middle text-center">
                                    <div class="px-2 py-1 bg-white border rounded shadow-sm d-inline-block">
                                        <small class="text-muted d-block font-weight-bold" style="font-size: 0.7rem; text-transform: uppercase;">Sisa Anggaran</small>
                                        <strong class="{{ $sisa < 0 ? 'text-danger' : 'text-success' }}">Rp {{ number_format($sisa, 0, ',', '.') }}</strong>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif
            <!-- Empty replacement to remove the unused table -->
        @endif

{{-- ACTION BAR (TOMBOL BAWAH) --}}
        <div class="row mt-4 mb-5">
            <div class="col-12">
                <div class="action-bar-container d-flex flex-column flex-lg-row justify-content-between align-items-center p-4 bg-white border rounded shadow-sm">
                    {{-- TOMBOL KIRI (KEMBALI) --}}
                    <div class="mb-3 mb-lg-0 w-100 text-center text-lg-left">
                        <a href="{{ route('procurement-packages.index') }}" class="btn btn-light border btn-modern text-muted">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                    {{-- TOMBOL KANAN (AKSI) --}}
                    <div class="d-flex flex-column flex-md-row w-100 justify-content-lg-end text-center">
                        @if($procurementPackage->workflow_status === \App\Models\ProcurementPackage::WORKFLOW_DRAFT)
                            @if($procurementPackage->package->jenis_pengadaan !== 'Swakelola')
                            <button type="submit" 
                                    class="btn btn-success btn-modern mb-2 mb-md-0 mr-md-3">
                                <i class="fas fa-save mr-1"></i> Simpan Informasi
                            </button>
                            
                            <button type="button" class="btn btn-warning btn-modern mb-2 mb-md-0 mr-md-2" data-toggle="modal" data-target="#editPromptModal">
                                <i class="fas fa-cog mr-1"></i> Edit Prompt
                            </button>
                            {{-- Menambahkan efek gradien agar tombol AI terlihat lebih canggih & premium --}}
                            <button type="button" 
                                    id="btn-generate-ai" 
                                    class="btn btn-primary btn-modern"
                                    style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border: none;">
                                <i class="fas fa-robot mr-1"></i> Buat Dokumen
                            </button>
                            @endif
                        @else
                            @if($procurementPackage->package->jenis_pengadaan !== 'Swakelola')
                            <a href="{{ route('procurement-packages.procurement-process.show', $procurementPackage->package) }}" class="btn btn-info btn-modern" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); border: none;">
                                <i class="fas fa-arrow-right mr-1"></i> Proses Pengadaan
                            </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>

{{-- MODAL LOADING AI (PRO CHECKLIST & PROGRESS BAR) --}}
    <div class="modal fade"
         id="aiLoadingModal"
         tabindex="-1"
         data-backdrop="static"
         data-keyboard="false">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                
                {{-- Header Modal --}}
                <div class="modal-header bg-light border-bottom-0 pb-2 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 45px; height: 45px;">
                            <i class="fas fa-robot fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="font-weight-bold mb-0 text-dark">AI Menyusun Dokumen</h5>
                            <span class="text-muted" style="font-size: 0.9rem;">Mohon tunggu, proses otomatisasi sedang berjalan...</span>
                        </div>
                    </div>
                </div>

                <div class="modal-body px-4 pt-3 pb-4">
                    
                    {{-- Container Checklist --}}
                    <div id="ai-steps-container" class="mb-4 mt-2 px-2">
                        {{-- List di-generate oleh JavaScript --}}
                    </div>

                    {{-- Progress Bar Container --}}
                    <div class="mt-4 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center mb-2 mt-2">
                            <span class="text-sm font-weight-bold text-dark">Progres Total</span>
                            <span id="ai-progress-percentage" class="badge badge-primary px-2 py-1" style="font-size: 0.85rem;">0%</span>
                        </div>
                        <div class="progress rounded-pill shadow-sm" style="height: 10px; background-color: #e9ecef;">
                            <div id="ai-progress-bar" 
                                 class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                 role="progressbar" 
                                 style="width: 0%; transition: width 0.5s ease;" 
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT PROMPT AI --}}
    <div class="modal fade" id="editPromptModal" tabindex="-1" aria-labelledby="editPromptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('procurement-packages.update-prompt', $procurementPackage) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-dark font-weight-bold" id="editPromptModalLabel"><i class="fas fa-cog mr-2"></i> Konfigurasi Prompt AI</h5>
                        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> Anda dapat menyesuaikan gaya bahasa dan parameter AI. Hindari mengubah variabel di dalam kurung kurawal seperti <code>{NAMA_PAKET}</code>.
                        </div>
                        <div class="form-group">
                            <label>Template Prompt Spesifikasi Teknis</label>
                            <textarea id="promptTextarea" name="prompt" class="form-control" rows="15" required>{{ $aiPrompt->prompt ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="restoreDefaultPrompt()"><i class="fas fa-undo mr-1"></i> Gunakan Default</button>
                        <div>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-1"></i> Simpan Prompt</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('js')
    <script>
    
let rowNumber =
{{ $procurementPackage->technicalSpecification?->items?->count() ?? 0 }};

    document
    .getElementById('btn-add-item')
    .addEventListener('click', function () {
        const tbody =
            document.getElementById('items-body');
        const emptyRow =
            document.getElementById('empty-row');
        if (emptyRow) {
            emptyRow.remove();
        }
        rowNumber++;
        const row = document.createElement('tr');
        row.innerHTML = `
                <td>${rowNumber}</td>
            <td>
                <input type="text"
                    name="items[${rowNumber}][nama_barang_jasa]"
                    class="form-control form-control-sm"
                    placeholder="Nama Barang/Jasa">
            </td>
            <td>
                <textarea
                    name="items[${rowNumber}][spesifikasi]"
                    class="form-control form-control-sm"
                    rows="2"></textarea>
            </td>
            <td>
                <input type="number"
                    min="0"
                    name="items[${rowNumber}][volume]"
                    class="form-control form-control-sm">
                </td>
            <td>
                <input type="text"
                    name="items[${rowNumber}][satuan]"
                    class="form-control form-control-sm">
            </td>
            <td>
                <input type="text"
                    name="items[${rowNumber}][harga_satuan_dpa]"
                    class="form-control form-control-sm currency-input">
            </td>
            <td class="text-center">
                <input type="checkbox"
                value="1"
                name="items[${rowNumber}][pdn]">
            </td>
            <td>
                <input type="number"
                min="0"
                max="100"
                step="0.01"
                name="items[${rowNumber}][tkdn]"
                class="form-control">
            </td>
            <td>
                <input type="text"
                name="items[${rowNumber}][kode_mak]"
                class="form-control form-control-sm">                    
            </td>
            <td class="text-center">
                <button type="button"
                        class="btn btn-danger btn-sm btn-remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
document.addEventListener('click', function(e) {

    const button =
        e.target.closest('.btn-remove-row');

    if (!button) {
        return;
    }

    const row = button.closest('tr');

    row.remove();

    const tbody =
        document.getElementById('items-body');

    if (
        tbody.querySelectorAll('tr').length === 0
    ) {

        tbody.innerHTML = `
            <tr id="empty-row">
                <td colspan="10"
                    class="text-center text-muted">
                    Belum ada rincian barang/jasa.
                </td>
            </tr>
        `;

    }

});
    });
    document.querySelectorAll('.btn-remove-row')
    .forEach(function(button) {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            row.remove();
            const tbody =
                document.getElementById('items-body');
            if (
                tbody.querySelectorAll('tr').length === 0
            ) {
                tbody.innerHTML = `
                    <tr id="empty-row">
                        <td colspan="10"
                            class="text-center text-muted">
                            Belum ada rincian barang/jasa.
                        </td>
                    </tr>
                `;
            }
        });
    });
        document
            .getElementById('btn-generate-ai')
            .addEventListener('click', function () {

                $('#aiLoadingModal').modal('show');

                // Daftar proses sesuai permintaan Anda
                const steps = [
                    'Membaca dan menganalisis data paket pengadaan',
                    'Menyusun latar belakang',
                    'Menyusun maksud dan tujuan',
                    'Menyusun target dan sasaran',
                    'Menyusun uraian pekerjaan',
                    'Finalisasi draf'
                ];

                const container = document.getElementById('ai-steps-container');
                const progressBar = document.getElementById('ai-progress-bar');
                const progressText = document.getElementById('ai-progress-percentage');
                
                // 1. Render UI Checklist awal (Semua status: Menunggu / Abu-abu)
                container.innerHTML = steps.map((step, i) => `
                    <div class="ai-step-item" id="step-${i}">
                        <div class="ai-step-icon"><i class="fas fa-circle" style="font-size: 6px;"></i></div>
                        <div class="ai-step-text">${step}</div>
                    </div>
                `).join('');

                let currentStep = 0;
                const totalSteps = steps.length;

                // 2. Jalankan interval transisi setiap 1.5 detik
                const interval = setInterval(function () {
                    
                    // A. Ubah langkah SEBELUMNYA menjadi SELESAI (Hijau & Centang)
                    if (currentStep > 0 && currentStep <= totalSteps) {
                        let prevEl = document.getElementById(`step-${currentStep - 1}`);
                        if (prevEl) {
                            prevEl.classList.remove('active');
                            prevEl.classList.add('completed');
                            prevEl.querySelector('.ai-step-icon').innerHTML = '<i class="fas fa-check"></i>';
                        }
                    }

                    // B. Ubah langkah SAAT INI menjadi AKTIF (Biru & Spinner Putar)
                    if (currentStep < totalSteps) {
                        let currEl = document.getElementById(`step-${currentStep}`);
                        if (currEl) {
                            currEl.classList.add('active');
                            currEl.querySelector('.ai-step-icon').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        }

                        // C. Hitung & Update Progress Bar
                        // (Maksimal di 95% agar menunggu server merespons reload)
                        let progress = Math.floor(((currentStep + 1) / totalSteps) * 95);
                        progressBar.style.width = progress + '%';
                        progressText.innerText = progress + '%';

                        currentStep++;
                    } else {
                        // Jika sudah di tahap akhir, hentikan interval
                        clearInterval(interval);
                    }

                }, 1500); // 1.5 detik per langkah

                // 3. Eksekusi Form ke Backend Laravel
                document.getElementById('btn-generate-ai').disabled = true;
                
                const mainForm = document.getElementById('form-meta-update');
                if (mainForm) {
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'generate_ai';
                    mainForm.appendChild(actionInput);
                    
                    mainForm.submit();
                }

            });

        document.addEventListener('input', function(e) {

        if (!e.target.classList.contains('currency-input')) {
            return;
        }

        let value = e.target.value.replace(/\D/g, '');

        if (value === '') {
            e.target.value = '';
            return;
        }

        e.target.value =
            new Intl.NumberFormat('id-ID')
                .format(value);

    });

    // --- FITUR 1: DISABLE INPUT GARANSI JIKA "TIDAK ADA GARANSI" ---
    function toggleGaransiInputs() {
        let isAdaGaransi = $('#garansi_ada').is(':checked');
        if(isAdaGaransi) {
            $('input[name="garansi_nilai"]').prop('disabled', false).prop('required', true);
            $('select[name="garansi_satuan"]').prop('disabled', false).prop('required', true);
        } else {
            $('input[name="garansi_nilai"]').prop('disabled', true).prop('required', false).val('');
            $('select[name="garansi_satuan"]').prop('disabled', true).prop('required', false).val('hari');
        }
    }

    $('input[name="ada_garansi"]').on('change', toggleGaransiInputs);
    toggleGaransiInputs(); // Jalankan saat pertama kali halaman dimuat

    // --- FITUR 2: RESTORE DEFAULT PROMPT ---
    const defaultAIPrompt = @json(config('ai_prompts.technical_specification'));

    function restoreDefaultPrompt() {
        if(confirm('Anda yakin ingin mengembalikan prompt ke versi default (Rekomendasi Pro)?\\n\\nPrompt saat ini akan terhapus dan digantikan dengan yang standar.')){
            document.getElementById('promptTextarea').value = defaultAIPrompt;
        }
    }

    </script>
    <style>
/* AI PRO CHECKLIST STYLES */
    .ai-step-item {
        display: flex;
        align-items: center;
        margin-bottom: 14px;
        color: #adb5bd; /* Warna Abu-abu (Menunggu) */
        transition: all 0.3s ease;
    }

    /* Kotak Ikon Kiri */
    .ai-step-icon {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        border: 2px solid #adb5bd;
        font-size: 10px;
        transition: all 0.3s ease;
    }

    .ai-step-text {
        font-size: 0.95rem;
        font-weight: 500;
        letter-spacing: 0.2px;
    }

    /* STATE: Sedang Dikerjakan (AKTIF) */
    .ai-step-item.active {
        color: #007bff;
    }
    .ai-step-item.active .ai-step-icon {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
        box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.1);
        font-size: 12px;
    }
    .ai-step-item.active .ai-step-text {
        font-weight: 700;
    }

    /* STATE: Sudah Selesai (COMPLETED) */
    .ai-step-item.completed {
        color: #28a745;
    }
    .ai-step-item.completed .ai-step-icon {
        border-color: #28a745;
        background-color: #28a745;
        color: white;
        font-size: 12px;
    }
</style>
    @endpush
@stop