@component('layouts.kdmp')
@section('title', 'Kelola Baris Anggaran')

@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $selisih = (float) $anggaran->pagu_efektif - $terinput;
    $seimbang = abs($selisih) < 0.01;
    $revisions = $anggaran->revisions;
@endphp

<x-ui.toast />

<x-ui.workspace title="Kelola Baris Anggaran"
    description="{{ $anggaran->account?->kode }} — {{ $anggaran->account?->nama }}">
    <x-slot:actions>
        <x-ui.button variant="secondary" size="md" href="{{ route('admin.anggaran.index') }}">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Kembali
        </x-ui.button>
    </x-slot:actions>

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_380px] gap-5 items-start">

        {{-- Kolom kiri --}}
        <div class="space-y-5 min-w-0">
            {{-- Ringkasan rekonsiliasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <x-ui.card padding="md">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Plafon Berlaku</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $rupiah($anggaran->pagu_efektif) }}</p>
                </x-ui.card>
                <x-ui.card padding="md">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Terinput Paket</p>
                    <p class="text-xl font-extrabold text-blue-600 mt-1">{{ $rupiah($terinput) }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">{{ $jumlahPaket }} paket RUP</p>
                </x-ui.card>
                <x-ui.card padding="md">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Selisih</p>
                    @if($seimbang)
                        <p class="text-xl font-extrabold text-emerald-600 mt-1">Sesuai</p>
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">rincian paket sudah pas</p>
                    @else
                        <p class="text-xl font-extrabold {{ $selisih > 0 ? 'text-amber-600' : 'text-rose-600' }} mt-1">{{ $rupiah($selisih) }}</p>
                        <p class="text-[11px] font-semibold {{ $selisih > 0 ? 'text-amber-500' : 'text-rose-500' }} mt-0.5">
                            {{ $selisih > 0 ? 'pagu belum dirinci jadi paket' : 'rincian melebihi plafon' }}
                        </p>
                    @endif
                </x-ui.card>
            </div>

            {{-- Riwayat revisi --}}
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-500"></i> Riwayat Revisi
                    </h3>
                    <span class="text-[11px] font-semibold text-slate-400">{{ $revisions->count() }} langkah</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Tahap</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">Dasar</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Pagu</th>
                                <th class="px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Perubahan</th>
                                <th class="px-5 py-3 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php $sebelumnya = null; @endphp
                            @foreach($revisions as $rev)
                                @php
                                    $delta = $sebelumnya === null ? null : (float) $rev->pagu - $sebelumnya;
                                    $sebelumnya = (float) $rev->pagu;
                                    $terakhir = $loop->last;
                                @endphp
                                <tr class="{{ $terakhir ? 'bg-emerald-50/40' : '' }}">
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border
                                            {{ $rev->jenis === 'perubahan' ? 'bg-violet-50 text-violet-700 border-violet-200'
                                               : ($rev->jenis === 'pergeseran' ? 'bg-amber-50 text-amber-700 border-amber-200'
                                               : 'bg-slate-100 text-slate-600 border-slate-200') }}">
                                            {{ $rev->label }}
                                        </span>
                                        @if($terakhir)
                                            <span class="block text-[10px] font-bold text-emerald-600 mt-1">← berlaku saat ini</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <p class="text-xs font-semibold text-slate-700">{{ $rev->nomor_dasar ?: '—' }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $rev->tanggal?->format('d-m-Y') ?: 'tanpa tanggal' }}</p>
                                        @if($rev->keterangan)
                                            <p class="text-[11px] text-slate-400 mt-0.5 max-w-md">{{ $rev->keterangan }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-slate-900 whitespace-nowrap">{{ $rupiah($rev->pagu) }}</td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        @if($delta === null)
                                            <span class="text-xs text-slate-300">nilai awal</span>
                                        @elseif(abs($delta) < 0.01)
                                            <span class="text-xs text-slate-400">tetap</span>
                                        @else
                                            <span class="text-xs font-bold {{ $delta > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $delta > 0 ? '+' : '' }}{{ $rupiah($delta) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        @if($terakhir && $revisions->count() > 1)
                                            <form action="{{ route('admin.anggaran.revisions.destroy', [$anggaran, $rev]) }}" method="POST"
                                                onsubmit="return confirm('Batalkan revisi terakhir ini? Pagu berlaku kembali ke nilai sebelumnya.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100 transition-colors" title="Batalkan revisi terakhir">
                                                    <i data-lucide="undo-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Identitas baris --}}
            <form action="{{ route('admin.anggaran.update', $anggaran) }}" method="POST">
                @csrf
                @method('PUT')
                @include('anggaran._form', ['submitLabel' => 'Simpan Perubahan'])
            </form>
        </div>

        {{-- Kolom kanan: catat revisi --}}
        <aside class="xl:sticky xl:top-20 space-y-5">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-blue-50/60">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-plus" class="w-4 h-4 text-blue-500"></i> Catat Revisi Baru
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pagu lama tetap tersimpan sebagai riwayat.</p>
                </div>
                <form action="{{ route('admin.anggaran.revisions.store', $anggaran) }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tahap Anggaran <span class="text-rose-500">*</span></label>
                        <x-ui.select name="jenis" required>
                            @foreach($jenisOptions as $key => $label)
                                <option value="{{ $key }}" @selected(old('jenis') === $key)>{{ $label }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pagu Baru (Rp) <span class="text-rose-500">*</span></label>
                        <x-ui.input type="number" name="pagu" step="0.01" min="0" :value="old('pagu', $anggaran->pagu_efektif)" required />
                        <p class="mt-1 text-[11px] text-slate-400">Isi nilai akhir setelah revisi, bukan selisihnya.</p>
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
                        <x-ui.textarea name="keterangan" rows="2" placeholder="Alasan revisi (opsional)">{{ old('keterangan') }}</x-ui.textarea>
                    </div>
                    <x-ui.button variant="primary" size="md" type="submit" class="w-full justify-center">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Catat Revisi
                    </x-ui.button>
                </form>
            </section>

            <section class="bg-white border border-rose-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-rose-50/50">
                    <h3 class="text-xs font-black text-slate-700 uppercase tracking-wide flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-500"></i> Hapus Baris
                    </h3>
                </div>
                <div class="p-4">
                    <p class="text-xs text-slate-500 mb-3">Menghapus baris ini sekaligus menghapus seluruh riwayat revisinya. Paket RUP tidak terpengaruh.</p>
                    <form action="{{ route('admin.anggaran.destroy', $anggaran) }}" method="POST"
                        onsubmit="return confirm('Hapus baris anggaran ini beserta seluruh riwayatnya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-rose-700 bg-white border border-rose-200 hover:bg-rose-50 rounded-lg transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Baris Anggaran
                        </button>
                    </form>
                </div>
            </section>
        </aside>
    </div>
</x-ui.workspace>
@endcomponent
