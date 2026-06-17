<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
@page {
    size: A4 portrait;
    margin: 15mm 10mm;
}

body {
    background: #f4f6f9;
    font-family: Arial, sans-serif;
    font-size: 12pt;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
}

.document-viewer {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.document-paper {
    background: white;
    width: 210mm;
    min-height: 297mm;
    padding: 15mm;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    box-sizing: border-box;
}

.btn-print {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 1000;
}

.btn-print:hover {
    background: #0056b3;
}

@media print {
    body {
        background: white;
        padding: 0;
    }
    
    .no-print {
        display: none !important;
    }
    
    .document-paper {
        box-shadow: none;
        margin: 0;
        width: 100%;
        min-height: auto;
        padding: 0;
    }

    /* Mencegah tabel terpotong di tengah baris dan mengulang header tabel */
    table { page-break-inside: auto; }
    .spesifikasi-table tr { page-break-inside: avoid; page-break-after: auto; }
    .spesifikasi-table thead { display: table-header-group; }
    
    /* Mencegah baris section terpotong jika memungkinkan */
    .admin-table tr { page-break-inside: avoid; }
}

.kop-pemerintah{
    text-align:center;
    font-size:12pt;
    text-transform:uppercase;
    margin-bottom:2px;
}

.kop-dinas{
    text-align:center;
    font-size:14pt;
    font-weight:bold;
    text-transform:uppercase;
    line-height:1;
    margin-bottom:2px;
}

.kop-alamat{
    text-align:center;
    font-size:10pt;
    line-height:1.2;
}

.garis-kop{
    border-top:2px solid #000;
    border-bottom:1px solid #000;
    height:3px;
    margin-top:8px;
    margin-bottom:5px;
}

.judul-dokumen{
    text-align:center;
    font-weight:bold;
    font-size:12pt;
    text-transform:uppercase;
    margin-bottom:15px;
}

.isi-narasi{
    text-align: justify;
    vertical-align: top;
}

.admin-table{
    width:100%;
    border-collapse:collapse;
}

.admin-table td{
    padding:2px 0;
    vertical-align:top;
}

.no-col{
    width:35px;
}

.uraian-col{
    width:170px;
}

.colon-col{
    width:15px;
    text-align:center;
}

.isi-col{
    vertical-align:top;
}

.spesifikasi-table th{
    border:1px solid #000;
    text-align:center;
    vertical-align:middle;
    font-weight:normal;
}
</style>

</head>
<body>

<button class="btn-print no-print" onclick="window.print()">Cetak</button>

<div class="document-viewer">
    <div class="document-paper">

<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="12%" align="center" valign="top">
            <img src="{{ asset('images/logo-bengkayang.png') }}"
                 width="70">
        </td>

        <td width="88%" align="center">
            <div class="kop-pemerintah">
                PEMERINTAH KABUPATEN BENGKAYANG
            </div>

            <div class="kop-dinas">
                DINAS PERUMAHAN RAKYAT DAN KAWASAN<br>
                PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP
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

<div class="garis-kop"></div>

<div class="judul-dokumen">
    SPESIFIKASI TEKNIS<br>
    {{ strtoupper($procurementPackage->package->nama_paket) }}
</div>


<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="25%" valign="top">
            1. Latar Belakang
        </td>
        <td width="3%" align="center" valign="top">
            :
        </td>
        <td width="72%" valign="top" class="isi-narasi">
            {!! nl2br(e($technicalSpecification->latar_belakang)) !!}
        </td>
    </tr>

    <tr><td colspan="3" height="15"></td></tr>

    <tr>
        <td valign="top">
            2. Maksud dan Tujuan
        </td>
        <td align="center" valign="top">
            :
        </td>
        <td valign="top" class="isi-narasi">
            {!! nl2br(e($technicalSpecification->maksud)) !!}
        </td>
    </tr>

    <tr><td colspan="3" height="15"></td></tr>

    <tr>
        <td valign="top">
            3. Target dan Sasaran
        </td>
        <td align="center" valign="top">
            :
        </td>
        <td valign="top" class="isi-narasi">

            @php
                $targetData = json_decode(
                    $technicalSpecification->target_sasaran,
                    true
                );
            @endphp

            @if(is_array($targetData))
                <table width="100%" cellpadding="2">
                    <tr>
                        <td width="15" valign="top">a.</td>
                        <td width="50" valign="top">Target</td>
                        <td width="15" valign="top">:</td>
                        <td style="text-align:justify;">
                            {{ $targetData['Target'] ?? '-' }}
                        </td>
                    </tr>

                    <tr>
                        <td width="15" valign="top">b.</td>
                        <td valign="top">Sasaran</td>
                        <td valign="top">:</td>
                        <td style="text-align:justify;">
                            {{ $targetData['Sasaran'] ?? '-' }}
                        </td>
                    </tr>
                </table>
            @else
                {{ $technicalSpecification->target_sasaran }}
            @endif

        </td>
    </tr>

    <tr><td colspan="3" height="15"></td></tr>

    <tr>
        <td valign="top">
            4. Uraian Pekerjaan
        </td>
        <td align="center" valign="top">
            :
        </td>
        <td valign="top" class="isi-narasi">
            {!! nl2br(e($technicalSpecification->uraian_pekerjaan)) !!}
        </td>
    </tr>
</table>

{{-- 5. Nama dan Organisasi Pengguna Jasa --}}
<table width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td width="3%" valign="top">5.</td>
        <td width="25%" valign="top">
            Nama dan Organisasi Pengguna Jasa
        </td>
        <td class="colon-col" valign="top">:</td>

        <td class="isi-col">

            <table class="admin-table">
                <tr>
                    <td style="width:25px;">a.</td>
                    <td style="width:150px;">Pemerintah Daerah</td>
                    <td style="width:15px;">:</td>
                    <td>Kabupaten Bengkayang</td>
                </tr>

                <tr>
                    <td colspan="4" style="height:6px;"></td>
                </tr>

                <tr>
                    <td>b.</td>
                    <td>Perangkat Daerah</td>
                    <td>:</td>
                    <td>
                        DINAS PERUMAHAN RAKYAT DAN KAWASAN
                        PERMUKIMAN, PERTANAHAN DAN
                        LINGKUNGAN HIDUP
                    </td>
                </tr>

                <tr>
                    <td colspan="4" style="height:6px;"></td>
                </tr>

                <tr>
                    <td valign="top">c.</td>
                    <td valign="top">
                        Nama PPK/PA/KPA
                    </td>
                    <td valign="top">:</td>
                    <td valign="top">
                        {{ $procurementPackage->nama_ppk ?? '-' }}
                    </td>
                </tr>

            </table>

        </td>
    </tr>

    <tr>
        <td width="3%" valign="top">6.</td>
        <td width="25%" valign="top">
            Program, Kegiatan dan Pekerjaan
        </td>
        <td class="colon-col" valign="top">:</td>
        <td class="isi-col">
            <table width="100%" cellpadding="2">
                <tr>
                    <td width="20">a.</td>
                    <td width="100">Program</td>
                    <td width="15">:</td>
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
                        {{ $procurementPackage->package->fiscalYear?->tahun }}
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
    <tr>
        <td colspan="3" height="15"></td>
    </tr>

    <tr>
        <td width="3%" valign="top">7.</td>
        <td width="25%" valign="top">
            Sumber Dana dan Pagu Anggaran
        </td>
        <td width="3%" align="center" valign="top">
            :
        </td>

        <td width="72%" valign="top">

            <table width="100%" cellpadding="2">

                <tr>
                    <td width="15" valign="top">a.</td>
                    <td width="100" valign="top">Sumber Dana</td>
                    <td width="15" valign="top">:</td>
                    <td valign="top">
                        APBD Tahun Anggaran
                        {{ $procurementPackage->package->fiscalYear?->tahun }}
                    </td>
                </tr>

                <tr>
                    <td valign="top">b.</td>
                    <td valign="top">Pagu Anggaran</td>
                    <td valign="top">:</td>
                    <td valign="top">
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

    <tr>
        <td colspan="3" height="15"></td>
    </tr>

    <tr>
        <td width="3%" valign="top">8.</td>
            <td width="25%" valign="top">
            Tempat Tujuan Pengiriman
        </td>
        <td width="3%" align="center" valign="top">
            :
        </td>
        <td width="72%" valign="top" class="isi-narasi">
            Kantor Dinas Perumahan Rakyat dan Kawasan Permukiman,
            Pertanahan dan Lingkungan Hidup Kabupaten Bengkayang,
            Jalan Guna Baru Trans Rangkang,
            Bengkayang, Kalimantan Barat,
            Kode Pos 79211.
        </td>
    </tr>

    <tr>
        <td colspan="3" height="15"></td>
    </tr>

<tr>
    <td width="3%" valign="top">9.</td>
            <td width="25%" valign="top">
        Spesifikasi Teknis Barang/Jasa
    </td>
    <td width="3%" align="center" valign="top">
        :
    </td>
    <td class="spesifikasi-table width="72%" valign="top">
        <table
    width="100%"
    cellspacing="0"
    cellpadding="4"
    style="border-collapse:collapse; border:1px solid #000;"
>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="30%">Nama Barang/Jasa</th>
            <th width="38%">Spesifikasi</th>
            <th width="7%">Vol</th>
            <th width="8%">Satuan</th>
            <th width="5%">PDN</th>
            <th width="7%">TKDN</th>
        </tr>
    </thead>

    <tbody>

    @foreach($technicalSpecification->items as $item)

        <tr>

            <td align="center"
                valign="top"
                style="border:1px solid #000;">
                {{ $loop->iteration }}
            </td>

            <td valign="top"
                style="border:1px solid #000;">
                {{ $item->nama_barang_jasa }}
            </td>

            <td valign="top"
                style="border:1px solid #000; text-align:justify;">
                {!! nl2br(e($item->spesifikasi)) !!}
            </td>

            <td align="center"
                valign="top"
                style="border:1px solid #000;">
                {{ number_format($item->volume,0,',','.') }}
            </td>

            <td align="center"
                valign="top"
                style="border:1px solid #000;">
                {{ $item->satuan }}
            </td>

            <td align="center"
                valign="top"
                style="border:1px solid #000;">
                {{ $item->pdn ? 'Ya' : 'Tidak' }}
            </td>

            <td align="center"
                valign="top"
                style="border:1px solid #000;">
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
<tr>
    <td colspan="3" height="15"></td>
</tr>

<tr>
    <td width="4%" valign="top">10.</td>
            <td width="25%" valign="top">
        {{ $isBarang
            ? 'Jangka Waktu Penyerahan/Pengiriman Barang'
            : 'Jangka Waktu Pekerjaan'
        }}
    </td>

    <td width="3%" align="center" valign="top">
        :
    </td>

    <td width="72%" valign="top" class="isi-narasi">

        @if($jangkaWaktuNilai)

            @if($isBarang)

                Jangka waktu penyerahan/pengiriman barang adalah
                {{ $jangkaWaktuNilai }}
                {{ strtolower($jangkaWaktuSatuan) }}
                kalender terhitung sejak tanggal penandatanganan kontrak.

            @else

                Jangka waktu pelaksanaan pekerjaan adalah
                {{ $jangkaWaktuNilai }}
                {{ strtolower($jangkaWaktuSatuan) }}
                kalender terhitung sejak tanggal penandatanganan kontrak.

            @endif

        @else

            -

        @endif

    </td>
</tr>

<tr>
    <td colspan="3" height="15"></td>
</tr>

    <tr>
    <td width="3%" valign="top">11.</td>

    <td width="25%" valign="top">
        Spesifikasi Pelayanan
    </td>

    <td width="3%" align="center" valign="top">
        :
    </td>

    <td width="72%" valign="top">

        <table width="100%" cellpadding="2">

            <tr>
                <td width="15" valign="top">a.</td>

                <td width="100" valign="top">
                    Garansi Barang
                </td>

                <td width="15" valign="top">:</td>

                <td valign="top">
                    @if($procurementPackage->garansi_nilai)
                        {{ $procurementPackage->garansi_nilai }}
                        {{ $procurementPackage->garansi_satuan }}
                    @else
                        -
                    @endif
                </td>
            </tr>

            <tr>
                <td valign="top">b.</td>

                <td valign="top">
                    Layanan Purna Jual
                </td>

                <td valign="top">:</td>

                <td valign="top">
                    {{ $procurementPackage->layanan_purna_jual ? 'Ada' : 'Tidak Ada' }}
                </td>
            </tr>

        </table>

    </td>
</tr>

<tr>
    <td colspan="4" height="15"></td>
</tr>
</table>

<table>
    
<tr>
    <td width="3%" valign="top">12.</td>

    <td width="25%" valign="top">
        DPA
    </td>

    <td width="3%" align="center" valign="top">
        :
    </td>

    <td width="69%" valign="top">
        DPA/A.1/1.04.2.11.2.10.04.0000/001/{{ $procurementPackage->package->fiscalYear?->tahun }}
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">Kode MAK</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        @foreach($technicalSpecification->items as $item)
            {{ $item->kode_mak }}<br>
        @endforeach
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">Kode RUP</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        {{ $procurementPackage->package->id_rup }}
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">NPWP Instansi</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        {{ $procurementPackage->npwp_instansi }}
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">Alamat Email PPK</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        {{ $procurementPackage->email_ppk }}
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">No. Telp</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        {{ $procurementPackage->no_telp_ppk }}
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">Tahun Anggaran</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        {{ $procurementPackage->package->fiscalYear?->tahun }}
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">Waktu Awal</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        Jangka waktu penyerahan/pengiriman barang adalah
        {{ $jangkaWaktuNilai }}
        {{ strtolower($jangkaWaktuSatuan) }}
        kalender terhitung sejak tanggal penandatanganan kontrak.
    </td>
</tr>

<tr>
    <td></td>
    <td valign="top">Catatan</td>
    <td align="center" valign="top">:</td>
    <td valign="top">
        Barang diterima paling lambat tanggal
        {{ $procurementPackage->tanggal_barang_diterima
            ? \Carbon\Carbon::parse(
                $procurementPackage->tanggal_barang_diterima
            )->translatedFormat('d F Y')
            : '-'
        }}
    </td>
</tr>

<tr>
    <td colspan="4" height="15"></td>
</tr>

</table>

<table>
    <tr>
    <td width="3%" valign="top">13.</td>

    <td width="25%" valign="top">
        Jenis Kontrak
    </td>

    <td width="3%" align="center" valign="top">
        :
    </td>

    <td width="72%" valign="top">
        {{ $procurementPackage->jenis_kontrak ?? '-' }}
    </td>
</tr>

<tr>
    <td colspan="4" height="15"></td>
</tr>

<tr>
    <td width="3%" valign="top">14.</td>

    <td width="25%" valign="top">
        Referensi Harga
    </td>

    <td width="3%" align="center" valign="top">
        :
    </td>

    <td width="72%" valign="top">
        Terlampir.
    </td>
</tr>

<tr>
    <td colspan="4" height="20"></td>
</tr>
<tr>
    <td colspan="4" style="text-align:justify;">
        Demikian Spesifikasi Teknis ini dibuat sebagai acuan dalam
        pelaksanaan paket pekerjaan
        {{ $procurementPackage->package->nama_paket }}.
    </td>
</tr>

<tr>
    <td colspan="4" height="25"></td>
</tr>
<tr>
    <td colspan="4">

        <table width="100%">
            <tr>

                <td width="40%">
                    &nbsp;
                </td>

                <td width="60%" align="center" valign="top">

                    Bengkayang,
                    {{ now()->translatedFormat('d F Y') }}

                    <br>

                    Pejabat Pembuat Komitmen
                    <br>
                    Dinas Perumahan Rakyat dan Kawasan Permukiman,
                    Pertanahan dan Lingkungan Hidup
                    <br>
                    Kabupaten Bengkayang

                    <br><br><br><br>

                    <strong>
                        <u>
                            {{ $procurementPackage->nama_ppk ?? '-' }}
                        </u>
                    </strong>

                    <br>

                    {{ $procurementPackage->pangkat_gol_ppk ?? '-' }}

                    <br>

                    NIP. {{ $procurementPackage->nip_ppk ?? '-' }}

                </td>

            </tr>
        </table>

    </td>
</tr>
</table>

    </div>
</div>

</body>
</html>