@component('layouts.kdmp')
@section('title', 'Tambah SBU Uang Harian')

<x-ui.toast />

<x-ui.workspace title="Tambah Uang Harian" description="Tambahkan standar uang harian luar daerah baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('sbu-uang-harians.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('sbu-uang-harians.store') }}" method="POST">
        @csrf
        @include('sbu-uang-harians._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
