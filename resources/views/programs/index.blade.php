@extends('adminlte::page')

@section('title', 'Program')

@section('content_header')
    <h1>Master Program</h1>
@stop

@section('content')

<div class="card">
    <div class="card-header">
        <a href="#" class="btn btn-primary">
            Tambah Program
        </a>
    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Program</th>
                    <th>Tahun</th>
                    <th>SKPD</th>
                    <th>Pagu</th>
                </tr>
            </thead>

            <tbody>

            @foreach($programs as $program)

                <tr>
                    <td>{{ $program->kode }}</td>
                    <td>{{ $program->nama }}</td>
                    <td>{{ $program->tahun }}</td>
                    <td>{{ $program->skpd->nama }}</td>
                    <td>
                        Rp {{ number_format($program->pagu,0,',','.') }}
                    </td>
                </tr>

            @endforeach

            </tbody>

        </table>

    </div>
</div>

@stop