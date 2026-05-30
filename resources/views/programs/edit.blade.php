@extends('adminlte::page')

@section('title', 'Edit Program')

@section('content_header')
    <h1>Edit Program</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('programs.update', $program) }}" method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('programs._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
