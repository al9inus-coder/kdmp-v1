@component('layouts.kdmp')
@section('title', 'Edit SKPD')

<x-ui.toast />

<x-ui.workspace title="Edit SKPD" description="Perbarui informasi perangkat daerah {{ $skpd->kode }}.">
    <x-slot:actions>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg">
            <i data-lucide="hash" class="w-3.5 h-3.5"></i>
            {{ $skpd->kode }}
        </span>
        <x-ui.button variant="outline" size="md" href="{{ route('skpds.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('skpds.update', $skpd) }}" method="POST">
        @csrf
        @method('PUT')
        @include('skpds._form', ['skpd' => $skpd, 'submitLabel' => 'Update Data SKPD'])
    </form>
</x-ui.workspace>
@endcomponent
