@extends('adminlte::page')

@section('title', 'Dokumen Spesifikasi Teknis')

@section('content_header')
@stop

@section('content')
<form method="POST"
      action="{{ route(
        'technical-specifications.update',
        $technicalSpecification
      ) }}">
    @csrf
    @method('PUT')
    @php
        $jangkaWaktuJenis = [
            'pengiriman_barang' => 'Pengiriman Barang',
            'pekerjaan_jasa' => 'Pekerjaan Jasa',
        ];

        $targetSasaran = $technicalSpecification->target_sasaran ?? $technicalSpecification->sasaran;
        $jangkaWaktu = $technicalSpecification->jangka_waktu ?? $technicalSpecification->jangka_waktu_hari;
        $maksudTujuan = collect([
            $technicalSpecification->maksud,
            $technicalSpecification->tujuan,
        ])->filter()->unique()->implode("\n\n");
        $garansiText = $technicalSpecification->garansi_nilai
            ? $technicalSpecification->garansi_nilai.' '.ucfirst((string) $technicalSpecification->garansi_satuan)
            : ($technicalSpecification->garansi ?? '-');
    @endphp

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{--Progress bar untuk menunjukkan status dokumen--}}
    <div class="card mb-3">
        <div class="card-body">

            <h5 class="mb-4">
                <i class="fas fa-project-diagram text-primary"></i>
                Tahapan Dokumen Pengadaan
            </h5>

            <div class="row text-center">

                <div class="col-md-4">
                    <div class="border rounded p-3 bg-success text-white">
                        <strong>1</strong><br>
                        Spesifikasi Teknis
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <strong>2</strong><br>
                        Referensi Harga
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <strong>3</strong><br>
                        Surat Permohonan Pengadaan
                    </div>
                </div>

            </div>

        </div>
    </div>

<style>
.document-paper{
    background:#fff;
    width:210mm;
    min-height:297mm;
    margin:0 auto;
    padding-top:2.5cm;
    padding-right:2cm;
    padding-bottom:2.5cm;
    padding-left:2.5cm;
    box-shadow:0 2px 15px rgba(0,0,0,.08);
    border:1px solid #ddd;
    font-family: Arial, sans-serif;
}

.document-title{
    text-align:center;
    font-weight:bold;
    margin-bottom:5px;
}

.document-subtitle{
    text-align:center;
    margin-bottom:40px;
}

.document-section{
    margin-top:30px;
}

.document-section h5{
    font-weight:bold;
    margin-bottom:15px;
}

.doc-editor{
    width:100%;
    border:none;
    outline:none;
    resize:none;
    line-height:1.8;
    background:transparent;
}

.doc-table td{
    padding:6px 10px;
    vertical-align:top;
}
</style>
<div class="document-paper">
    <div class="document-title">
        PEMERINTAH KABUPATEN BENGKAYANG
    </div>
    <div class="document-title"><h5>
        DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN,
        PERTANAHAN DAN LINGKUNGAN HIDUP</h5>
    </div>
    <div class="document-subtitle">
        Jl. Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat, Kode Pos 79211
        <br>Situs : bengkayangkab.go.id
    </div>
    <hr>
    <h3 class="document-title">
        SPESIFIKASI TEKNIS
    </h3>
    <table class="document-table">

        <tr>
            <td width="180">
                1. Latar Belakang
            </td>

            <td width="20">
                :
            </td>

            <td>
                <textarea></textarea>
            </td>
        </tr>

        <tr>
            <td>
                2. Maksud dan Tujuan
            </td>

            <td>
                :
            </td>

            <td>
                <textarea></textarea>
            </td>
        </tr>

    </table>
    {{--Isi dokumen spesifikasi teknis akan ditampilkan di sini--}}
    <div class="document-section">
        <h5>
            1. LATAR BELAKANG
        </h5>
        <textarea
            name="latar_belakang"
            rows="3"
            class="doc-editor">{{ $technicalSpecification->latar_belakang }}</textarea>
    </div>
    <div class="document-section">
        <h5>
            2. MAKSUD DAN TUJUAN
        </h5>
        <textarea
            name="maksud"
            rows="3"
            class="doc-editor">{{ $technicalSpecification->maksud }}</textarea>
    </div>
    <div class="document-section">
        <h5>
            3. TARGET DAN SASARAN
        </h5>
        <textarea
            name="target_sasaran"
            rows="5"
            class="doc-editor">{{ $technicalSpecification->target_sasaran }}</textarea>
    </div>
    <div class="document-section">
        <h5>
            4. URAIAN PEKERJAAN
        </h5>
        <textarea
            name="uraian_pekerjaan"
            rows="5"
            class="doc-editor">{{ $technicalSpecification->uraian_pekerjaan }}</textarea>
    </div>

    {{-- Informasi kontrak --}}
        <h5>
        5. NAMA DAN ORGANISASI PENGGUNA JASA
    </h5>

    <table class="doc-table">
    <tr>
    <td width="250">
    Pemerintah Daerah
    </td>
    <td>
    :
    </td>
    <td>
    Kabupaten Bengkayang
    </td>
    </tr>
    <tr>
    <td>
    Nama PPK
    </td>
    <td>
    :
    </td>
    <td>
    {{ $technicalSpecification->nama_ppk }}
    </td>
    </tr>
    </table>

    <div class="mt-5 text-right">

        Bengkayang,
        {{ now()->translatedFormat('d F Y') }}

        <br><br><br><br>

        <strong>
            {{ $technicalSpecification->nama_ppk }}
        </strong>

        <br>

        {{ $technicalSpecification->pangkat_gol_ppk }}

        <br>

        NIP.
        {{ $technicalSpecification->nip_ppk }}

    </div>
