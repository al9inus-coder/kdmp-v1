@extends('adminlte::page')

@section('title', 'Dokumen Spesifikasi Teknis')

@section('content_header')
@stop
@push('css')
<style>
/* TOMBOL MELENGKUNG (PILL) & EFEK HOVER */
    .btn-floating {
        border-radius: 50px !important;
        padding: 12px 25px !important;
        font-weight: bold;
        font-size: 1.05rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    }

    .btn-floating:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.4) !important;
    }

    .btn-warning.btn-floating {
        color: #212529 !important; 
    }

    /* WADAH TOMBOL STICKY (Otomatis menyesuaikan lebar document-viewer) */
    .viewer-sticky-actions {
        position: sticky;
        /* Menahan posisi tombol agar selalu ~160px dari dasar layar monitor */
        top: calc(100vh - 160px); 
        z-index: 999;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        
        /* Jarak tombol dari tepi kiri dan tepi kanan document-viewer (kotak abu-abu) */
        padding: 0 40px; 
        
        /* Tinggi dibuat 0 agar tidak memakan ruang dan mendorong kertas ke bawah */
        height: 0; 
        pointer-events: none; /* Area kosong transparan tidak menghalangi klik ke dokumen */
    }

    /* Kembalikan fungsi klik khusus untuk area yang ada tombolnya */
    .viewer-sticky-actions .action-left,
    .viewer-sticky-actions .action-right {
        pointer-events: auto;
    }

    /* Susun tombol Edit & Cetak ke bawah rata kanan */
    .viewer-sticky-actions .action-right {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

/* Latar Belakang bergaya PDF Viewer */
    .document-viewer {
        background-color: #a7a7a7;
        padding: 30px 0;
        border-radius: 5px;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.5);
    }

    /* Pengaturan Kertas A4 Dinamis */
    .document-paper {
        width: 210mm;
        min-height: 297mm;
        margin: auto;
        
        /* Padding standar dokumen resmi (Atas: 3cm, Kanan: 2cm, Bawah: 2cm, Kiri: 3cm) */
        padding: 13mm 15mm 15mm 15mm;
        background: #fff;
        
        font-family: Arial, sans-serif;
        font-size: 11pt; /* Lebih proporsional untuk cetak */
        color: #000;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        
        /* TRICK: Garis penanda batas halaman A4 otomatis (setiap 297mm) */
        background-image: repeating-linear-gradient(
            to bottom,
            transparent,
            transparent 296mm,
            rgba(255, 0, 0, 0.6) 296mm,
            rgba(255, 0, 0, 0.6) 297mm
        );
        position: relative;
    }

    .document-paper::after {
        content: "--- Batas Halaman A4 ---";
        position: absolute;
        top: 297mm;
        left: 0;
        right: 0;
        text-align: center;
        color: rgba(255, 0, 0, 0.6);
        font-size: 10pt;
        font-weight: bold;
        margin-top: -15px; /* Menyesuaikan dengan garis merah */
        pointer-events: none;
    }

.kop-pemerintah{
    font-size:14pt;
    text-transform:uppercase;
    line-height:1.1;
    margin-bottom:2px;
}

.kop-dinas{
    font-size:15pt;
    font-weight:bold;
    text-transform:uppercase;
    line-height:1.15;
    margin-bottom:4px;
}

.kop-alamat{
    font-size:10pt;
    line-height:1.1;
    margin-bottom:0;
}

.judul-dokumen{
    font-size:12pt;
    font-weight:bold;
    text-transform:uppercase;
}

    /* Input Editor dalam Dokumen */
    .doc-editor {
        width: 100%;
        border: 1px dashed #adb5bd; /* Tanda bahwa ini form yang bisa diedit */
        border-radius: 4px;
        padding: 10px;
        font-family: Arial, sans-serif;
        font-size: 11pt;
        line-height: 1.5;
        resize: vertical;
        min-height: 100px;
        background: #fdfdfd;
        transition: all 0.3s ease;
    }

    .doc-editor:focus {
        border: 1px solid #007bff;
        background: #fff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
        outline: none;
    }

