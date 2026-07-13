@component('layouts.kdmp')
@section('title', 'Impor Paket Pengadaan')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                Impor <span class="text-emerald-600">Paket Pengadaan</span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Unggah file Excel ekspor untuk menambahkan banyak paket sekaligus.</p>
        </div>
        <a href="{{ auth()->user()->hasRole('Staff') ? route('staf.packages.index') : route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Kembali
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 flex items-start gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold text-sm">Import Selesai</p>
                <p class="text-sm mt-0.5">{{ session('success') }}</p>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold text-sm">Import Gagal</p>
                <p class="text-sm mt-0.5">{{ session('error') }}</p>
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl p-4 flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 mt-0.5 shrink-0"></i>
            <div>
                <p class="font-semibold text-sm mb-1">Periksa isian berikut:</p>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Import Card --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="h-1 bg-gradient-to-r from-emerald-500 via-teal-400 to-blue-400"></div>

        {{-- Stepper --}}
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <ol class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <li class="flex items-center gap-3">
                    <span class="shrink-0 w-9 h-9 rounded-full bg-emerald-600 text-white text-sm font-bold flex items-center justify-center shadow-sm shadow-emerald-200">1</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800">Unduh Template</p>
                        <a href="{{ asset('template/template_import_rup.xlsx') }}" download
                            class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline mt-0.5">
                            <i data-lucide="download" class="w-3 h-3"></i>
                            template_import_rup.xlsx
                        </a>
                    </div>
                    <i data-lucide="chevron-right" class="hidden sm:block w-4 h-4 text-slate-300 ml-auto shrink-0"></i>
                </li>
                <li class="flex items-center gap-3">
                    <span class="shrink-0 w-9 h-9 rounded-full bg-white border-2 border-emerald-200 text-emerald-600 text-sm font-bold flex items-center justify-center">2</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800">Lengkapi Data</p>
                        <p class="text-xs text-slate-500 mt-0.5">ID RUP &amp; Nama Paket wajib. Pagu tanpa titik/koma.</p>
                    </div>
                    <i data-lucide="chevron-right" class="hidden sm:block w-4 h-4 text-slate-300 ml-auto shrink-0"></i>
                </li>
                <li class="flex items-center gap-3">
                    <span class="shrink-0 w-9 h-9 rounded-full bg-white border-2 border-emerald-200 text-emerald-600 text-sm font-bold flex items-center justify-center">3</span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-800">Unggah &amp; Proses</p>
                        <p class="text-xs text-slate-500 mt-0.5">Format .xlsx &bull; Maks. 10MB</p>
                    </div>
                </li>
            </ol>
        </div>

        {{-- Form --}}
        <form id="importForm" action="{{ auth()->user()->hasRole('Staff') ? route('staf.packages.import.store') : route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.import.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            @if(auth()->user()->hasRole('Staff'))
                <input type="hidden" name="source" value="staf">
            @endif

            {{-- Drop Zone Hero --}}
            <label for="file" id="dropZone"
                class="flex flex-col items-center justify-center w-full py-9 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-emerald-50/30 hover:border-emerald-400 transition-all group">

                <div id="dropDefault" class="flex flex-col items-center gap-3 text-center pointer-events-none px-4">
                    <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center group-hover:shadow-md group-hover:-translate-y-0.5 transition-all">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6 text-emerald-500"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-700">
                            <span class="text-emerald-600">Klik untuk memilih file</span> atau seret ke sini
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Format: .xlsx &bull; Maks. 10MB</p>
                    </div>
                </div>

                <div id="dropSelected" class="hidden flex-col items-center gap-2 text-center pointer-events-none px-4">
                    <div class="w-12 h-12 bg-emerald-50 rounded-2xl border-2 border-emerald-200 flex items-center justify-center">
                        <i data-lucide="file-check-2" class="w-6 h-6 text-emerald-600"></i>
                    </div>
                    <div>
                        <p id="fileName" class="text-sm font-bold text-emerald-700"></p>
                        <p id="fileSize" class="text-xs text-slate-400 mt-0.5"></p>
                        <p class="text-xs text-emerald-600 mt-1">Klik untuk ganti file</p>
                    </div>
                </div>

                <input id="file" name="file" type="file" class="hidden" accept=".xlsx" required />
            </label>
            @error('file')
                <p class="text-rose-500 text-xs">{{ $message }}</p>
            @enderror

            {{-- Toolbar: Tahun + Submit --}}
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
                <div class="flex items-center gap-3">
                    <label for="fiscal_year_id" class="text-sm font-semibold text-slate-700 shrink-0">
                        Tahun Anggaran <span class="text-rose-500">*</span>
                    </label>
                    <select id="fiscal_year_id" name="fiscal_year_id" required
                        class="w-full sm:w-44 px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all @error('fiscal_year_id') border-rose-400 @enderror">
                        <option value="">Pilih Tahun</option>
                        @foreach($fiscalYears as $fy)
                            <option value="{{ $fy->id }}"
                                @selected((string) old('fiscal_year_id', $activeFiscalYearId) === (string) $fy->id)>
                                {{ $fy->tahun }}{{ $fy->is_active ? ' (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" id="submitBtn"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    Proses Import
                </button>
            </div>
            @error('fiscal_year_id')
                <p class="text-rose-500 text-xs">{{ $message }}</p>
            @enderror
        </form>
    </div>

    {{-- Riwayat Import Batch --}}
    <div class="bg-white border border-slate-200 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="history" class="w-5 h-5 text-slate-400"></i>
                Riwayat Import Batch
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 font-semibold">File</th>
                        <th class="px-5 py-3 font-semibold">Tahun Anggaran</th>
                        <th class="px-5 py-3 font-semibold">Diunggah Oleh</th>
                        <th class="px-5 py-3 font-semibold text-center">Total</th>
                        <th class="px-5 py-3 font-semibold text-center">Berhasil</th>
                        <th class="px-5 py-3 font-semibold text-center">Gagal</th>
                        <th class="px-5 py-3 font-semibold text-center">Status</th>
                        <th class="px-5 py-3 font-semibold">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer"
                            onclick="window.location='{{ auth()->user()->hasRole('Staff') ? route('staf.packages.import.show', $batch->id) : route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'packages.import.show', $batch->id) }}'">
                            <td class="px-5 py-3 font-medium text-slate-800 max-w-48 truncate">
                                <span title="{{ $batch->file_name }}">{{ $batch->file_name }}</span>
                            </td>
                            <td class="px-5 py-3">{{ $batch->fiscalYear->tahun ?? '-' }}</td>
                            <td class="px-5 py-3">{{ $batch->creator->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-center">{{ $batch->total_rows }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-semibold text-emerald-600">{{ $batch->success_rows }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="font-semibold {{ $batch->failed_rows > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $batch->failed_rows }}</span>
                            </td>
                            <td class="px-5 py-3 text-center">
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
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs whitespace-nowrap">
                                {{ $batch->imported_at?->format('d-m-Y H:i') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                    <p class="text-sm">Belum ada riwayat import.</p>
                                </div>
                            </td>
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
    </div>
</div>

<script>
    const input      = document.getElementById('file');
    const dropZone   = document.getElementById('dropZone');
    const dropDef    = document.getElementById('dropDefault');
    const dropSel    = document.getElementById('dropSelected');
    const fileNameEl = document.getElementById('fileName');
    const fileSizeEl = document.getElementById('fileSize');

    function formatBytes(b) {
        if (b < 1024) return b + ' B';
        if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
        return (b / 1048576).toFixed(2) + ' MB';
    }

    function showFile(file) {
        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = formatBytes(file.size);
        dropDef.classList.add('hidden');   dropDef.classList.remove('flex');
        dropSel.classList.remove('hidden'); dropSel.classList.add('flex');
        dropZone.classList.add('border-emerald-400', 'bg-emerald-50/30');
        dropZone.classList.remove('border-slate-300', 'bg-slate-50');
        lucide.createIcons();
    }

    input.addEventListener('change', () => { if (input.files[0]) showFile(input.files[0]); });

    dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('border-emerald-400'); });
    dropZone.addEventListener('dragleave', () => { if (!input.files[0]) dropZone.classList.remove('border-emerald-400'); });
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (file && file.name.endsWith('.xlsx')) {
            const dt = new DataTransfer(); dt.items.add(file); input.files = dt.files;
            showFile(file);
        } else {
            alert('Hanya file .xlsx yang diterima!');
        }
    });

    document.getElementById('importForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Memproses...`;
    });
</script>
@endcomponent