</div>
    <div class="card">
        <div class="card-body p-4">
            <div class="mb-4 text-center">
                <h4 class="font-weight-bold mb-1">SPESIFIKASI TEKNIS</h4>
                <div>{{ $procurementPackage->package->nama_paket }}</div>
            </div>

            <table class="table table-bordered mb-4">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Paket Pengadaan</th>
                        <td>{{ $procurementPackage->package->nama_paket }}</td>
                    </tr>
                    <tr>
                        <th>ID RUP</th>
                        <td>{{ $procurementPackage->package->id_rup ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Program / Kegiatan</th>
                        <td>
                            {{ $procurementPackage->package->program?->kode }} {{ $procurementPackage->package->program ? '- '.$procurementPackage->package->program->nama : '' }}
                            <br>
                            {{ $procurementPackage->package->activity?->kode }} {{ $procurementPackage->package->activity ? '- '.$procurementPackage->package->activity->nama : '' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <h5 class="font-weight-bold">1. Latar Belakang</h5>
                <textarea
                name="latar_belakang"
                rows="10"
                class="form-control mb-4">{{ old(
                    'latar_belakang',
                    $technicalSpecification->latar_belakang
                ) }}</textarea>

            <h5 class="font-weight-bold">2. Maksud dan Tujuan</h5>
            <textarea
                name="maksud"
                rows="5"
                class="form-control mb-4">{{ old(
                    'maksud',
                    $technicalSpecification->maksud
                ) }}</textarea>

            <h5 class="font-weight-bold">3. Target dan Sasaran</h5>
            <textarea
                name="target_sasaran"
                rows="5"
                class="form-control mb-4">{{ old(
                    'target_sasaran',
                    $technicalSpecification->target_sasaran
                ) }}</textarea>

            <h5 class="font-weight-bold">4. Uraian Pekerjaan</h5>
            <textarea
                name="uraian_pekerjaan"
                rows="5"
                class="form-control mb-4">{{ old(
                    'uraian_pekerjaan',
                    $technicalSpecification->uraian_pekerjaan
                ) }}</textarea>

            <h5 class="font-weight-bold">5. Informasi Kontrak</h5>
            <table class="table table-bordered mb-4">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Jenis Kontrak</th>
                        <td>{{ $technicalSpecification->jenis_kontrak ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Jangka Waktu</th>
                        <td>
                            @if($jangkaWaktu)
                                {{ $jangkaWaktu }} hari
                                {{ $technicalSpecification->jangka_waktu_jenis ? '('.($jangkaWaktuJenis[$technicalSpecification->jangka_waktu_jenis] ?? $technicalSpecification->jangka_waktu_jenis).')' : '' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Garansi</th>
                        <td>{{ $garansiText }}</td>
                    </tr>
                    <tr>
                        <th>Layanan Purna Jual</th>
                        <td>{{ $technicalSpecification->layanan_purna_jual ? 'Ya' : 'Tidak' }}</td>
                    </tr>
                </tbody>
            </table>

            <h5 class="font-weight-bold">6. Informasi PPK</h5>
            <table class="table table-bordered mb-4">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Nama PPK</th>
                        <td>{{ $technicalSpecification->nama_ppk ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Pangkat/Golongan</th>
                        <td>{{ $technicalSpecification->pangkat_gol_ppk ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>NIP</th>
                        <td>{{ $technicalSpecification->nip_ppk ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>NPWP Instansi</th>
                        <td>{{ $technicalSpecification->npwp_instansi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>No Telp</th>
                        <td>{{ $technicalSpecification->no_telp_ppk ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $technicalSpecification->email_ppk ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <h5 class="font-weight-bold">7. Rincian Barang/Jasa</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Barang/Jasa</th>
                            <th>Spesifikasi</th>
                            <th class="text-right">Volume</th>
                            <th>Satuan</th>
                            <th>PDN</th>
                            <th class="text-right">TKDN</th>
                            <th>Kode MAK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($technicalSpecification->items as $item)
                            <tr>
                                <td>{{ $item->nama_barang_jasa }}</td>
                                <td>{!! nl2br(e($item->spesifikasi ?? '-')) !!}</td>
                                <td class="text-right">{{ number_format((float) $item->volume, 2, ',', '.') }}</td>
                                <td>{{ $item->satuan ?? '-' }}</td>
                                <td>{{ $item->pdn ? 'Ya' : 'Tidak' }}</td>
                                <td class="text-right">
                                    {{ $item->tkdn !== null ? number_format((float) $item->tkdn, 2, ',', '.').'%' : '-' }}
                                </td>
                                <td>{{ $item->kode_mak ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Belum ada rincian barang/jasa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex justify-content-between">

                <a href="{{ route('procurement-packages.show', $procurementPackage) }}"
                class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Paket
                </a>

                <div>

                    <a href="{{ route(
                        'technical-specifications.edit',
                        $technicalSpecification
                    ) }}"
                    class="btn btn-warning">
                        <i class="fas fa-save"></i>
                        Simpan Perubahan
                    </a>

                    <button
                        type="button"
                        class="btn btn-primary"
                        disabled>
                        <i class="fas fa-arrow-right"></i>
                        Referensi Harga
                    </button>

                </div>

            </div>
            
        </div>
    </div>
</form>    
@stop
