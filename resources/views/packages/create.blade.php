@component('layouts.kdmp')
@section('title', 'Tambah Paket Pekerjaan')

<x-ui.toast />

<x-ui.workspace title="Tambah Paket Pekerjaan" description="Buat paket pekerjaan / RUP baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('packages.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('packages.store') }}" method="POST">
        @include('packages._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
