@component('layouts.kdmp')
@section('title', 'Buat Surat Permohonan')

<x-ui.toast />

<x-ui.workspace title="Buat Surat Permohonan" description="{{ $procurementPackage->package->nama_paket ?? '' }}">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('procurement-packages.show', $procurementPackage->package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('procurement-packages.procurement-request.store', $procurementPackage->package) }}" method="POST">
        @include('procurement-requests._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
