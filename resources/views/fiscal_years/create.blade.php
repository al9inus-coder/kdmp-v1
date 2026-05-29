@extends('adminlte::page')

@section('title', 'Tambah Tahun Anggaran')

@section('content_header')
    <h1>Tambah Tahun Anggaran</h1>
@stop

@section('content')

<div class="card">

    <div class="card-body">

        <form action="{{ route('fiscal-years.store') }}"
              method="POST">

            @csrf

            <div class="form-group">
                <label>Tahun</label>

                <input type="number"
                       name="tahun"
                       class="form-control"
                       placeholder="2027">
            </div>

            <div class="form-check mt-3">
                <input type="checkbox"
                       name="is_active"
                       class="form-check-input">

                <label class="form-check-label">
                    Jadikan Tahun Aktif
                </label>
            </div>

            <button class="btn btn-success mt-3">
                Simpan
            </button>

        </form>

    </div>

</div>

@stop