@component('layouts.kdmp')
@section('title', 'Detail Import Batch')

<x-ui.toast />

<x-ui.workspace title="Detail Import Batch #{{ $batch->id }}" description="Ringkasan hasil impor dan daftar error.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('packages.import.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    {{-- Informasi Import --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6 max-w-3xl">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="file-spreadsheet" class="w-4 h-4"></i></div>
            <h3 class="text-sm font-bold text-slate-900">Informasi Import</h3>
        </div>
        <dl class="divide-y divide-slate-100">
            <div class="px-6 py-3 flex gap-4"><dt class="w-48 text-sm font-semibold text-slate-500 shrink-0">File</dt><dd class="text-sm text-slate-800">{{ $batch->file_name }}</dd></div>
            <div class="px-6 py-3 flex gap-4"><dt class="w-48 text-sm font-semibold text-slate-500 shrink-0">Tahun Anggaran</dt><dd class="text-sm text-slate-800">{{ $batch->fiscalYear->tahun ?? '-' }}</dd></div>
            <div class="px-6 py-3 flex gap-4"><dt class="w-48 text-sm font-semibold text-slate-500 shrink-0">Total Data</dt><dd class="text-sm font-semibold text-slate-800">{{ $batch->total_rows }}</dd></div>
            <div class="px-6 py-3 flex gap-4"><dt class="w-48 text-sm font-semibold text-slate-500 shrink-0">Berhasil</dt><dd class="text-sm font-semibold text-emerald-600">{{ $batch->success_rows }}</dd></div>
            <div class="px-6 py-3 flex gap-4"><dt class="w-48 text-sm font-semibold text-slate-500 shrink-0">Gagal</dt><dd class="text-sm font-semibold text-rose-600">{{ $batch->failed_rows }}</dd></div>
            <div class="px-6 py-3 flex gap-4"><dt class="w-48 text-sm font-semibold text-slate-500 shrink-0">Status</dt><dd><x-ui.badge variant="info">{{ $batch->status }}</x-ui.badge></dd></div>
        </dl>
    </section>

    @if($batch->errors->count())
        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-rose-50/50 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center"><i data-lucide="alert-triangle" class="w-4 h-4"></i></div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Error ({{ $batch->errors->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Baris</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-40">ID RUP</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-48">Jenis Error</th>
                            <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Pesan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($batch->errors as $error)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-3 text-slate-600">{{ $error->row_number }}</td>
                                <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $error->id_rup }}</td>
                                <td class="px-6 py-3"><x-ui.badge variant="danger">{{ $error->error_type }}</x-ui.badge></td>
                                <td class="px-6 py-3 text-slate-700">{{ $error->error_message }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-ui.workspace>
@endcomponent
