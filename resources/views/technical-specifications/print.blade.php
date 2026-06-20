<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Spesifikasi Teknis</title>
@section('title', 'Dokumen Spesifikasi Teknis')

@section('content_header')
@stop
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
.document-viewer{
    background:#bfbfbf;
    padding:40px 0;
}

.document-paper{
    width:210mm;
    min-height:297mm;

    margin:0 auto 30px auto;

    padding:13mm 15mm;

    background:#fff;

    box-shadow:
        0 0 0 1px #ddd,
        0 3px 12px rgba(0,0,0,.15);
}

.document-paper::after{
    content:"Halaman A4";
    position:absolute;
    bottom:-24px;
    left:50%;
    transform:translateX(-50%);

    font-size:11px;
    color:#888;
}

.kop-pemerintah{
    font-size:16pt;
    text-transform:uppercase;
    line-height:1.1;
}

.kop-dinas{
    font-size:17pt;
    font-weight:bold;
    text-transform:uppercase;
    line-height:1.15;
}

.kop-alamat{
    font-size:10pt;
    line-height:1.1;
    margin-bottom:0;
}

.judul-dokumen{
    margin-top:15px;
    margin-bottom:20px;
    font-size:12pt;
    font-weight:bold;
    text-transform:uppercase;
    text-align:center;
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
    padding:8px 10px;
    vertical-align:top;
}

.spesifikasi-table th{
    text-align:center;
    font-weight:bold;
}

.spesifikasi-table td{
    text-align:left;
}

.spesifikasi-table td.text-center{
    text-align:center;
    vertical-align:middle;
    vertical-align: top;
}

.spesifikasi-table td,
.spesifikasi-table th{
    word-wrap:break-word;
    overflow-wrap:break-word;
}

.spesifikasi-table td:nth-child(2),
.spesifikasi-table td:nth-child(3){
    padding-left:10px;
}

.admin-table {
    border-collapse: collapse;
    width: 100%;
}

.label-col {
    width: 180px;
    vertical-align: top;
}

.no-col{
    width:35px;
    vertical-align:top;
}

.uraian-col{
    width:160px;
    vertical-align:top;
}

.colon-col{
    width:15px;
    text-align:center;
    vertical-align:top;
}

.isi-col{
    vertical-align: top;
    text-align: justify;
}

.admin-table td {
    padding: 2px 0;
    vertical-align: top;
}

</style>
</head>
<body>
@section('content')


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

    $jangkaWaktuNilai = $procurementPackage->jangka_waktu_nilai ?? null;
    $jangkaWaktuSatuan = $procurementPackage->jangka_waktu_satuan ?? 'hari';
    $garansiNilai = $procurementPackage->garansi_nilai;
    $garansiSatuan = $procurementPackage->garansi_satuan;
    $layananPurnaJual = $procurementPackage->layanan_purna_jual;
