@extends('adminlte::page')

@section('title', 'Persiapan Pengadaan')

@section('content_header')
    <h1>Persiapan Pengadaan</h1>
@stop

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            {{ $package->nama_paket }}
        </h3>
    </div>

    <form method="POST"
          action="{{ route('packages.procurement.update', $package) }}">
        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">
                    <div class="form-group">
                        <label>ID RUP</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $package->id_rup }}"
                               readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Status Pengadaan</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $package->procurement_status }}"
                               readonly>
                    </div>
                </div>

            </div>

            <div class="form-group">
                <label>PPTK</label>
                <input type="text"
                       name="pptk_name"
                       class="form-control"
                       value="{{ old('pptk_name', $package->pptk_name) }}">
            </div>

            <div class="form-group">
                <label>PPK</label>
                <input type="text"
                       name="ppk_name"
                       class="form-control"
                       value="{{ old('ppk_name', $package->ppk_name) }}">
            </div>

            <div class="form-group">
                <label>Target Pengadaan</label>
                <input type="date"
                       name="target_procurement_date"
                       class="form-control"
                       value="{{ old(
                            'target_procurement_date',
                            optional($package->target_procurement_date)->format('Y-m-d')
                       ) }}">
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="procurement_notes"
                          class="form-control"
                          rows="4">{{ old('procurement_notes', $package->procurement_notes) }}</textarea>
            </div>

        </div>

        <div class="card-footer">
            <button type="submit"
                    class="btn btn-primary">
                Simpan
            </button>
        </div>

    </form>
</div>

@stop