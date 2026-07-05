@component('layouts.kdmp')
@section('title', 'Tambah Sub Kegiatan')

<x-ui.toast />

<x-ui.workspace title="Tambah Sub Kegiatan" description="Tambahkan sub kegiatan anggaran baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('sub-activities.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('sub-activities.store') }}" method="POST">
        @csrf
        @include('sub_activities._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
