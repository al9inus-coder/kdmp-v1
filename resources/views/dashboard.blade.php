@extends('adminlte::page')

@section('title', 'Dashboard KDMP')

@section('content_header')
    <h1>Dashboard KDMP</h1>
@stop

@section('content')

<div class="row">

    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>Total Pengguna</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>
                    {{ auth()->user()->roles->first()?->name }}
                </h3>
                <p>Role Saya</p>
            </div>
        </div>
    </div>

</div>

@stop