@component('layouts.kdmp')
@section('title', 'Tambah SKPD')

<x-ui.toast />

<x-ui.workspace title="Tambah SKPD Baru" description="Lengkapi informasi perangkat daerah dan pejabat terkait.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('skpds.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('skpds.store') }}" method="POST">
        @csrf
        @include('skpds._form', ['submitLabel' => 'Simpan Data SKPD'])
    </form>
</x-ui.workspace>
@endcomponent
