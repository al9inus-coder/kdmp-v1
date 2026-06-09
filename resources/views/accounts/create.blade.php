@extends('adminlte::page')

@section('title', 'Tambah Rekening Belanja')

@section('content_header')
    <h1>Tambah Rekening Belanja</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('accounts.store') }}" method="POST">
                @php($submitLabel = 'Simpan')
                @include('accounts._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
