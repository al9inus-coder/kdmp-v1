@component('layouts.kdmp')
@section('title', 'Tambah Referensi Harga')

<x-ui.toast />

<x-ui.workspace title="Tambah Referensi Harga" description="{{ $procurementPackage->package->nama_paket ?? '' }}">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('procurement-packages.price-references.index', $procurementPackage->package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('procurement-packages.price-references.store', $procurementPackage->package) }}" method="POST">
        @include('price-references._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
