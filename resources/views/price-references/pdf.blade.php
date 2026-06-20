<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Referensi Harga - {{ $procurementPackage->package->nama_paket }}</title>
@php
    $skpd = \App\Models\Skpd::first();
@endphp
<style>
@page {
    size: A4 landscape;
    margin: 15mm 10mm;
}

body {
    background: #f4f6f9;
    font-family: Arial, sans-serif;
    font-size: 12pt;
    line-height: 1.4;
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
    width: 297mm;
    min-height: 210mm;
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

    table { page-break-inside: auto; }
    .tabel-referensi tbody { page-break-inside: avoid; }
    .tabel-referensi tr { page-break-inside: avoid; page-break-after: auto; }
    .tabel-referensi thead { display: table-header-group; }
}

.judul-dokumen{
    font-weight:bold;
    font-size:12pt;
    text-transform:uppercase;
    margin-bottom:20px;
}

table.tabel-referensi {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

table.tabel-referensi th, table.tabel-referensi td {
    border: 1px solid black;
    padding: 4px 6px;
    vertical-align: top;
    text-align: left;
}

table.tabel-referensi th {
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
}

table.tabel-referensi td.center {
    text-align: center;
}

table.tabel-referensi td.right {
    text-align: right;
}

.signature-table {
    width: 100%;
    border-collapse: collapse;
}

.signature-table td {
    vertical-align: top;
    border: none;
}

.signature-container {
    text-align: center;
}
</style>

</head>
<body>

<button class="btn-print no-print" onclick="window.print()">Cetak</button>

<div class="document-viewer">
    <div class="document-paper">

<div class="judul-dokumen">
    REFERENSI HARGA<br>
    {{ strtoupper($procurementPackage->package->nama_paket ?? '') }}
</div>

<table class="tabel-referensi">
    <thead>
        <tr>
            <th width="3%">No</th>
            <th width="15%">Nama<br>Barang/Jasa</th>
            <th width="15%">Nama<br>Barang/Jasa<br>di Etalase</th>
            <th width="5%">Vol</th>
            <th width="6%">Satuan</th>
            <th width="15%">Nama<br>Pelaku<br>Usaha</th>
            <th width="12%">Harga<br>Satuan<br>Tayang</th>
            <th width="12%">Jumlah<br>Harga</th>
            <th width="17%">Link Produk</th>
        </tr>
    </thead>
        @php
            $priceReferences = $procurementPackage->priceReferences ?? collect();
            $groupedReferences = $priceReferences->groupBy('nama_barang_jasa');
            $no = 1;
        @endphp

        @forelse($technicalItems as $techItem)
            @php
                $namaBarang = $techItem->nama_barang_jasa;
                $references = $groupedReferences->get($namaBarang, collect());
            @endphp
            
            <tbody>
                @if($references->isNotEmpty())
                    @php $groupCount = $references->count(); @endphp
                    @foreach($references as $refIndex => $item)
                        <tr>
                            <td class="center" style="{{ $refIndex > 0 ? 'border-top: none;' : '' }} {{ $refIndex < ($groupCount - 1) ? 'border-bottom: none;' : '' }}">
                                {{ $refIndex === 0 ? $no++ : '' }}
                            </td>
                            <td style="{{ $refIndex > 0 ? 'border-top: none;' : '' }} {{ $refIndex < ($groupCount - 1) ? 'border-bottom: none;' : '' }}">
                                {{ $refIndex === 0 ? $namaBarang : '' }}
                            </td>
                            <td class="center">{{ $item->nama_produk_etalase ?? '-' }}</td>
                            <td class="center">{{ number_format($item->volume, 2, ',', '.') }}</td>
                            <td class="center">{{ $item->satuan }}</td>
                            <td class="center">{{ $item->nama_pelaku_usaha ?? '-' }}</td>
                            <td class="right">{{ $item->harga_satuan ? 'Rp' . number_format((float) $item->harga_satuan, 0, ',', '.') : 'Rp 0' }}</td>
                            <td class="right">{{ $item->jumlah_harga ? 'Rp' . number_format((float) $item->jumlah_harga, 0, ',', '.') : 'Rp 0' }}</td>
                            <td style="word-break: break-all;">
                                @if($item->link_produk)
                                    <a href="{{ $item->link_produk }}" style="color: black; text-decoration: none;">{{ $item->link_produk }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td class="center">{{ $no++ }}</td>
                        <td>{{ $namaBarang }}</td>
                        <td class="center">-</td>
                        <td class="center">{{ number_format((float) $techItem->volume, 2, ',', '.') }}</td>
                        <td class="center">{{ $techItem->satuan }}</td>
                        <td class="center">-</td>
                        <td class="right">Rp 0</td>
                        <td class="right">Rp 0</td>
                        <td class="center">-</td>
                    </tr>
                @endif
            </tbody>
        @empty
            <tbody>
                <tr>
                    <td colspan="9" class="center">Belum ada data spesifikasi teknis dan referensi harga.</td>
                </tr>
            </tbody>
        @endforelse
</table>

<table class="signature-table">
    <tr>
        <td width="60%">
            &nbsp;
        </td>
        <td width="40%" class="signature-container">
            Bengkayang, {{ $procurementPackage->technicalSpecification?->tanggal ? \Carbon\Carbon::parse($procurementPackage->technicalSpecification->tanggal)->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
            <br>
            Pejabat Pembuat Komitmen
            <br>
            {{ $skpd->nama }}
            <br>
            Kabupaten Bengkayang
            <br><br><br><br>
            <strong><u>{{ $procurementPackage->nama_ppk ?? '.........................................' }}</u></strong>
            <br>
            {{ $procurementPackage->pangkat_gol_ppk ?? '.........................................' }}
            <br>
            NIP. {{ $procurementPackage->nip_ppk ?? '.........................................' }}
        </td>
    </tr>
</table>

    </div>
</div>

</body>
</html>
