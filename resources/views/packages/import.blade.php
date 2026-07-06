@component('layouts.kdmp')
@section('title', 'Import Paket')

<x-ui.toast />

<x-ui.workspace title="Import Paket RUP" description="Unggah file Excel Rencana Umum Pengadaan (RUP).">
    <x-slot:actions>
        <x-ui.button variant="outline" size="md" href="{{ route('packages.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    @if($errors->any())
        <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 rounded-xl">
            <div class="p-1.5 rounded-full bg-rose-100 shrink-0"><i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i></div>
            <div>
                <p class="text-sm font-bold text-rose-800">Terjadi kesalahan</p>
                <ul class="mt-1 text-xs text-rose-600 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Upload --}}
    <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6 max-w-2xl">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i data-lucide="upload" class="w-4 h-4"></i></div>
                <h3 class="text-sm font-bold text-slate-900">Upload File Excel RUP</h3>
            </div>
            <a href="{{ asset('template/template_import_rup.xlsx') }}" download
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download Template
            </a>
        </div>
        <form action="{{ route('packages.import.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="p-6 space-y-5">
                <div>
                    <label for="fiscal_year_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Anggaran <span class="text-rose-500">*</span></label>
                    <x-ui.select name="fiscal_year_id" id="fiscal_year_id" :invalid="$errors->has('fiscal_year_id')" required>
                        <option value="">Pilih Tahun Anggaran</option>
                        @foreach($fiscalYears as $fiscalYear)
                            <option value="{{ $fiscalYear->id }}" @selected((string) old('fiscal_year_id', $activeFiscalYearId) === (string) $fiscalYear->id)>
                                {{ $fiscalYear->tahun }} @if($fiscalYear->is_active) (Aktif) @endif
                            </option>
                        @endforeach
                    </x-ui.select>
                    @error('fiscal_year_id') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="file" class="block text-sm font-semibold text-slate-700 mb-1.5">File XLSX <span class="text-rose-500">*</span></label>
                    <input type="file" id="file" name="file" accept=".xlsx" required
                        class="block w-full text-sm text-slate-600 border border-slate-200 rounded-md bg-white file:mr-4 file:py-2.5 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer @error('file') border-rose-300 @enderror">
                    @error('file') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-2 text-xs text-slate-500">Kolom wajib: ID RUP, Nama Paket, Kode Sub Kegiatan, Kode Rekening, Pagu, Jenis Pengadaan, Metode Pengadaan, Pemilihan Mulai, Pemilihan Selesai, Kontrak Mulai, Kontrak Selesai.</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/60 flex justify-end">
                <x-ui.button variant="primary" size="md" type="submit"><i data-lucide="play" class="w-4 h-4 mr-2"></i> Proses Import</x-ui.button>
            </div>
        </form>
    </section>

    {{-- Riwayat --}}
    <x-ui.card padding="none">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2"><i data-lucide="history" class="w-4 h-4 text-slate-400"></i> Riwayat Import Batch</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider w-16">ID</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">File</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Tahun</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Total</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Berhasil</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Gagal</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Imported At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        <tr onclick="window.location='{{ route('packages.import.show', $batch->id) }}'" class="hover:bg-slate-50/80 transition-colors cursor-pointer">
                            <td class="px-5 py-3.5 text-slate-500 font-medium">{{ $batch->id }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $batch->file_name }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $batch->fiscalYear->tahun ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-center text-slate-600">{{ $batch->total_rows }}</td>
                            <td class="px-5 py-3.5 text-center font-semibold text-emerald-600">{{ $batch->success_rows }}</td>
                            <td class="px-5 py-3.5 text-center font-semibold text-rose-600">{{ $batch->failed_rows }}</td>
                            <td class="px-5 py-3.5 text-center">
                                @if($batch->status === 'completed')
                                    <x-ui.badge variant="success">Completed</x-ui.badge>
                                @elseif($batch->status === 'completed_with_errors')
                                    <x-ui.badge variant="warning">With Errors</x-ui.badge>
                                @elseif($batch->status === 'failed')
                                    <x-ui.badge variant="danger">Failed</x-ui.badge>
                                @elseif($batch->status === 'processing')
                                    <x-ui.badge variant="info">Processing</x-ui.badge>
                                @else
                                    <x-ui.badge variant="draft">{{ $batch->status }}</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $batch->imported_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-slate-400">Belum ada riwayat import.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-sm text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $batches->firstItem() }}–{{ $batches->lastItem() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $batches->total() }}</span> data
                </p>
                <div class="flex items-center gap-1">
                    @if($batches->onFirstPage())
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                    @else
                        <a href="{{ $batches->previousPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                    @endif
                    @foreach($batches->getUrlRange(max(1, $batches->currentPage() - 2), min($batches->lastPage(), $batches->currentPage() + 2)) as $page => $url)
                        @if($page == $batches->currentPage())
                            <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-bold text-white bg-emerald-600 border border-emerald-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-9 h-9 px-2 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                    @if($batches->hasMorePages())
                        <a href="{{ $batches->nextPageUrl() }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                    @else
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 bg-white border border-slate-200 cursor-not-allowed"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
