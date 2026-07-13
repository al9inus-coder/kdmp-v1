@component('layouts.kdmp')
@section('title', 'Tambah Pegawai')

<x-ui.toast />

<x-ui.workspace title="Tambah Pegawai" description="Tambahkan data pegawai baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.employees.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.employees.store') }}" method="POST">
        @csrf
        @include('employees._form', ['submitLabel' => 'Simpan Pegawai'])
    </form>
</x-ui.workspace>
@endcomponent
