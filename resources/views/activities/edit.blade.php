@extends('adminlte::page')

@section('title', 'Edit Kegiatan')

@section('content_header')
    <h1>Edit Kegiatan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('activities.update', $activity) }}" method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('activities._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
