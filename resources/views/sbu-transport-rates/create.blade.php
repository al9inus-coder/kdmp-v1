@component('layouts.kdmp')
@section('title', 'Tambah Biaya Transportasi')

<x-ui.toast />

<x-ui.workspace title="Tambah Biaya Transportasi" description="Tambahkan standar biaya transportasi baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('sbu-transport-rates.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('sbu-transport-rates.store') }}" method="POST">
        @csrf
        @include('sbu-transport-rates._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
