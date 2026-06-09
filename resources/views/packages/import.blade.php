@extends('adminlte::page')

@section('title', 'Import Paket')

@section('content_header')
    <h1>Import Paket RUP</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upload File Excel RUP</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('packages.import.store') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="fiscal_year_id">Tahun Anggaran</label>
                    <select id="fiscal_year_id"
                            name="fiscal_year_id"
                            class="form-control @error('fiscal_year_id') is-invalid @enderror"
                            required>
                        <option value="">Pilih Tahun Anggaran</option>
                        @foreach($fiscalYears as $fiscalYear)
                            <option value="{{ $fiscalYear->id }}"
                                @selected((string) old('fiscal_year_id', $activeFiscalYearId) === (string) $fiscalYear->id)>
                                {{ $fiscalYear->tahun }}
                                @if($fiscalYear->is_active) (Aktif) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('fiscal_year_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="file">File XLSX</label>
                    <input type="file"
                           id="file"
                           name="file"
                           class="form-control @error('file') is-invalid @enderror"
                           accept=".xlsx"
                           required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Kolom wajib: ID RUP, Nama Paket, Kode Sub Kegiatan, Kode Rekening, Pagu,
                        Jenis Pengadaan, Metode Pengadaan, Pemilihan Mulai, Pemilihan Selesai,
                        Kontrak Mulai, Kontrak Selesai.
                    </small>
                </div>

                <button type="submit" class="btn btn-primary">
                    Proses Import
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Riwayat Import Batch</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>File</th>
                            <th>Tahun Anggaran</th>
                            <th>Total</th>
                            <th>Berhasil</th>
                            <th>Gagal</th>
                            <th>Status</th>
                            <th>Imported At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                            <tr onclick="window.location='{{ route('packages.import.show', $batch->id) }}'" class="table-row-clickable">
                                <td>{{ $batch->id }}</td>
                                <td>{{ $batch->file_name }}</td>
                                <td>{{ $batch->fiscalYear->tahun ?? '-' }}</td>
                                <td>{{ $batch->total_rows }}</td>
                                <td>{{ $batch->success_rows }}</td>
                                <td>{{ $batch->failed_rows }}</td>
                                <td>
                                    @if($batch->status === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($batch->status === 'completed_with_errors')
                                        <span class="badge badge-warning">Completed With Errors</span>
                                    @elseif($batch->status === 'failed')
                                        <span class="badge badge-danger">Failed</span>
                                    @elseif($batch->status === 'processing')
                                        <span class="badge badge-info">Processing</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $batch->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $batch->imported_at?->format('d-m-Y H:i:s') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    Belum ada riwayat import.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($batches->hasPages())
            <div class="card-footer clearfix">
                {{ $batches->links() }}
            </div>
        @endif
    </div>
    <style>
.table-row-clickable:hover {
    cursor: pointer;
    background-color: #f4f6f9;
}
</style>
@stop
