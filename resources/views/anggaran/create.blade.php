@component('layouts.kdmp')
@section('title', 'Tambah Baris Anggaran')

<x-ui.workspace title="Tambah Baris Anggaran" description="Catat plafon DPA untuk satu rekening belanja dalam sub kegiatan.">
    <div class="max-w-3xl">
        <form action="{{ route('admin.anggaran.store') }}" method="POST">
            @csrf
            @include('anggaran._form', ['submitLabel' => 'Simpan Baris'])
        </form>
    </div>
</x-ui.workspace>
@endcomponent
