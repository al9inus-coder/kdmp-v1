@extends('adminlte::page')

@section('title', 'Tambah SKPD')

@section('content_header')
    <h1>Tambah SKPD</h1>
@stop

@section('content')

<div class="card">

    <div class="card-body">

        <form action="{{ route('skpds.store') }}"
              method="POST">

            @csrf

            <div class="form-group">
                <label>Kode SKPD</label>
                <input type="text"
                       name="kode"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>Nama SKPD</label>
                <input type="text"
                       name="nama"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>Kepala SKPD</label>
                <input type="text"
                       name="kepala_skpd"
                       class="form-control">
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat"
                          class="form-control"></textarea>
            </div>

            <button class="btn btn-success">
                Simpan
            </button>

        </form>

    </div>

</div>

@stop