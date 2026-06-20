<div class="row">
    <div class="col-2 text-center">
        <img src="{{ asset('images/logo-bengkayang.png') }}" style="width:70px;">
    </div>
    <div class="col-10 text-center">
        <div class="kop-pemerintah">PEMERINTAH KABUPATEN BENGKAYANG</div>
        <div class="kop-dinas">DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP</div>
        <div class="kop-alamat">Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat</div>
        <div class="kop-alamat">Situs : bengkayangkab.go.id</div>
    </div>
</div>
<hr style="border-top:3px solid #000; border-bottom:1px solid #000; margin-top:5px; margin-bottom: 2px; padding-bottom: 2px;">

<div class="row mt-2">
    <div class="col-12 text-right">
        Bengkayang, {{ $procurementRequest->tanggal_surat?->translatedFormat('d F Y') }}
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
                    Permohonan Pemesanan Barang/Jasa melalui {{ $procurementPackage->package->metode_pengadaan }}
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="mb-2">
    Yth.<br>
    Sdr.
    <strong>
        {{ $procurementRequest->nama_pejabat_pengadaan }}
    </strong>
    <br>
    Pejabat Pengadaan {{ $skpd->singkatan ?? $skpd->nama }}
    <br>
    di -
    <div class="ml-4">
        TEMPAT
    </div>
</div>

<div style="text-align:justify">
    Dengan hormat,
    <br>
    Dalam rangka memenuhi kebutuhan barang dan jasa pada di lingkungan {{ $skpd->nama }}, maka dengan ini diminta kepada Saudara untuk dapat melaksanakan 
    proses pengadaan barang/jasa melalui metode {{ $procurementPackage->package->metode_pengadaan }} dengan data paket sebagai berikut :
</div>

@php
    $items = $procurementPackage->technicalSpecification?->items ?? collect();
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
            $hargaSatuanDpa = (float) $item->harga_satuan_dpa;
            $jumlah = $hargaSatuanDpa * (float) $item->volume;
            $grandTotal += $jumlah;
        @endphp
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $item->nama_barang_jasa }}</td>
            <td class="text-center">{{ $item->satuan }}</td>
            <td class="text-center">
                {{ number_format((float) $item->volume, 0, ',', '.') }}
            </td>
            <td class="text-right">
                Rp {{ number_format($hargaSatuanDpa, 0, ',', '.') }}
            </td>
            <td class="text-right">
                Rp {{ number_format($jumlah, 0, ',', '.') }}
            </td>
        </tr>
    @endforeach
    <tr>
        <td colspan="5" class="text-center font-weight-bold">Jumlah Total</td>
        <td class="text-right font-weight-bold">
            Rp {{ number_format($grandTotal, 0, ',', '.') }}
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
                <td class="label-col">Nama Paket</td>
                <td class="colon-col">:</td>
                <td>{{ $procurementPackage->package->nama_paket }}</td>
            </tr>
            <tr>
                <td class="label-col">Lokasi Pelaksanaan</td>
                <td class="colon-col">:</td>
                <td>Kabupaten Bengkayang</td>
            </tr>
            <tr>
                <td class="label-col">Sumber Dana</td>
                <td class="colon-col">:</td>
                <td>APBD Tahun Anggaran {{ $procurementPackage->package->fiscalYear?->tahun }}</td>
            </tr>
            <tr>
                <td class="label-col">Kode Rekening</td>
                <td class="colon-col">:</td>
                <td>{{ $procurementPackage->package->account?->kode ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Pagu Anggaran</td>
                <td class="colon-col">:</td>
                <td>Rp {{ number_format((float) $procurementPackage->package->pagu, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label-col">Tahun Anggaran</td>
                <td class="colon-col">:</td>
                <td>Tahun Anggaran {{ $procurementPackage->package->fiscalYear?->tahun }}</td>
            </tr>
            <tr>
                <td class="label-col">User Akun PPK</td>
                <td class="colon-col">:</td>
                <td>{{ $procurementPackage->user_ppk ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Email PPK</td>
                <td class="colon-col">:</td>
                <td>{{ $procurementPackage->email_ppk }}</td>
            </tr>
            <tr>
                <td class="label-col">Kode RUP</td>
                <td class="colon-col">:</td>
                <td>{{ $procurementPackage->package->id_rup }}</td>
            </tr>
            <tr>
                <td class="label-col">Rekomendasi Penyedia</td>
                <td class="colon-col">:</td>
                <td>{{ $procurementRequest->nama_penyedia ?: '-' }}</td>
            </tr>
            <tr>
                <td class="label-col">Alasan Pemilihan Penyedia</td>
                <td class="colon-col">:</td>
                <td style="text-align: justify;">{{ $procurementRequest->alasan_pemilihan_penyedia ?: '-' }}</td>
            </tr>
        </table>
    </div>
</div>

<div class="mt-2 text-justify">Demikian Surat Permohonan ini disampaikan, atas perhatian dan kerjasamanya diucapkan terima kasih.</div>
<div class="row ttd-area">
    <div class="col-5"></div>
    <div class="col-7 text-center">
            Pejabat Pembuat Komitmen<br>
            {{ $skpd->nama }}<br>
            Kabupaten Bengkayang
            <br><br><br><br>
            <strong><u>{{ $procurementPackage->nama_ppk ?? '-' }}</u></strong>
            <br>
            {{ $procurementPackage->pangkat_gol_ppk ?? '-' }}<br>
            NIP. {{ $procurementPackage->nip_ppk ?? '-' }}
        </div>
</div>
