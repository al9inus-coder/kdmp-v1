@extends('adminlte::page')

@section('title', 'Surat Permohonan Pengadaan')

@section('content_header')
@stop
<style>

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
        padding: 11mm 15mm 11mm 15mm;
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
        color: rgba(250, 82, 82, 0.6);
        font-size: 10pt;
        font-weight: bold;
        margin-top: -11px; /* Menyesuaikan dengan garis merah */
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
@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- DOKUMEN VIEWER (A4) --}}
    <div class="document-viewer">
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

            <div class="row mt-2">
                <div class="col-12 text-right">
                    Bengkayang,
                    {{ $procurementRequest->tanggal_surat?->translatedFormat('d F Y') }}
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-12">
                    <table class="admin-table">
                        <tr>
                            <td width="70">Nomor</td>
                            <td class="colon-col">:</td>
                            <td>{{ $nomorSuratLengkap }}</td>
                        </tr>
                        <tr>
                            <td width="70">Sifat</td>
                            <td class="colon-col">:</td>
                            <td>Segera</td>
                        </tr>
                        <tr>
                            <td width="70">Lampiran</td>
                            <td class="colon-col">:</td>
                            <td>1 (satu) berkas</td>
                        </tr>
                        <tr>
                            <td width="70">Hal</td>
                            <td class="colon-col">:</td>
                            <td>
                                Permohonan Pemesanan Barang/Jasa
                                melalui {{ $procurementPackage->package->metode_pengadaan }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mb-2">
                Yth.<br>
                Sdr. {{ $procurementRequest->nama_pejabat_pengadaan ?? 'Nama Pejabat Pengadaan' }}<br>
                (Pejabat Pengadaan Barang/Jasa {{ config('app.instansi', 'Dinas ...') }})<br>
                di -
                <div class="ml-4">
                TEMPAT
                </div>
            </div>

            <div style="text-align:justify">
                Dengan hormat,
                <br>
                Dalam rangka memenuhi kebutuhan barang dan jasa pada di lingkungan Dinas Perumahan Rakyat dan Kawasan Permukiman, 
                Pertanahan dan Lingkungan Hidup Kabupaten Bengkayang, maka dengan ini diminta kepada Saudara untuk dapat melaksanakan 
                proses pengadaan barang/jasa melalui metode {{ $procurementPackage->package->metode_pengadaan }} dengan data paket sebagai berikut :
            </div>
{{--tabel spesifikasi teknis--}}
            @php
                $items = $procurementPackage
                    ->technicalSpecification
                    ?->items ?? collect();

                $grandTotal = 0;
            @endphp
            <table class="spesifikasi-table mt-1">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Uraian Barang</th>
                        <th width="70">Satuan</th>
                        <th width="80">Jumlah</th>
                        <th width="170">Harga Satuan DPA</th>
                        <th width="140">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    @php
                        $hargaSatuanDpa =
                            (float) $item->harga_satuan_dpa;
                        $jumlah =
                            $hargaSatuanDpa *
                            (float) $item->volume;
                        $grandTotal += $jumlah;
                    @endphp
                    <tr>
                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>
                        <td>
                            {{ $item->nama_barang_jasa }}
                        </td>
                        <td class="text-center">
                            {{ $item->satuan }}
                        </td>
                        <td class="text-center">
                            {{ number_format(
                                (float) $item->volume,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format(
                                $hargaSatuanDpa,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                        <td class="text-right">
                            Rp {{ number_format(
                                $jumlah,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5"
                        class="text-center font-weight-bold">
                        Jumlah Total
                    </td>
                    <td class="text-right font-weight-bold">
                        Rp {{ number_format(
                            $grandTotal,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="mt-1">
                <em>* Harga sudah termasuk pajak (PPN)</em>
            </div>
            <div class="row mt-1">
                <div class="col-12">
                    <table class="admin-table">
                        <tr>
                            <td class="label-col">
                                Nama Paket
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                {{ $procurementPackage->package->nama_paket }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Lokasi Pelaksanaan
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                Kabupaten Bengkayang
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Sumber Dana
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                APBD Tahun Anggaran
                                {{ $procurementPackage->package->fiscalYear?->tahun }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Kode Rekening
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                {{ $procurementPackage->package->account?->kode ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Pagu Anggaran
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                Rp {{ number_format(
                                    (float) $procurementPackage->package->pagu,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Tahun Anggaran
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                Tahun Anggaran
                                {{ $procurementPackage->package->fiscalYear?->tahun }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                USer Akun PPK
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                {{ $procurementPackage->user_ppk ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Email PPK
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                {{ $procurementPackage->email_ppk }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Kode RUP
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                {{ $procurementPackage->package->id_rup }}
                            </td>
                        </tr>
                        <tr>
                            <td class="label-col">
                                Rekomendasi Penyedia
                            </td>
                            <td class="colon-col">:</td>
                            <td>
                                {{ $procurementRequest->nama_penyedia ?: '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td class="label-col">
                                Alasan Pemilihan Penyedia
                            </td>
                            <td class="colon-col">:</td>
                            <td style="text-align: justify;">
                                {{ $procurementRequest->alasan_pemilihan_penyedia ?: '-' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="mt-2 text-justify">Demikian Surat Permohonan ini disampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.</div>
            <div class="mt-2 row ttd-area">
                <div class="col-5"></div>
                <div class="col-7 text-center">
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
    <a href="{{ route(
    'procurement-packages.procurement-request.print',
    $procurementPackage->package
) }}"
   target="_blank"
   class="btn btn-success">

    <i class="fas fa-print mr-1"></i>

    Cetak

</a>
@stop
