@component('layouts.kdmp')
@section('title', 'Edit User')

<x-ui.toast />

<x-ui.workspace title="Edit User" description="Perbarui informasi akun, role, dan status. Password diubah melalui menu Reset Password.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.users.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['submitLabel' => 'Perbarui User'])
    </form>
</x-ui.workspace>
@endcomponent
