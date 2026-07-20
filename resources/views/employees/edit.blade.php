@component('layouts.kdmp')
@section('title', 'Edit Pegawai')

<x-ui.toast />

<x-ui.workspace title="Edit Pegawai" description="Perbarui data pegawai.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.employees.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.employees.update', $employee) }}" method="POST">
        @csrf
        @method('PUT')
        @include('employees._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
</x-ui.workspace>
@endcomponent
