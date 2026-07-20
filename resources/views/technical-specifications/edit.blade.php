@component('layouts.kdmp')

@section('title', 'Edit Spesifikasi Teknis')

@slot('header')
    <h1>Edit Spesifikasi Teknis</h1>
@endslot


    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6">
            <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.technical-specification.update', $procurementPackage) }}"
                  method="POST">
                @method('PUT')
                @php($submitLabel = 'Perbarui')
                @include('technical-specifications._form', ['submitLabel' => $submitLabel])
            </form>
        </div>
    </div>

@endcomponent
