@extends('adminlte::page')

@section('title', 'Edit Sub Kegiatan')

@section('content_header')
    <h1>Edit Sub Kegiatan</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('sub-activities.update', $subActivity) }}" method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('sub_activities._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
