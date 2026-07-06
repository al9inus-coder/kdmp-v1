@component('layouts.kdmp')
@section('title', 'Edit Paket Pekerjaan')

<x-ui.toast />

<x-ui.workspace title="Edit / Lengkapi Paket Pekerjaan" description="{{ $package->nama_paket }}">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('packages.show', $package) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('packages.update', $package) }}" method="POST">
        @method('PUT')
        @include('packages._form', ['submitLabel' => 'Perbarui'])
    </form>
</x-ui.workspace>
@endcomponent
