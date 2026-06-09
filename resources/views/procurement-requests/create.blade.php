@extends('adminlte::page')

@section('title', 'Buat Surat Permohonan')

@section('content_header')
    <h1>Buat Surat Permohonan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('procurement-packages.procurement-request.store', $procurementPackage->package) }}"
                  method="POST">
                @php($submitLabel = 'Simpan')
                @include('procurement-requests._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
