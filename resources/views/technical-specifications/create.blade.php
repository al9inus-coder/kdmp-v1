@extends('adminlte::page')

@section('title', 'Buat Spesifikasi Teknis')

@section('content_header')
    <h1>Buat Spesifikasi Teknis</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('procurement-packages.technical-specification.store', $procurementPackage) }}"
                  method="POST">
                @php($submitLabel = 'Simpan')
                @include('technical-specifications._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
