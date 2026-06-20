@extends('adminlte::page')

@section('title', 'Paket Pekerjaan')

@section('content_header')
    <h1>
        Paket Pekerjaan
        <small class="text-muted">
            ({{ $packages->total() }} Data)
        </small>
    </h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header pt-4 pb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex">
                    @can('create', App\Models\Package::class)
                        <a href="{{ route('packages.create') }}" class="btn btn-primary" style="margin-right: 10px;">
                            <i class="fas fa-plus"></i> Tambah Paket
                        </a>
                    @endcan
                    @can('create', App\Models\ImportBatch::class)
                        <a href="{{ route('packages.import.index') }}" class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Import Paket
                        </a>
                    @endcan
                </div>
            </div>

            <form action="{{ route('packages.index') }}" method="GET" class="d-flex flex-wrap align-items-center bg-light p-3 rounded border">
                <select name="fiscal_year_id" class="form-select form-control" style="width: auto; min-width: 180px; margin-right: 10px; margin-bottom: 10px;">
                    <option value="">Semua Tahun Anggaran</option>
                    @foreach($fiscalYears as $fiscalYear)
                        <option value="{{ $fiscalYear->id }}" @selected((string) $fiscalYearId === (string) $fiscalYear->id)>
                            {{ $fiscalYear->tahun }}
                        </option>
                    @endforeach
                </select>

                <select name="program_id" class="form-select form-control" style="width: auto; max-width: 200px; margin-right: 10px; margin-bottom: 10px;">
                    <option value="">Semua Program</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" @selected((string) $programId === (string) $program->id)>
                            {{ \Illuminate\Support\Str::limit($program->nama, 30) }}
                        </option>
                    @endforeach
                </select>

                <select name="activity_id" class="form-select form-control" style="width: auto; max-width: 200px; margin-right: 10px; margin-bottom: 10px;">
                    <option value="">Semua Kegiatan</option>
                    @foreach($activities as $activity)
                        <option value="{{ $activity->id }}" @selected((string) $activityId === (string) $activity->id)>
                            {{ \Illuminate\Support\Str::limit($activity->nama, 30) }}
                        </option>
                    @endforeach
                </select>

                <select name="sub_activity_id" class="form-select form-control" style="width: auto; max-width: 200px; margin-right: 10px; margin-bottom: 10px;">
                    <option value="">Semua Sub Kegiatan</option>
                    @foreach($subActivities as $subActivity)
                        <option value="{{ $subActivity->id }}" @selected((string) $subActivityId === (string) $subActivity->id)>
                            {{ \Illuminate\Support\Str::limit($subActivity->nama, 30) }}
                        </option>
                    @endforeach
                </select>

                <input type="text" name="q" class="form-control" style="width: 250px; margin-right: 10px; margin-bottom: 10px;" placeholder="Cari ID RUP / Nama Paket" value="{{ $search }}">

                <select name="status" class="form-select form-control" style="width: auto; margin-right: 10px; margin-bottom: 10px;">
                    <option value="">Semua Status</option>
                    <option value="needs_review" @selected($status === 'needs_review')>Needs Review</option>
                    <option value="draft" @selected($status === 'draft')>Draft</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                </select>

                <div class="d-flex ml-auto" style="margin-bottom: 10px;">
                    <button class="btn btn-primary" type="submit" style="margin-right: 5px;">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary">
                        <i class="fas fa-sync-alt"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 10%;">ID RUP</th>
                            <th>Nama Paket</th>
                            <th class="text-end text-nowrap">Pagu</th>
                            <th class="text-nowrap">Jenis Pengadaan</th>
                            <th class="text-nowrap">Metode</th>
                            <th class="text-center text-nowrap">Status</th>
                            <th style="width: 90px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td class="text-center">{{ $package->id_rup ?? '-' }}</td>
                                <td class="fw-bold text-wrap">{{ $package->nama_paket }}</td>
                                <td class="text-end text-nowrap fw-bold text-success">
                                    Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}
                                </td>
                                <td class="text-nowrap">{{ $package->jenis_pengadaan ?? '-' }}</td>
                                <td class="text-nowrap">{{ $package->metode_pengadaan ?? '-' }}</td>
                                <td class="text-center">
                                    @if($package->status === 'needs_review')
                                        <span class="badge bg-danger">Needs Review</span>
                                    @elseif($package->status === 'draft')
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @elseif($package->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $package->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <a href="{{ route('packages.show', $package) }}"
                                           class="btn btn-sm btn-info text-white" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('delete', $package)
                                        <form action="{{ route('packages.destroy', $package) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    Data paket pekerjaan belum tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($packages->hasPages())
            <div class="card-footer d-flex justify-content-center pt-4 pb-3">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
@stop
