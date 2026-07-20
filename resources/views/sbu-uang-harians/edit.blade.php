@component('layouts.kdmp')
@section('title', 'Edit SBU Uang Harian')

<x-ui.toast />

<x-ui.workspace title="Edit Uang Harian" description="Perbarui standar uang harian luar daerah.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.sbu-uang-harians.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.sbu-uang-harians.update', $sbuUangHarian) }}" method="POST">
        @csrf
        @method('PUT')
        @include('sbu-uang-harians._form', ['submitLabel' => 'Update', 'rate' => $sbuUangHarian])
    </form>
</x-ui.workspace>
@endcomponent
