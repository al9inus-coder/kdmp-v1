@component('layouts.kdmp')
@section('title', 'Tambah Program')

<x-ui.toast />

<x-ui.workspace title="Tambah Program" description="Tambahkan program anggaran baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.programs.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.programs.store') }}" method="POST">
        @csrf
        @include('programs._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
