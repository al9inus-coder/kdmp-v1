@component('layouts.kdmp')

@section('title', 'Buat Spesifikasi Teknis')

@slot('header')
    <h1>Buat Spesifikasi Teknis</h1>
@endslot


    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6">
            <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.technical-specification.store', $procurementPackage) }}"
                  method="POST">
                @php($submitLabel = 'Simpan')
                @include('technical-specifications._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>

@endcomponent