.section-row{
    margin-bottom:20px;
}

.ttd-area{
    margin-top:50px;
}

.doc-mini-table{
    width:100%;
    font-size:12pt;
}

.doc-mini-table td{
    padding:1px 4px;
    border:none !important;
    vertical-align:top;
}

.doc-mini-table tr{
    border:none !important;
}

.spesifikasi-table{
    width:100%;
    border-collapse:collapse;
    font-size:11pt;
}

.spesifikasi-table th,
.spesifikasi-table td{
    border:1px solid #000;
    padding:6px;
    vertical-align:top;
}

.spesifikasi-table th{
    text-align:center;
    font-weight:bold;
}

.admin-table {
    border-collapse: collapse;
    width: 100%;
}

.admin-table td {
    border: none;
    padding: 1px 0;
    vertical-align: top;
}

.label-col {
    width: 180px;
    white-space: nowrap;
}

.colon-col {
    width: 25px;
    text-align: center;
}


</style>
@endpush
@section('content')
@include('components.procurement-progress', [
    'procurementPackage' => $procurementPackage
])
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

    $targetSasaran =
        $technicalSpecification->target_sasaran
        ?? $technicalSpecification->sasaran;

    $jangkaWaktu =
        $technicalSpecification->jangka_waktu
        ?? $technicalSpecification->jangka_waktu_hari;

    $maksudTujuan = collect([
        $technicalSpecification->maksud,
        $technicalSpecification->tujuan,
    ])->filter()->unique()->implode("\n\n");

    $isBarang =
        ($procurementPackage->package->jenis_pengadaan ?? '')
        === 'Barang';

    $jangkaWaktuNilai =
        $procurementPackage->jangka_waktu_nilai ?? null;

    $jangkaWaktuSatuan =
        $procurementPackage->jangka_waktu_satuan ?? 'hari';

    $garansiText = $technicalSpecification->garansi_nilai
        ? $technicalSpecification->garansi_nilai.' '.ucfirst((string) $technicalSpecification->garansi_satuan)
        : ($technicalSpecification->garansi ?? '-');
