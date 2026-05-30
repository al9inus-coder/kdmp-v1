@extends('adminlte::page')

@section('title', 'Tambah Sub Kegiatan')

@section('content_header')
    <h1>Tambah Sub Kegiatan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sub-activities.store') }}" method="POST">
                @php($submitLabel = 'Simpan')
                @include('sub_activities._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
