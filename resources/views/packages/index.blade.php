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
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2">
                <a href="{{ route('packages.create') }}" class="btn btn-primary">
                    Tambah Paket
                </a>
                <a href="{{ route('packages.import.index') }}" class="btn btn-success">
                    Import Paket
                </a>
            </div>

            <form action="{{ route('packages.index') }}" method="GET" class="form-inline">
                <div class="input-group">
                    <select name="fiscal_year_id" class="form-control">
                        <option value="">Semua Tahun Anggaran</option>
                        @foreach($fiscalYears as $fiscalYear)
                            <option value="{{ $fiscalYear->id }}"
                                @selected((string) $fiscalYearId === (string) $fiscalYear->id)>
                                {{ $fiscalYear->tahun }}
                            </option>
                        @endforeach
                    </select>
                    <select name="program_id" class="form-control ml-2">
                        <option value="">Semua Program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected((string) $programId === (string) $program->id)>
                                {{ $program->kode }} - {{ $program->nama }}
                            </option>
                        @endforeach
                    </select>
                    <input type="text"
                           name="q"
                           class="form-control ml-2"
                           placeholder="Cari ID RUP / Nama Paket"
                           value="{{ $search }}">
                    <select name="status" class="form-control ml-2">
                        <option value="">Semua Status</option>
                        <option value="needs_review" @selected($status === 'needs_review')>
                            Needs Review
                        </option>
                        <option value="draft" @selected($status === 'draft')>
                            Draft
                        </option>
                        <option value="approved" @selected($status === 'approved')>
                            Approved
                        </option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit">
                            Filter
                        </button>
                        <a href="{{ route('packages.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID RUP</th>
                            <th>Nama Paket</th>
                            <th>Program</th>
                            <th style="width: 180px;">Pagu</th>
                            <th>Jenis Pengadaan</th>
                            <th>Metode</th>
                            <th style="width: 150px;">Status</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td>{{ $package->id_rup ?? '-' }}</td>
                                <td>{{ $package->nama_paket }}</td>
                                <td>
                                    @if($package->program)
                                        {{ $package->program->kode }} - {{ $package->program->nama }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>Rp {{ number_format((float) $package->pagu, 0, ',', '.') }}</td>
                                <td>{{ $package->jenis_pengadaan ?? '-' }}</td>
                                <td>{{ $package->metode_pengadaan ?? '-' }}</td>
                                <td>
                                    @if($package->status === 'needs_review')
                                        <span class="badge badge-danger">Needs Review</span>
                                    @elseif($package->status === 'draft')
                                        <span class="badge badge-warning">Draft</span>
                                    @elseif($package->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $package->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('packages.show', $package) }}"
                                       class="btn btn-sm btn-info">
                                        Detail
                                    </a>
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
            <div class="card-footer clearfix">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
@stop
