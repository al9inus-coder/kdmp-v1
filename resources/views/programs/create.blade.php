@extends('adminlte::page')

@section('title', 'Tambah Program')

@section('content_header')
    <h1>Tambah Program</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('programs.store') }}" method="POST">
                @php($submitLabel = 'Simpan')
                @include('programs._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
