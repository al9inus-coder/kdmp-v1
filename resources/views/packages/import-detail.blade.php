@component('layouts.kdmp')
@section('title', 'Detail Import Batch')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                Detail Import <span class="text-emerald-600">#{{ $batch->id }}</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Rincian hasil proses import file Excel RUP.</p>
        </div>
        <a href="{{ route('packages.import.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    {{-- Ringkasan Stat --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase">Total Data</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $batch->total_rows }}</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase">Berhasil</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $batch->success_rows }}</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase">Gagal</p>
            <p class="text-2xl font-bold {{ $batch->failed_rows > 0 ? 'text-rose-600' : 'text-slate-400' }} mt-1">{{ $batch->failed_rows }}</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl p-5">
            <p class="text-xs font-semibold text-slate-500 uppercase">Status</p>
            <div class="mt-2">
                @if($batch->status === 'completed')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i data-lucide="check-circle" class="w-3 h-3"></i> Selesai
                    </span>
                @elseif($batch->status === 'completed_with_errors')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i> Ada Error
                    </span>
                @elseif($batch->status === 'failed')
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        <i data-lucide="x-circle" class="w-3 h-3"></i> Gagal
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        <i data-lucide="loader" class="w-3 h-3"></i> Proses
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Informasi Import --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
            <div class="p-2 bg-emerald-50 rounded-xl">
                <i data-lucide="file-spreadsheet" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <h3 class="font-bold text-slate-800">Informasi Import</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-slate-500">File</dt>
                    <dd class="font-semibold text-slate-800 mt-0.5">{{ $batch->file_name }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Tahun Anggaran</dt>
                    <dd class="font-semibold text-slate-800 mt-0.5">{{ $batch->fiscalYear->tahun ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Diunggah oleh</dt>
                    <dd class="font-semibold text-slate-800 mt-0.5">{{ $batch->creator->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Waktu Import</dt>
                    <dd class="font-semibold text-slate-800 mt-0.5">{{ $batch->imported_at?->format('d-m-Y H:i') ?? '-' }}</dd>
                </div>
                @if($batch->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Catatan</dt>
                        <dd class="font-semibold text-rose-600 mt-0.5">{{ $batch->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    </div>

    {{-- Daftar Error --}}
    @if($batch->errors->count())
        <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-3">
                <div class="p-2 bg-rose-50 rounded-xl">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600"></i>
                </div>
                <h3 class="font-bold text-slate-800">Daftar Error ({{ $batch->errors->count() }} baris)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600">
                    <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Baris</th>
                            <th class="px-5 py-3 font-semibold">ID RUP</th>
                            <th class="px-5 py-3 font-semibold">Jenis Error</th>
                            <th class="px-5 py-3 font-semibold">Pesan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($batch->errors as $error)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $error->row_number }}</td>
                                <td class="px-5 py-3">{{ $error->id_rup ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $error->error_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">{{ $error->error_message }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endcomponent
