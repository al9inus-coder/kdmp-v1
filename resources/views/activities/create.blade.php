@extends('adminlte::page')

@section('title', 'Tambah Kegiatan')

@section('content_header')
    <h1>Tambah Kegiatan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('activities.store') }}" method="POST">
                @php($submitLabel = 'Simpan')
                @include('activities._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
