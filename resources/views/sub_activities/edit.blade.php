@component('layouts.kdmp')
@section('title', 'Edit Sub Kegiatan')

<x-ui.toast />

<x-ui.workspace title="Edit Sub Kegiatan" description="Perbarui data sub kegiatan {{ $subActivity->kode }}.">
    <x-slot:actions>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-mono">
            <i data-lucide="hash" class="w-3.5 h-3.5"></i>
            {{ $subActivity->kode }}
        </span>
        <x-ui.button variant="outline" size="md" href="{{ route('admin.sub-activities.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <form action="{{ route('admin.sub-activities.update', $subActivity) }}" method="POST">
        @csrf
        @method('PUT')
        @include('sub_activities._form', ['submitLabel' => 'Perbarui'])
    </form>
</x-ui.workspace>
@endcomponent
