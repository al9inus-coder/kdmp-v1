@extends('adminlte::page')

@section('title', 'Referensi Harga')

@section('content_header')
    {{-- Memanggil komponen progress bar yang sudah dibuat sebelumnya --}}
    @include('components.procurement-progress', [
        'procurementPackage' => $procurementPackage
    ])
@stop

@section('content')

    {{-- ALERT PESAN --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
        </div>
    @endif

    {{-- INFORMASI PAKET UTAMA (SLIM RIBBON) --}}
    <div class="bg-white rounded border shadow-sm mb-4 px-4 py-3" style="border-left: 4px solid #007bff !important;">
        <div class="row align-items-center text-center text-md-left">
            <div class="col-md-5 mb-2 mb-md-0">
                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Nama Paket</small>
                <h6 class="font-weight-bold text-dark mb-0">{{ $procurementPackage->package->nama_paket }}</h6>
            </div>
            <div class="col-md-3 border-left border-right mb-2 mb-md-0 px-md-3">
                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">ID RUP</small>
                <h6 class="font-weight-bold text-dark mb-0">{{ $procurementPackage->package->id_rup ?? '-' }}</h6>
            </div>
            <div class="col-md-4 px-md-4">
                <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Pagu Paket</small>
                <h6 class="font-weight-bold text-primary mb-0" style="font-size: 1.1rem;">
                    Rp {{ number_format((float) $procurementPackage->package->pagu, 0, ',', '.') }}
                </h6>
            </div>
        </div>
    </div>

    {{-- TOMBOL TAMBAH --}}
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <h4 class="m-0 text-dark font-weight-bold">Daftar Referensi Harga</h4>
        <a href="{{ route('procurement-packages.price-references.create', $procurementPackage->package) }}"
           class="btn btn-primary rounded-pill shadow-sm px-4">
            <i class="fas fa-plus mr-1"></i> Tambah Referensi
        </a>
    </div>

    {{-- LIST REFERENSI HARGA (LOOPING) --}}
    @forelse($technicalItems as $item)
        @php
            $namaBarang =
                $item->nama_barang_jasa;
            $references =
                $groupedReferences[$namaBarang]
                ?? collect();
            $first =
                $references->first();
            $rataRataHargaSatuan =
                $references->avg('harga_satuan') ?? 0;
            $rataRataJumlahHarga =
                $references->avg('jumlah_harga') ?? 0;
            $hargaTerendah =
                $references->min('harga_satuan') ?? 0;
        @endphp
        <div class="card card-outline card-info shadow-sm mb-4">
            
            {{-- HEADER CARD BARANG (RAPAT KE KIRI) --}}
            <div class="card-header bg-white d-flex flex-wrap align-items-center py-3">
                <h5 class="font-weight-bold text-info mb-2 mb-md-0 mr-4">
                    <i class="fas fa-cube mr-2"></i>
                    {{ $item->nama_barang_jasa }}
                    <span class="badge badge-info ml-2">
                        {{ $references->count() }}/3 Referensi
                    </span>
                </h5>
                <div class="d-flex flex-wrap">
                    <span class="badge badge-light border px-3 py-2 mr-2 text-sm text-dark font-weight-normal elevation-1">
                        <span class="text-secondary mr-1">
                            Volume:
                        </span>
                        <strong>
                            {{ number_format((float) $item->volume,0,',','.') }}
                            {{ $item->satuan }}
                        </strong>
                    </span>
                    <span class="badge badge-light border px-3 py-2 text-sm text-dark font-weight-normal elevation-1">
                        <span class="text-secondary mr-1">
                            Harga Satuan DPA:
                        </span>
                        <strong>
                            Rp {{ number_format(
                                $item->harga_satuan_dpa ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </strong>
                    </span>
                </div>
            </div>

            {{-- TABEL REFERENSI --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center border-top-0" width="5%">No</th>
                                <th class="border-top-0">Produk Etalase</th>
                                <th class="border-top-0">Pelaku Usaha</th>
                                <th class="text-right border-top-0">Harga Satuan</th>
                                <th class="text-right border-top-0">Jumlah Harga</th>
                                <th class="text-center border-top-0">Link Produk</th>
                                <th class="text-center border-top-0" width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($references->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="alert alert-warning mb-0">
                                            <i class="fas fa-exclamation-circle mr-2"></i>
                                            Belum ada referensi harga untuk barang ini.
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($references as $index => $priceReference)
                                <tr>
                                    <td class="text-center align-middle">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $priceReference->nama_produk_etalase ?? '-' }}
                                    </td>
                                    <td class="align-middle">
                                        {{ $priceReference->nama_pelaku_usaha ?? '-' }}
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-dark">
                                        Rp {{ number_format(
                                            (float) $priceReference->harga_satuan,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-primary">
                                        Rp {{ number_format(
                                            (float) $priceReference->jumlah_harga,
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($priceReference->link_produk)
                                            <a href="{{ $priceReference->link_produk }}"
                                            target="_blank"
                                            class="btn btn-xs btn-outline-info rounded-pill px-3">
                                                <i class="fas fa-external-link-alt mr-1"></i>
                                                Buka Link
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group">
                                            <a href="{{ route('procurement-packages.price-references.edit', [$procurementPackage->package, $priceReference]) }}"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('procurement-packages.price-references.destroy', [$procurementPackage->package, $priceReference]) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus referensi harga ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER: RATA-RATA & HARGA TERENDAH (3 KOLOM PROPORSIONAL) --}}
            <div class="card-footer bg-white pt-3 pb-3 border-top">
                <div class="row">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="callout callout-info border shadow-none mb-0 bg-light py-2">
                            <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Rata-rata Satuan</small>
                            <h5 class="text-info font-weight-bold mb-0">Rp {{ number_format((float) $rataRataHargaSatuan,0,',','.') }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="callout callout-success border shadow-none mb-0 bg-light py-2">
                            <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Rata-rata Jumlah</small>
                            <h5 class="text-success font-weight-bold mb-0">Rp {{ number_format((float) $rataRataJumlahHarga,0,',','.') }}</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="callout callout-warning border shadow-none mb-0 bg-light py-2">
                            <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Harga Terendah</small>
                            <h5 class="text-warning font-weight-bold mb-0">Rp {{ number_format((float) $hargaTerendah,0,',','.') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info shadow-sm">
            <i class="fas fa-info-circle mr-2"></i> Belum ada referensi harga yang ditambahkan untuk paket ini.
        </div>
    @endforelse

{{-- NAVIGASI BAWAH: KEMBALI (KIRI) & LANJUT SURAT PERMOHONAN (KANAN) --}}
<div class="d-flex justify-content-between align-items-center mt-4 mb-5">
    <a href="{{ route('procurement-packages.index') }}"
       class="btn btn-default btn-lg shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i>
        Kembali
    </a>
    
    <div class="d-flex align-items-center">
        <button type="button" class="btn btn-secondary btn-lg shadow-sm px-4 mr-2" onclick="printPdf('{{ route('procurement-packages.price-references.print', $procurementPackage->package) }}')">
            <i class="fas fa-print mr-1"></i>
            Cetak Referensi Harga
        </button>
        @if($procurementPackage->procurementRequest)
            <a href="{{ route(
                'procurement-packages.procurement-request.show',
                $procurementPackage->package
            ) }}"
               class="btn btn-info btn-lg shadow-sm px-4">
                <i class="fas fa-file-alt mr-1"></i>
                Lihat Surat Permohonan
            </a>
        @else
            <a href="{{ route(
                'procurement-packages.procurement-request.create',
                $procurementPackage->package
            ) }}"
               class="btn btn-success btn-lg shadow-sm px-4">
                Lanjut Surat Permohonan
                <i class="fas fa-arrow-right ml-1"></i>
            </a>
        @endif
    </div>
</div>

@stop

@push('js')
<script>
function printPdf(url) {
    let iframe = document.getElementById('print-iframe');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'print-iframe';
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
    }
    iframe.src = url;
    iframe.onload = function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    };
}
</script>
@endpush