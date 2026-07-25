@component('layouts.kdmp')
@section('title', 'Anggaran — ' . $subActivity->nama)

@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $sisa = $ringkas['plafon'] - $ringkas['terinput'];
@endphp

<x-ui.toast />

<x-ui.workspace title="{{ $subActivity->nama }}"
    description="{{ $subActivity->kode }} · {{ $subActivity->activity?->nama }}">
    <x-slot:actions>
        <x-ui.button variant="secondary" size="md" href="{{ route('admin.anggaran.index', ['tahun' => $tahunId]) }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
        <x-ui.button variant="primary" size="md"
            href="{{ route('admin.anggaran.create', ['sub_kegiatan' => $subActivity->id, 'tahun' => $tahunId]) }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Rekening
        </x-ui.button>
    </x-slot:actions>

    {{-- Jejak hierarki --}}
    <div class="flex flex-wrap items-center gap-2 mb-5 text-xs font-semibold">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600">
            <i data-lucide="folder-kanban" class="w-3.5 h-3.5 text-blue-500"></i>
            {{ $subActivity->activity?->program?->nama ?? '-' }}
        </span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600">
            {{ $subActivity->activity?->kode }}
        </span>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-300"></i>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700">
            {{ $subActivity->kode }}
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 ml-auto">
            <i data-lucide="calendar" class="w-3.5 h-3.5"></i> TA {{ $tahun->tahun ?? '-' }}
        </span>
    </div>

    {{-- Ringkasan sub kegiatan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Plafon DPA</p>
            <p class="text-xl font-extrabold text-slate-900 mt-1.5">{{ $rupiah($ringkas['plafon']) }}</p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">{{ $baris->count() }} rekening belanja</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Terinput Paket</p>
            <p class="text-xl font-extrabold text-blue-600 mt-1.5">{{ $rupiah($ringkas['terinput']) }}</p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">{{ $baris->sum('jumlahPaket') }} paket RUP</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Selisih</p>
            <p class="text-xl font-extrabold {{ abs($sisa) < 0.01 ? 'text-emerald-600' : ($sisa > 0 ? 'text-amber-600' : 'text-rose-600') }} mt-1.5">
                {{ abs($sisa) < 0.01 ? 'Sesuai' : $rupiah($sisa) }}
            </p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">
                {{ $ringkas['belumSeimbang'] }} rekening belum seimbang
            </p>
        </x-ui.card>
    </div>

    {{-- Tabel rekening + edit massal --}}
    <form action="{{ route('admin.anggaran.revisi-massal', [$subActivity, 'tahun' => $tahunId]) }}" method="POST">
        @csrf
        <x-ui.card padding="none">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="list-tree" class="w-4 h-4 text-emerald-500"></i> Rekening Belanja
                    </h2>
                    <p class="text-[11px] text-slate-400 font-semibold mt-0.5">
                        Ubah angka plafon yang perlu direvisi, lalu catat sekali di bawah — dasar hukumnya berlaku untuk semua.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Rekening</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-32">Tahap</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-48">Plafon (Rp)</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-40">Terinput</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-40">Selisih</th>
                            <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Riwayat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($baris as $b)
                            @php
                                $line = $b['line'];
                                $seimbang = abs($b['selisih']) < 0.01;
                                $revisi = $line->revisions->last();
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900 leading-snug">
                                        <span class="font-mono text-emerald-700">{{ $line->account?->kode ?? '-' }}</span>
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $line->account?->nama ?? 'Rekening terhapus' }}</p>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if($revisi)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border
                                            {{ $revisi->jenis === 'perubahan' ? 'bg-violet-50 text-violet-700 border-violet-200'
                                               : ($revisi->jenis === 'pergeseran' ? 'bg-amber-50 text-amber-700 border-amber-200'
                                               : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                            {{ $revisi->label }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <input type="number" step="0.01" min="0"
                                        name="pagu[{{ $line->id }}]"
                                        value="{{ old('pagu.' . $line->id, (float) $line->pagu_efektif) }}"
                                        data-awal="{{ (float) $line->pagu_efektif }}"
                                        class="pagu-input w-full text-right font-bold rounded-lg border-slate-200 text-sm py-1.5 focus:border-emerald-500 focus:ring-emerald-500">
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <span class="font-semibold text-blue-600">{{ $rupiah($b['terinput']) }}</span>
                                    <span class="block text-[10px] text-slate-400 font-semibold">{{ $b['jumlahPaket'] }} paket</span>
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    @if($seimbang)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Sesuai
                                        </span>
                                    @else
                                        <span class="font-bold {{ $b['selisih'] > 0 ? 'text-amber-600' : 'text-rose-600' }}">{{ $rupiah($b['selisih']) }}</span>
                                        <span class="block text-[10px] font-bold {{ $b['selisih'] > 0 ? 'text-amber-500' : 'text-rose-500' }} uppercase tracking-wide">
                                            {{ $b['selisih'] > 0 ? 'belum dirinci' : 'melebihi' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('admin.anggaran.edit', $line) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 transition-colors"
                                        title="Riwayat revisi rekening ini">
                                        <i data-lucide="history" class="w-3.5 h-3.5"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12">
                                    <x-ui.empty-state icon="wallet" title="Belum Ada Rekening"
                                        description="Sub kegiatan ini belum punya plafon DPA. Tambahkan rekening belanja beserta pagunya.">
                                        <x-ui.button variant="primary" size="md"
                                            href="{{ route('admin.anggaran.create', ['sub_kegiatan' => $subActivity->id, 'tahun' => $tahunId]) }}">
                                            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Rekening
                                        </x-ui.button>
                                    </x-ui.empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.card>

        {{-- Catat revisi untuk seluruh rekening yang berubah --}}
        @if($baris->isNotEmpty())
            <x-ui.card padding="none" class="mt-5">
                <div class="px-5 py-4 border-b border-slate-100 bg-blue-50/50 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Catat Revisi</h3>
                        <p class="text-xs text-slate-500">Berlaku untuk semua rekening yang angkanya Anda ubah di atas.</p>
                    </div>
                    <span id="hitungBerubah"
                        class="hidden ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i> <span></span>
                    </span>
                </div>
                <div class="p-5 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tahap Anggaran <span class="text-rose-500">*</span></label>
                        <x-ui.select name="jenis" required>
                            @foreach($jenisOptions as $key => $label)
                                <option value="{{ $key }}" @selected(old('jenis', 'pergeseran') === $key)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Dasar</label>
                        <x-ui.input type="date" name="tanggal" :value="old('tanggal')" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Dasar Hukum</label>
                        <x-ui.input type="text" name="nomor_dasar" :value="old('nomor_dasar')" placeholder="Perda / Perkada" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan</label>
                        <x-ui.input type="text" name="keterangan" :value="old('keterangan')" placeholder="Opsional" />
                    </div>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex items-center justify-between gap-3">
                    <p class="text-[11px] text-slate-400 font-semibold">
                        Rekening yang nilainya tidak berubah tidak akan dicatat, sehingga riwayat tetap bersih.
                    </p>
                    <x-ui.button variant="primary" size="md" type="submit">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i> Catat Revisi
                    </x-ui.button>
                </div>
            </x-ui.card>
        @endif
    </form>
</x-ui.workspace>

<script>
    // Tandai berapa rekening yang angkanya berubah, supaya operator sadar
    // apa yang akan tercatat sebelum menekan simpan.
    (function () {
        const inputs = Array.from(document.querySelectorAll('.pagu-input'));
        const chip = document.getElementById('hitungBerubah');
        if (!inputs.length || !chip) return;

        function hitung() {
            let n = 0;
            inputs.forEach(function (el) {
                const berubah = Math.abs(parseFloat(el.value || 0) - parseFloat(el.dataset.awal || 0)) >= 0.01;
                el.classList.toggle('border-amber-400', berubah);
                el.classList.toggle('bg-amber-50', berubah);
                if (berubah) n++;
            });
            chip.classList.toggle('hidden', n === 0);
            chip.querySelector('span').textContent = n + ' rekening diubah';
        }

        inputs.forEach(el => el.addEventListener('input', hitung));
        hitung();
    })();
</script>
@endcomponent
