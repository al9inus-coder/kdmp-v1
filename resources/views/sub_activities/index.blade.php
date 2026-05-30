@extends('adminlte::page')

@section('title', 'Master Sub Kegiatan')

@section('content_header')
    <h1>
        Master Sub Kegiatan
        <small class="text-muted">
            ({{ $subActivities->total() }} Data)
        </small>
    </h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <a href="{{ route('sub-activities.create') }}" class="btn btn-primary">
                Tambah Sub Kegiatan
            </a>

            <form action="{{ route('sub-activities.index') }}" method="GET" class="form-inline">
                <div class="input-group">
                    <input type="text"
                           name="q"
                           class="form-control"
                           placeholder="Cari kode / nama sub kegiatan"
                           value="{{ $search }}">
                    <select name="status" class="form-control ml-2">
                        <option value="">Semua Status</option>
                        <option value="1" @selected($status === '1')>Aktif</option>
                        <option value="0" @selected($status === '0')>Nonaktif</option>
                    </select>
                    <select name="activity_id" class="form-control ml-2">
                        <option value="">Semua Kegiatan</option>
                        @foreach($activities as $activity)
                            <option value="{{ $activity->id }}" @selected((string) $activityId === (string) $activity->id)>
                                {{ $activity->kode }} - {{ $activity->nama }}
                            </option>
                        @endforeach
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit">
                            Cari
                        </button>
                        <a href="{{ route('sub-activities.index') }}" class="btn btn-outline-secondary">
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
                            <th style="width: 70px;">No</th>
                            <th>Kode</th>
                            <th>Nama Sub Kegiatan</th>
                            <th>Kegiatan</th>
                            <th style="width: 140px;">Status</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subActivities as $subActivity)
                            <tr>
                                <td>{{ $subActivities->firstItem() + $loop->index }}</td>
                                <td>{{ $subActivity->kode }}</td>
                                <td>{{ $subActivity->nama }}</td>
                                <td>{{ $subActivity->activity->kode }} - {{ $subActivity->activity->nama }}</td>
                                <td>
                                    @if($subActivity->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('sub-activities.edit', $subActivity) }}"
                                       class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div>
                                        <strong>Belum ada data Sub Kegiatan.</strong>
                                    </div>
                                    <div class="text-muted">
                                        Klik tombol Tambah Sub Kegiatan untuk membuat data pertama.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($subActivities->hasPages())
            <div class="card-footer clearfix">
                {{ $subActivities->links() }}
            </div>
        @endif
    </div>
@stop