@endphp

    {{-- DOKUMEN VIEWER (A4) --}}
           
    <div class="document-paper">
{{-- KOP SURAT --}}
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:110px; text-align:center; padding-top:5px; vertical-align:middle;">
                        <img
                            src="{{ public_path('images/logo-bengkayang.png') }}"
                            style="width:80px;">
                    </td>
                    <td style="text-align:center;">
                        <div class="kop-pemerintah">
                            PEMERINTAH KABUPATEN BENGKAYANG
                        </div>
                        <div class="kop-dinas">
                            DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN,<br>
                            PERTANAHAN DAN LINGKUNGAN HIDUP
                        </div>
                        <div class="kop-alamat">
                            Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat
                        </div>
                        <div class="kop-alamat">
                            Situs : bengkayangkab.go.id
                        </div>
                    </td>
                </tr>
            </table>
            <hr style="border-top:3px solid #000; border-bottom:1px solid #000; margin-top:5px; margin-bottom: 2px; padding-bottom: 2px;">

            <div class="text-center mt-4 mb-4">
                <div class="judul-dokumen">
                    SPESIFIKASI TEKNIS<br>
                    {{ $procurementPackage->package->nama_paket }}
                </div>
            </div>

            {{--Latar Belakang dan Maksud Tujuan--}}
            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">1.</td>

                    <td class="uraian-col">
                        Latar Belakang
                    </td>

                    <td class="colon-col">:</td>

                    <td style="
                        white-space: pre-line;
                        text-align: justify;
                    ">{{ $technicalSpecification->latar_belakang }}
                    </td>
                </tr>
            </table>
            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">2.</td>

                    <td class="uraian-col">
                        Maksud dan Tujuan
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">
                        <table class="admin-table">
                            <tr>
                                <td style="width:20px; vertical-align:top;">a.</td>
                                <td style="text-align:justify;">
                                    <strong>Maksud</strong><br>
                                    <div style="white-space: pre-wrap;">{{ $technicalSpecification->maksud['Maksud'] ?? '' }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="height:8px;"></td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top;">b.</td>
                                <td style="text-align:justify;">
                                    <strong>Tujuan</strong><br>
                                    <div style="white-space: pre-wrap;">{{ $technicalSpecification->maksud['Tujuan'] ?? '' }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <table class="admin-table section-row">
                    <tr>
                    <td class="no-col">3.</td>
                    <td class="uraian-col">Target dan Sasaran</td>
                    <td class="colon-col">:</td>
                    <td class="isi-col">
                        <table class="admin-table">
                            <tr>
                                <td style="width:20px; vertical-align:top;">a.</td>
                                <td style="text-align:justify;">
                                    <strong>Target</strong><br>
                                    <div style="white-space: pre-wrap;">{{ $technicalSpecification->target_sasaran['Target'] ?? '' }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="height:8px;"></td>
                            </tr>
                            <tr>
                                <td style="vertical-align:top;">b.</td>
                                <td style="text-align:justify;">
                                    <strong>Sasaran</strong><br>
                                    <div style="white-space: pre-wrap;">{{ $technicalSpecification->target_sasaran['Sasaran'] ?? '' }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">4.</td>
                    <td class="uraian-col">Uraian Pekerjaan</td>
                    <td class="colon-col">:</td>
                    <td style="white-space: pre-line; text-align: justify">{{ $technicalSpecification->uraian_pekerjaan }}</td>
                </tr>
            </table>

            {{-- Informasi kontrak --}}
            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">5.</td>
                    <td class="uraian-col">Nama dan Organisasi Pengguna Jasa</td>
                    <td class="colon-col">:</td>
                    <td class="isi-col">
                        <table class="admin-table">
                            <tr>
                                <td style="width:20px;">a.</td>
                                <td style="width:60px;">Pemerintah Daerah</td>
                                <td style="width:15px;">:</td>
                                <td style="text-align:justify;">Kabupaten Bengkaayng
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" style="height:8px;"></td>
                            </tr>
                            <tr>
                                <td>b.</td>
                                <td>Perangkat Daerah</td>
                                <td>:</td>
                                <td style="text-align:justify;">DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP
                                </td>
                            </tr>
                            <tr>
                                <td>c.</td>
                                <td>Nama PPK/Pengguna Anggaran/Kuasa Pengguna Anggaran:</td>
                                <td>:</td>
                                <td style="text-align:justify;">{{ $procurementPackage->nama_ppk ?? '-' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>    
                
            <table class="admin-table section-row">
            <tr>
                <td class="no-col">6.</td>

                <td class="uraian-col">
                    Program,<br>
                    Kegiatan dan<br>
                    Pekerjaan
                </td>

                <td class="colon-col">:</td>

                <td class="isi-col">

                    <table class="admin-table">
                        <tr>
                            <td style="width:20px;">a.</td>
                            <td style="width:140px;">Program</td>
                            <td style="width:15px;">:</td>
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

                </td>
            </tr>
        </table>

           <table class="admin-table section-row">
                <tr>
                    <td class="no-col">7.</td>

                    <td class="uraian-col">
                        Sumber Dana<br>
                        dan Pagu<br>
                        Anggaran
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">

                        <table class="admin-table">
                            <tr>
                                <td style="width:20px;">a.</td>
                                <td style="width:140px;">Sumber Dana</td>
                                <td style="width:15px;">:</td>
                                <td>
                                    APBD Tahun Anggaran
                                    {{ $procurementPackage->package->fiscalYear?->tahun }}
                                </td>
                            </tr>

                            <tr>
                                <td>b.</td>
                                <td>Pagu Anggaran</td>
                                <td>:</td>
                                <td>
                                    Rp {{ number_format(
                                        (float) $procurementPackage->package->pagu,
                                        2,
                                        ',',
                                        '.'
                                    ) }}
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">8.</td>

                    <td class="uraian-col">
                        Tempat Tujuan<br>
                        Pengiriman
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">
                        Kantor Dinas Perumahan Rakyat dan Kawasan Permukiman,
                        Pertanahan dan Lingkungan Hidup Kabupaten Bengkayang,
                        Jalan Guna Baru Trans Rangkang,
                        Bengkayang, Kalimantan Barat,
                        Kode Pos 79211.
                    </td>
                </tr>
            </table>

            <table class="admin-table section-row">
            <tr>
                <td class="no-col">9.</td>

                <td class="uraian-col">
                    Spesifikasi Teknis<br>
                    Barang/Jasa
                </td>

                <td class="colon-col">:</td>

                <td class="isi-col">

                    <table class="spesifikasi-table">
                        <thead>
                            <tr>
                                <th>No</th>
                               <th width="30%">Nama Barang/Jasa</th>
                                <th width="38%">Spesifikasi</th>
                                <th width="7%">Vol</th>
                                <th width="8%">Satuan</th>
                                <th width="7%">PDN</th>
                                <th width="10%">TKDN</th>
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

                </td>
            </tr>
        </table>

            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">10.</td>

                    <td class="uraian-col">
                        {{ $isBarang
                            ? 'Jangka Waktu Penyerahan/Pengiriman Barang'
                            : 'Jangka Waktu Pekerjaan'
                        }}
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">

                        @if($jangkaWaktuNilai)

                            @if($isBarang)

                                {{ $jangkaWaktuNilai }}
                                {{ ucfirst($jangkaWaktuSatuan) }}
                                kalender sejak tanggal penandatanganan kontrak.

                            @else

                                {{ $jangkaWaktuNilai }}
                                {{ ucfirst($jangkaWaktuSatuan) }}
                                kalender sejak tanggal penandatanganan kontrak.

                            @endif

                        @else

                            -

                        @endif

                    </td>
                </tr>
            </table>

            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">11.</td>

                    <td class="uraian-col">
                        Spesifikasi<br>
                        Pelayanan
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">

                        <table class="admin-table">

                            <tr>
                                <td style="width:20px;">a.</td>
                                <td style="width:180px;">Garansi Barang</td>
                                <td style="width:15px;">:</td>
                                <td>
                                    @if($garansiNilai)
                                        {{ $garansiNilai }}
                                        {{ $garansiSatuan }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td>b.</td>
                                <td>Layanan Purna Jual</td>
                                <td>:</td>
                                <td>
                                    {{ $layananPurnaJual ? 'Ada' : 'Tidak Ada' }}
                                </td>
                            </tr>

                        </table>

                    </td>
                </tr>
            </table>

           <table class="admin-table section-row">
                <tr>
                    <td class="no-col">12.</td>

                    <td class="uraian-col">
                        DPA
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">
                        DPA/A.1/1.04.2.11.2.10.04.0000/001/{{ $procurementPackage->package->fiscalYear?->tahun }}
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">Kode MAK</td>
                    <td class="colon-col">:</td>
                    <td>
                        @foreach($technicalSpecification->items as $item)
                            {{ $item->kode_mak }}<br>
                        @endforeach
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">Kode RUP</td>
                    <td class="colon-col">:</td>
                    <td>{{ $procurementPackage->package->id_rup }}</td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">NPWP Instansi</td>
                    <td class="colon-col">:</td>
                    <td>{{ $procurementPackage->npwp_instansi }}</td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">Alamat Email PPK</td>
                    <td class="colon-col">:</td>
                    <td>{{ $procurementPackage->email_ppk }}</td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">No. Telp</td>
                    <td class="colon-col">:</td>
                    <td>{{ $procurementPackage->no_telp_ppk }}</td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">Tahun Anggaran</td>
                    <td class="colon-col">:</td>
                    <td>{{ $procurementPackage->package->fiscalYear?->tahun }}</td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">Waktu Awal</td>
                    <td class="colon-col">:</td>
                    <td>
                        Jangka waktu penyerahan/pengiriman barang adalah
                        {{ $jangkaWaktuNilai }}
                        {{ $jangkaWaktuSatuan }}
                        terhitung sejak tanggal penandatanganan kontrak.
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td class="uraian-col">Catatan</td>
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

            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">13.</td>

                    <td class="uraian-col">
                        Jenis Kontrak
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">
                        {{ $procurementPackage->jenis_kontrak ?? '-' }}
                    </td>
                </tr>
            </table>

            <table class="admin-table section-row">
                <tr>
                    <td class="no-col">14.</td>

                    <td class="uraian-col">
                        Referensi Harga
                    </td>

                    <td class="colon-col">:</td>

                    <td class="isi-col">
                        Terlampir.
                    </td>
                </tr>
            </table>

            <div class="section-row" style="margin-top:25px; text-align:justify;">
                Demikian Spesifikasi Teknis ini dibuat sebagai acuan dalam
                pelaksanaan paket pekerjaan
                {{ $procurementPackage->package->nama_paket }}.
            </div>

            <table style="width:100%; margin-top:40px;">
                <tr>
                    <td style="width:55%;"></td>

                    <td style="width:45%; text-align:center; vertical-align:top;">

                        Bengkayang,
                        {{ $technicalSpecification->tanggal ? \Carbon\Carbon::parse($technicalSpecification->tanggal)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                        <br><br>

                        Pejabat Pembuat Komitmen<br>
                        Dinas Perumahan Rakyat dan Kawasan Permukiman,
                        Pertanahan dan Lingkungan Hidup<br>
                        Kabupaten Bengkayang

                        <br><br><br><br><br>

                        <strong>
                            <u>{{ $procurementPackage->nama_ppk ?? '-' }}</u>
                        </strong>

                        <br>

                        {{ $procurementPackage->pangkat_gol_ppk ?? '-' }}

                        <br>

                        NIP. {{ $procurementPackage->nip_ppk ?? '-' }}

                    </td>
                </tr>
            </table>
    </div>

       
    </div>

</div>
</body>
</html>
