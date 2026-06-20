@extends('adminlte::page')

@section('title', 'Detail Paket Pekerjaan')

@section('content_header')
    <h1>Detail Paket Pekerjaan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID RUP</th>
                        <td>{{ $package->id_rup ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nama Paket</th>
                        <td>{{ $package->nama_paket }}</td>
                    </tr>
                    <tr>
                        <th>Tahun Anggaran</th>
                        <td>{{ $package->fiscalYear->tahun ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Program</th>
                        <td>{{ $package->program?->kode }} {{ $package->program ? '- '.$package->program->nama : '' }}</td>
                    </tr>
                    <tr>
                        <th>Kegiatan</th>
                        <td>{{ $package->activity?->kode }} {{ $package->activity ? '- '.$package->activity->nama : '' }}</td>
                    </tr>
                    <tr>
                        <th>Sub Kegiatan</th>
                        <td>{{ $package->subActivity?->kode }} {{ $package->subActivity ? '- '.$package->subActivity->nama : '' }}</td>
                    </tr>
                    <tr>
                        <th>Rekening</th>
                        <td>{{ $package->account?->kode }} {{ $package->account ? '- '.$package->account->nama : '' }}</td>
                    </tr>
                    <tr>
                        <th>Pagu</th>
                        <td>Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Jenis Pengadaan</th>
                        <td>{{ $package->jenis_pengadaan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Metode Pengadaan</th>
                        <td>{{ $package->metode_pengadaan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Pemilihan Mulai/Selesai</th>
                        <td>
    {{ bulanIndonesia($package->pemilihan_mulai_bulan) }}
    /
    {{ bulanIndonesia($package->pemilihan_selesai_bulan) }}
</td>
                    </tr>
                    <tr>
                        <th>Kontrak Mulai/Selesai</th>
<td>
    {{ bulanIndonesia($package->kontrak_mulai_bulan) }}
    /
    {{ bulanIndonesia($package->kontrak_selesai_bulan) }}
</td>                    </tr>
                    <tr>
    <th>Diajukan</th>
    <td>
        {{ $package->submitted_at?->format('d-m-Y H:i') ?? '-' }}
    </td>
</tr>

<tr>
    <th>Disetujui</th>
    <td>
        {{ $package->approved_at?->format('d-m-Y H:i') ?? '-' }}
    </td>
</tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            @if($package->status === 'needs_review')
                                <span class="badge badge-danger">Needs Review</span>

                            @elseif($package->status === 'draft')
                                <span class="badge badge-warning">Draft</span>

                            @elseif($package->status === 'submitted')
                                <span class="badge badge-info">Submitted</span>

                            @elseif($package->status === 'approved')
                                <span class="badge badge-success">Approved</span>

                            @else
                                <span class="badge badge-secondary">
                                    {{ $package->status }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

<hr>

<div class="d-flex justify-content-between align-items-center mt-3">

    {{-- KIRI --}}
    <div>

        @if($package->status === 'draft')
            @can('submit', $package)
            <form action="{{ route('packages.submit', $package) }}"
                  method="POST"
                  style="display:inline;">
                @csrf
                <button type="submit"
                        class="btn btn-primary">
                    Ajukan
                </button>
            </form>
            @endcan
        @endif

        @if($package->status === 'submitted')
            @can('approve', $package)
            <form action="{{ route('packages.approve', $package) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success">
                    Setujui
                </button>
            </form>
            @endcan

            @can('returnToDraft', $package)
            <form action="{{ route('packages.return', $package) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger">
                    Kembalikan ke Draft
                </button>
            </form>
            @endcan
        @endif

        @if($package->status === 'approved' && !$package->procurementPackage)
            <form action="{{ route('packages.procurement-packages.store', $package) }}"
                  method="POST"
                  style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success">
                    Buat Paket Pengadaan
                </button>
            </form>
        @endif

        @if($package->procurementPackage)
            <a href="{{ route('procurement-packages.show', $package) }}"
               class="btn btn-success">
                Masuk Paket Pengadaan
            </a>
        @endif

    </div>

    {{-- KANAN --}}
    <div>

        @if(in_array($package->status, ['needs_review', 'draft']))
            @can('update', $package)
            <a href="{{ route('packages.edit', $package) }}"
               class="btn btn-warning">
                Edit / Lengkapi
            </a>
            @endcan
        @endif

        @can('delete', $package)
        <form action="{{ route('packages.destroy', $package) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                Hapus
            </button>
        </form>
        @endcan

        <a href="{{ route('packages.index') }}"
           class="btn btn-default">
            Kembali
        </a>

    </div>

</div>


        </div>
    </div>
@stop
