@component('layouts.kdmp')
@section('title', 'Edit Biaya Transportasi')

<x-ui.toast />

<x-ui.workspace title="Edit Biaya Transportasi" description="Perbarui standar biaya transportasi.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('sbu-transport-rates.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('sbu-transport-rates.update', $sbuTransportRate) }}" method="POST">
        @csrf
        @method('PUT')
        @include('sbu-transport-rates._form', ['submitLabel' => 'Update', 'rate' => $sbuTransportRate])
    </form>
</x-ui.workspace>
@endcomponent
