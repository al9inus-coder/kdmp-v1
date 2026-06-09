@extends('adminlte::page')

@section('title', 'Edit Spesifikasi Teknis')

@section('content_header')
    <h1>Edit Spesifikasi Teknis</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('procurement-packages.technical-specification.update', $procurementPackage) }}"
                  method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('technical-specifications._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
