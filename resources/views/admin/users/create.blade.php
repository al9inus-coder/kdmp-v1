@component('layouts.kdmp')
@section('title', 'Tambah User')

<x-ui.toast />

<x-ui.workspace title="Tambah User Baru" description="Buat akun pengguna baru beserta role dan status aksesnya.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.users.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        @include('admin.users._form', ['submitLabel' => 'Simpan User'])
    </form>
</x-ui.workspace>
@endcomponent
