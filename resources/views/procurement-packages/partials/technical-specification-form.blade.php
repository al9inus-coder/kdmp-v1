<form action="{{ route('technical-specifications.update', $procurementPackage->technicalSpecification) }}" method="POST" class="h-full flex flex-col">
    @csrf
    @method('PUT')
    
    <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="file-edit" class="w-5 h-5 text-emerald-500"></i>
                Review Spesifikasi Teknis
            </h2>
            <p class="text-sm text-slate-500 mt-1">Periksa dan sesuaikan dokumen Spesifikasi Teknis.</p>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan
            </button>
            <button type="button" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-slate-200 focus:ring-offset-1">
                <i data-lucide="settings-2" class="w-4 h-4 text-slate-400"></i> Edit Prompt
            </button>
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                <i data-lucide="sparkles" class="w-4 h-4 text-indigo-500"></i> Generate AI
            </button>
            @if($procurementPackage->technicalSpecification)
            <a href="{{ route('technical-specifications.print', $procurementPackage->technicalSpecification) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-slate-500 focus:ring-offset-1">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak PDF
            </a>
            @endif
        </div>
    </div>
    
    <div class="p-6 flex-1 overflow-y-auto bg-white">
        @if($procurementPackage->technicalSpecification)
            @php
                $technicalSpecification = $procurementPackage->technicalSpecification;
            @endphp
            
            <div class="space-y-6 max-w-4xl mx-auto">
                <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-bold text-blue-800">Informasi</h4>
                        <p class="text-sm text-blue-600 mt-1">Anda dapat menyesuaikan isi spesifikasi teknis di bawah ini. Pastikan untuk mengklik tombol <strong>Simpan</strong> setelah melakukan perubahan.</p>
                    </div>
                </div>

                <!-- Latar Belakang -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs">1</span>
                        Latar Belakang
                    </label>
                    <textarea name="latar_belakang" rows="6" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm p-4">{{ old('latar_belakang', $technicalSpecification->latar_belakang) }}</textarea>
                </div>

                <!-- Maksud dan Tujuan -->
                <div class="space-y-4">
                    <label class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs">2</span>
                        Maksud dan Tujuan
                    </label>
                    <div class="pl-8 space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600">a. Maksud</label>
                            <textarea name="maksud[Maksud]" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm p-3">{{ old('maksud.Maksud', $technicalSpecification->maksud['Maksud'] ?? '') }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600">b. Tujuan</label>
                            <textarea name="maksud[Tujuan]" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm p-3">{{ old('maksud.Tujuan', $technicalSpecification->maksud['Tujuan'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Target dan Sasaran -->
                <div class="space-y-4">
                    <label class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs">3</span>
                        Target dan Sasaran
                    </label>
                    <div class="pl-8 space-y-4">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600">a. Target</label>
                            <textarea name="target_sasaran[Target]" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm p-3">{{ old('target_sasaran.Target', $technicalSpecification->target_sasaran['Target'] ?? '') }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-600">b. Sasaran</label>
                            <textarea name="target_sasaran[Sasaran]" rows="3" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm p-3">{{ old('target_sasaran.Sasaran', $technicalSpecification->target_sasaran['Sasaran'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Uraian Pekerjaan -->
                <div class="space-y-2">
                    <label class="text-sm font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-xs">4</span>
                        Uraian Pekerjaan
                    </label>
                    <textarea name="uraian_pekerjaan" rows="6" class="w-full rounded-xl border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm p-4">{{ old('uraian_pekerjaan', $technicalSpecification->uraian_pekerjaan) }}</textarea>
                </div>
                
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-full text-slate-500 space-y-4 my-20">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
                    <i data-lucide="file-x" class="w-8 h-8 text-slate-400"></i>
                </div>
                <div class="text-center">
                    <p class="font-semibold text-lg text-slate-700">Spesifikasi Teknis Belum Dibuat</p>
                    <p class="text-sm mt-1">Silakan Generate AI atau buat draf spesifikasi teknis terlebih dahulu.</p>
                </div>
            </div>
        @endif
    </div>
</form>
