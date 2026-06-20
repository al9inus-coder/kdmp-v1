@extends('adminlte::page')

@section('title', 'Master SKPD')

@section('content_header')
    <h1>Master SKPD</h1>
@stop

@section('content')

<a href="{{ route('skpds.create') }}" class="btn btn-primary mb-3">
    <i class="fas fa-plus mr-1"></i> Tambah SKPD
</a>

<div class="card card-outline card-primary">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="25%">Informasi SKPD</th>
                        <th width="20%">PA / Kepala Dinas</th>
                        <th width="20%">PPK</th>
                        <th width="20%">PPTK</th>
                        <th width="10%" class="text-center"><i class="fas fa-cogs"></i> Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($skpds as $index => $skpd)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong class="text-primary">{{ $skpd->kode }}</strong><br>
                            {{ $skpd->nama }}
                            @if($skpd->singkatan)
                                <br><span class="badge bg-info mt-1">{{ $skpd->singkatan }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="mb-1"><i class="fas fa-user text-muted mr-1"></i> {{ $skpd->kepala_skpd ?? '-' }}</div>
                            <div><i class="fas fa-id-card text-muted mr-1"></i> <small>{{ $skpd->nip_kepala ?? '-' }}</small></div>
                        </td>
                        <td>
                            <div class="mb-1"><i class="fas fa-user text-muted mr-1"></i> {{ $skpd->nama_ppk ?? '-' }}</div>
                            <div><i class="fas fa-id-card text-muted mr-1"></i> <small>{{ $skpd->nip_ppk ?? '-' }}</small></div>
                        </td>
                        <td>
                            <div class="mb-1"><i class="fas fa-user text-muted mr-1"></i> {{ $skpd->nama_pptk ?? '-' }}</div>
                            <div><i class="fas fa-id-card text-muted mr-1"></i> <small>{{ $skpd->nip_pptk ?? '-' }}</small></div>
                        </td>
                        <td class="text-center">
                            <form action="{{ route('skpds.destroy', $skpd) }}" method="POST" onsubmit="return confirm('Peringatan: Menghapus SKPD juga akan menghapus data Program, Kegiatan, Sub Kegiatan, dan Paket di bawahnya! Yakin ingin melanjutkan?');">
                                @csrf
                                @method('DELETE')
                                <div class="btn-group">
                                    <a href="{{ route('skpds.edit', $skpd) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-folder-open mb-2 fa-2x"></i><br>
                            Belum ada data SKPD
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@stop