@extends('adminlte::page')

@section('title', 'Detail Import Batch')

@section('content_header')
    <h1>Detail Import Batch #{{ $batch->id }}</h1>
@stop

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Informasi Import</h3>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th width="250">File</th>
                <td>{{ $batch->file_name }}</td>
            </tr>
            <tr>
                <th>Tahun Anggaran</th>
                <td>{{ $batch->fiscalYear->tahun ?? '-' }}</td>
            </tr>
            <tr>
                <th>Total Data</th>
                <td>{{ $batch->total_rows }}</td>
            </tr>
            <tr>
                <th>Berhasil</th>
                <td>{{ $batch->success_rows }}</td>
            </tr>
            <tr>
                <th>Gagal</th>
                <td>{{ $batch->failed_rows }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $batch->status }}</td>
            </tr>
        </table>
    </div>
</div>

@if($batch->errors->count())

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Error</h3>
    </div>

    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th width="100">Baris</th>
                    <th width="150">ID RUP</th>
                    <th width="200">Jenis Error</th>
                    <th>Pesan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($batch->errors as $error)
                    <tr>
                        <td>{{ $error->row_number }}</td>
                        <td>{{ $error->id_rup }}</td>
                        <td>{{ $error->error_type }}</td>
                        <td>{{ $error->error_message }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endif

<a href="{{ route('packages.import.index') }}"
   class="btn btn-secondary">
    Kembali
</a>

@stop