@endphp
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        
    @endif
    {{-- DOKUMEN VIEWER (A4) --}}
    <div class="document-viewer">
{{-- TOMBOL FLOATING (OTOMATIS TERKURUNG DI DALAM VIEWER) --}}
        <div class="viewer-sticky-actions">
            {{-- Bagian Kiri: Tombol Kembali --}}
            <div class="action-left">
                <a href="{{ route('procurement-packages.show', $procurementPackage->package) }}" 
                   class="btn btn-secondary btn-floating">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
            {{-- Bagian Kanan: Tombol Edit & Cetak --}}
            <div class="action-right">
                <button type="submit" class="btn btn-success btn-floating">
                    Simpan <i class="fas fa-save mr-1"></i> 
                </button>
                <a href="{{ route('procurement-packages.price-references.index', $procurementPackage->package) }}"
                   class="btn btn-primary btn-floating">
                    Ref. Harga <i class="fas fa-arrow-right mr-1"></i>
                </a>
            </div>
            
        </div>
        <div class="document-paper">
            <div class="row">
                <div class="col-2 text-center">
                    <img
                        src="{{ asset('images/logo-bengkayang.png') }}"
                        style="width:70px;">
                </div>
                <div class="col-10 text-center">
                    <div class="kop-pemerintah">
                        PEMERINTAH KABUPATEN BENGKAYANG
                    </div>
                    <div class="kop-dinas">
                        DINAS PERUMAHAN RAKYAT DAN KAWASAN
                        PERMUKIMAN, PERTANAHAN DAN
                        LINGKUNGAN HIDUP
                    </div>
                    <div class="kop-alamat">
                        Jalan Guna Baru Trans Rangkang,
                        Bengkayang, Kalimantan Barat
                    </div>
                    <div class="kop-alamat">
                        Situs : bengkayangkab.go.id
                    </div>
                </div>
            </div>
            <hr style="border-top:3px solid #000; border-bottom:1px solid #000; margin-top:5px; margin-bottom: 2px; padding-bottom: 2px;">

            <div class="text-center mt-4 mb-4">
                <div class="judul-dokumen">
                    SPESIFIKASI TEKNIS<br>
                    {{ $procurementPackage->package->nama_paket }}
                </div>
            </div>

            {{--Latar Belakang dan Maksud Tujuan--}}
            <div class="row section-row">
                <div class="col-3">
                    1. Latar Belakang
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    <textarea
                        rows="4"
                        class="doc-editor"
                        name="latar_belakang">{{ $technicalSpecification->latar_belakang }}</textarea>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    2. Maksud dan Tujuan
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    <textarea
                        rows="4"
                        class="doc-editor"
                        name="maksud">{{ $technicalSpecification->maksud }}</textarea>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    3. Target dan Sasaran
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    <textarea
                        rows="4"
                        class="doc-editor"
                        name="target_sasaran">{{ $technicalSpecification->target_sasaran }}</textarea>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    4. Uraian Pekerjaan
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    <textarea
                        rows="6"
                        class="doc-editor"
                        name="uraian_pekerjaan">{{ $technicalSpecification->uraian_pekerjaan }}</textarea>
                </div>
            </div>

            {{-- Informasi kontrak --}}
            <div class="row section-row">
                <div class="col-3">
                    5. Nama dan Organisasi<br>
                    Pengguna Jasa
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    <div class="row mb-2">
                        <div class="col-1">a.</div>
                        <div class="col-11">
                            Pemerintah Daerah:<br>
                            KABUPATEN BENGKAYANG
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-1">b.</div>
                        <div class="col-11">
                            Perangkat Daerah:<br>
                            DINAS PERUMAHAN RAKYAT DAN KAWASAN
                            PERMUKIMAN, PERTANAHAN DAN
                            LINGKUNGAN HIDUP
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-1">c.</div>
                        <div class="col-11">
                            Nama PPK/Pengguna Anggaran/Kuasa Pengguna Anggaran:<br>
                            {{ $procurementPackage->nama_ppk ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    6. Program,<br>
                    Kegiatan dan<br>
                    Pekerjaan
                </div>
                <div class="col-1 text-center">:</div>  
                <div class="col-8">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td width="20">a.</td>
                            <td width="130">Program</td>
                            <td width="18">:</td>
                            <td>
                                {{ $procurementPackage->package->program?->nama }}
                            </td>
                        </tr>
                        <tr>
                            <td>b.</td>
                            <td>Kegiatan</td>
                            <td>:</td>
                            <td>
                                {{ $procurementPackage->package->activity?->nama }}
                            </td>
                        </tr>
                        <tr>
                            <td>c.</td>
                            <td>Sub Kegiatan</td>
                            <td>:</td>
                            <td>
                                {{ $procurementPackage->package->subActivity?->nama }}
                            </td>
                        </tr>
                        <tr>
                            <td>d.</td>
                            <td>Pekerjaan</td>
                            <td>:</td>
                            <td>
                                {{ $procurementPackage->package->nama_paket }}
                            </td>
                        </tr>
                        <tr>
                            <td>e.</td>
                            <td>Sumber Dana</td>
                            <td>:</td>
                            <td>
                                APBD Tahun Anggaran
                                {{ $procurementPackage->package->fiscalYear?->tahun ?? date('Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td>f.</td>
                            <td>Nomor Rekening</td>
                            <td>:</td>
                            <td>
    {{ $procurementPackage->package->account?->kode ?? '-' }}
</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    7. Sumber Dana<br>
                    dan Pagu<br>
                    Anggaran
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    <div class="row mb-3">
                        <div class="col-1">
                            a.
                        </div>
                        <div class="col-11">
                            Sumber dana yang diperlukan untuk
                            membiayai pengadaan barang bersumber dari
                            APBD Tahun Anggaran
                            {{ $procurementPackage->package->fiscalYear?->tahun }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-1">
                            b.
                        </div>
                        <div class="col-11">
                            Total Pagu Anggaran
                            Rp {{ number_format(
                                (float)$procurementPackage->package->pagu,
                                2,
                                ',',
                                '.'
                            ) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    8. Tempat tujuan<br>
                    pengiriman
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    Kantor Dinas Perumahan Rakyat dan Kawasan
                    Permukiman, Pertanahan dan Lingkungan Hidup
                    Kabupaten Bengkayang,
                    Jalan Guna Baru Trans Rangkang,
                    Bengkayang, Kalimantan Barat,
                    Kode Pos 79211.
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    9. Spesifikasi Teknis<br>
                    Barang/Jasa
                </div>
                <div class="col-1 text-center">
                    :
                </div>
                <div class="col-8">
                    <table class="spesifikasi-table">
                        <thead>
                        <tr>
                            <th width="5%">
                                No
                            </th>
                            <th width="25%">
                                Nama Barang/Jasa
                            </th>
                            <th width="35%">
                                Spesifikasi
                            </th>
                            <th width="10%">
                                Volume
                            </th>
                            <th width="10%">
                                Satuan
                            </th>
                            <th width="7%">
                                PDN
                            </th>
                            <th width="8%">
                                TKDN
                            </th>
                        </tr>
                        </thead>
                    <tbody>
                        @foreach($technicalSpecification->items as $item)
                        <tr>
                            <td class="text-center">
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                {{ $item->nama_barang_jasa }}
                            </td>
                            <td>
                                {!! nl2br(e($item->spesifikasi)) !!}
                            </td>
                            <td class="text-center">
                                {{ number_format($item->volume,0,',','.') }}
                            </td>
                            <td class="text-center">
                                {{ $item->satuan }}
                            </td>
                            <td class="text-center">
                                {{ $item->pdn ? 'Ya' : 'Tidak' }}
                            </td>
                            <td class="text-center">
                                {{ $item->tkdn !== null
                                    ? number_format($item->tkdn,1,',','.') . '%'
                                    : '-'
                                }}
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    10.
                    {{ $isBarang
                        ? 'Jangka waktu penyerahan/pengiriman barang'
                        : 'Jangka waktu pekerjaan'
                    }}
                </div>

                <div class="col-1 text-center">
                    :
                </div>

                <div class="col-8">

                    @if($jangkaWaktuNilai)

                        @if($isBarang)

                            Jangka waktu penyerahan/pengiriman barang adalah
                            <strong>{{ $jangkaWaktuNilai }}</strong>
                            {{ $jangkaWaktuSatuan }}
                            kalender terhitung sejak tanggal penandatanganan kontrak.

                        @else

                            Jangka waktu pelaksanaan pekerjaan adalah
                            <strong>{{ $jangkaWaktuNilai }}</strong>
                            {{ $jangkaWaktuSatuan }}
                            kalender terhitung sejak tanggal penandatanganan kontrak.

                        @endif

                    @else

                        -

                    @endif

                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    11. Spesifikasi<br>
                    Pelayanan
                </div>

                <div class="col-1 text-center">
                    :
                </div>

                <div class="col-8">
                    <table class="doc-mini-table">
                        <tr>
                            <td width="25">a.</td>
                            <td width="220">
                                Garansi barang selama
                            </td>
                            <td width="20">:</td>
                            <td>
                                @if($procurementPackage->garansi_nilai)
                                    {{ $procurementPackage->garansi_nilai }}
                                    {{ $procurementPackage->garansi_satuan }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <td width="25">b.</td>
                            <td>
                                Layanan purna jual
                            </td>
                            <td width="20">:</td>
                            <td>
                                {{ $procurementPackage->layanan_purna_jual ? 'Ada' : 'Tidak Ada' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-12">
                    <table class="admin-table">
                        <tr>
                            <td class="label-col">12. DPA</td>
                            <td class="colon-col">:</td>
                            <td>DPA/A.1/1.04.2.11.2.10.04.0000/001/{{ $procurementPackage->package->fiscalYear?->tahun }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;Kode MAK</td>
                            <td class="colon-col">:</td>
                            <td>
                            @foreach($technicalSpecification->items as $item)
                                {{ $item->kode_mak }}<br>
                            @endforeach
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;Kode RUP</td>
                            <td class="colon-col">:</td>
                            <td>{{ $procurementPackage->package->id_rup }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;NPWP Instansi</td>
                            <td class="colon-col">:</td>
                            <td>{{ $procurementPackage->npwp_instansi }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;Alamat Email PPK</td>
                            <td class="colon-col">:</td>
                            <td>{{ $procurementPackage->email_ppk }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;No. Telp</td>
                            <td class="colon-col">:</td>
                            <td>{{ $procurementPackage->no_telp_ppk }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;Tahun Anggaran</td>
                            <td class="colon-col">:</td>
                            <td>{{ $procurementPackage->package->fiscalYear?->tahun }}</td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;Waktu awal</td>
                            <td class="colon-col">:</td>
                            <td>
                                Jangka waktu penyerahan/pengiriman barang adalah
                                {{ $jangkaWaktuNilai }} {{ $jangkaWaktuSatuan }}
                                terhitung sejak tanggal penandatanganan kontrak.
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">&nbsp;&nbsp;&nbsp;Catatan</td>
                            <td class="colon-col">:</td>
                            <td>
                                Barang diterima paling lambat tanggal
                                    {{ $procurementPackage->tanggal_barang_diterima
                                        ? \Carbon\Carbon::parse(
                                            $procurementPackage->tanggal_barang_diterima
                                        )->translatedFormat('d F Y')
                                        : '-'
                                    }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">
                    13. Jenis Kontrak
                </div>
                <div class="col-1 text-center">
                    :
                </div>
            <div class="col-8">
                    Jenis kontrak yang digunakan adalah kontrak
                        {{ $procurementPackage->jenis_kontrak ?? '-' }}
                </div>
            </div>

            <div class="row section-row">
                <div class="col-3">14. Referensi Harga</div>
                <div class="col-1 text-center">:</div>
                <div class="col-8">Terlampir.</div>
            </div>

            <div class="mt-3 text-justify">
                Demikian Spesifikasi Teknis ini dibuat sebagai acuan dalam pelaksanaan paket pekerjaan Pengadaan Barang.
            </div>

            <div class="row mt-4">
                <div class="col-5"></div>
                    <div class="col-7 text-center">
                        Bengkayang,
                        {{ now()->translatedFormat('d F Y') }}
                        <br><br>
                        Pejabat Pembuat Komitmen<br>
                        Dinas Perumahan Rakyat dan Kawasan Permukiman, Pertanahan dan Lingkungan Hidup<br>
                        Kabupaten Bengkayang
                        <br><br><br><br>
                        <strong><u>
                        {{ $procurementPackage->nama_ppk ?? '-' }}</u>
                        </strong>
                        <br>
                        {{ $procurementPackage->pangkat_gol_ppk ?? '-' }}<br>
                        NIP.
                        {{ $procurementPackage->nip_ppk ?? '-' }}
                    </div>
                </div>
            </div>
    </div>

    {{-- Tambahkan Tombol Submit di sini agar form bisa disimpan --}}
    <div class="mt-3 d-flex justify-content-between">
<p class="text-align:justify">  FILIPI 3 : 14 </div>
        
    </div>

</div>
</form>    
@stop
