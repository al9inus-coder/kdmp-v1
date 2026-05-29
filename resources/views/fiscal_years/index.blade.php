@extends('adminlte::page')

@section('title', 'Tahun Anggaran')

@section('content_header')
    <h1>Tahun Anggaran</h1>
@stop

@section('content')

<a href="{{ route('fiscal-years.create') }}"
   class="btn btn-primary mb-3">
    Tambah Tahun
</a>

<div class="card">
    <div class="card-body">

        <table class="table table-bordered">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tahun</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

            @forelse($years as $year)

                <tr>
                    <td>{{ $year->id }}</td>
                    <td>{{ $year->tahun }}</td>
                    <td>
                        @if($year->is_active)
                            <span class="badge bg-success">
                                Aktif
                            </span>
                        @else
                            <span class="badge bg-secondary">
                                Non Aktif
                            </span>
                        @endif
                    </td>
                    <td>
                        @if(!$year->is_active)
                            <form action="{{ route('fiscal-years.activate',$year->id) }}"
                                method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm">
                                    Aktifkan
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="3" class="text-center">
                        Belum ada data
                    </td>
                </tr>
             @endforelse

            </tbody>

        </table>

    </div>
</div>

@stop