@component('layouts.kdmp')
@section('title', 'Tambah Rekening Belanja')

<x-ui.toast />

<x-ui.workspace title="Tambah Rekening Belanja" description="Tambahkan rekening belanja baru.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.accounts.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.accounts.store') }}" method="POST">
        @csrf
        @include('accounts._form', ['submitLabel' => 'Simpan'])
    </form>
</x-ui.workspace>
@endcomponent
