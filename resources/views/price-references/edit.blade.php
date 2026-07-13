@component('layouts.kdmp')
@section('title', 'Edit Referensi Harga')

<x-ui.toast />

<x-ui.workspace title="Edit Referensi Harga" description="{{ $procurementPackage->package->nama_paket ?? '' }}">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.price-references.index', $procurementPackage->package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route((auth()->user()->hasRole(['Admin', 'Super Admin']) ? 'admin.' : 'kabid.') . 'procurement-packages.price-references.update', [$procurementPackage->package, $priceReference]) }}" method="POST">
        @method('PUT')
        @include('price-references._form', ['submitLabel' => 'Perbarui'])
    </form>
</x-ui.workspace>
@endcomponent
