@component('layouts.kdmp')
@section('title', 'Tambah Kegiatan')

<x-ui.toast />

<x-ui.workspace title="Tambah Kegiatan" description="Tambahkan kegiatan anggaran baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.activities.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.activities.store') }}" method="POST">
        @csrf
        @include('activities._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
