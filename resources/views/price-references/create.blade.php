@extends('adminlte::page')

@section('title', 'Tambah Referensi Harga')

@section('content_header')
    <h1>Tambah Referensi Harga</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('procurement-packages.price-references.store', $procurementPackage->package) }}"
                  method="POST">
                @php($submitLabel = 'Simpan')
                @include('price-references._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
