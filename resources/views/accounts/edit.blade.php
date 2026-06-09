@extends('adminlte::page')

@section('title', 'Edit Rekening Belanja')

@section('content_header')
    <h1>Edit Rekening Belanja</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('accounts.update', $account) }}" method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('accounts._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>
@stop
