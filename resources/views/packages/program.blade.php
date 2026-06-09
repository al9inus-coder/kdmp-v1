@extends('adminlte::page')

@section('title', 'Paket Program')

@section('content_header')
    <h1>
        Paket Pekerjaan - {{ $program->kode }} {{ $program->nama ? '- '.$program->nama : '' }}
        <small class="text-muted">
            ({{ $packages->total() }} Data)
        </small>
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="{{ route('packages.program', $program) }}" method="GET" class="form-inline">
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
                    <input type="text"
                           name="q"
                           class="form-control ml-2"
                           placeholder="Cari ID RUP / Nama Paket"
                           value="{{ $search }}">
                    <select name="status" class="form-control ml-2">
                        <option value="">Semua Status</option>
                        <option value="needs_review" @selected($status === 'needs_review')>Needs Review</option>
                        <option value="draft" @selected($status === 'draft')>Draft</option>
                        <option value="approved" @selected($status === 'approved')>Approved</option>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit">Filter</button>
                        <a href="{{ route('packages.program', $program) }}" class="btn btn-outline-secondary">Reset</a>
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
                            <th>Kegiatan</th>
                            <th>Sub Kegiatan</th>
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
                                <td>{{ $package->activity?->kode }} {{ $package->activity ? '- '.$package->activity->nama : '' }}</td>
                                <td>{{ $package->subActivity?->kode }} {{ $package->subActivity ? '- '.$package->subActivity->nama : '' }}</td>
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
                                    <a href="{{ route('packages.show', $package) }}" class="btn btn-sm btn-info">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
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
