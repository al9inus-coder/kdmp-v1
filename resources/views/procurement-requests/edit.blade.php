@extends('adminlte::page')

@section('title', 'Edit Surat Permohonan')

@section('content_header')
    <h1>Edit Surat Permohonan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('procurement-packages.procurement-request.update', $procurementPackage->package) }}"
                  method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('procurement-requests._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
