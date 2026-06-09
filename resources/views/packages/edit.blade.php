@extends('adminlte::page')

@section('title', 'Edit Paket Pekerjaan')

@section('content_header')
    <h1>Edit / Lengkapi Paket Pekerjaan</h1>
@stop

@section('content')
<div class="card">
    <form action="{{ route('packages.update', $package) }}"
          method="POST">

        @csrf
        @method('PUT')

        @include('packages._form', [
            'submitLabel' => 'Perbarui'
        ])

    </form>
</div>
@stop