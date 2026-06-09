@extends('adminlte::page')

@section('title', 'Edit Referensi Harga')

@section('content_header')
    <h1>Edit Referensi Harga</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('procurement-packages.price-references.update', [$procurementPackage->package, $priceReference]) }}"
                  method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('price-references._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
