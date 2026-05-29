@extends('adminlte::page')

@section('title', 'Master SKPD')

@section('content_header')
    <h1>Master SKPD</h1>
@stop

@section('content')

<a href="{{ route('skpds.create') }}"
   class="btn btn-primary mb-3">
    Tambah SKPD
</a>

<div class="card">
    <div class="card-body">

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama SKPD</th>
                    <th>Kepala SKPD</th>
                </tr>
            </thead>

            <tbody>

                @forelse($skpds as $skpd)

                <tr>
                    <td>{{ $skpd->id }}</td>
                    <td>{{ $skpd->kode }}</td>
                    <td>{{ $skpd->nama }}</td>
                    <td>{{ $skpd->kepala_skpd }}</td>
                </tr>

                @empty

                <tr>
                    <td colspan="4" class="text-center">
                        Belum ada data
                    </td>
                </tr>

                @endforelse

            </tbody>

        </table>

    </div>
</div>

@stop