@extends('adminlte::page')

@section('title', 'Tambah Paket Pekerjaan')

@section('content_header')
    <h1>Tambah Paket Pekerjaan</h1>
@stop

@section('content')

<div class="card">

    <form action="{{ route('packages.store') }}"
          method="POST">

        @csrf

        @include('packages._form', [
            'submitLabel' => 'Simpan'
        ])

    </form>

</div>

@stop