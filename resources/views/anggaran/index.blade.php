@component('layouts.kdmp')
@section('title', 'Anggaran (DPA)')

@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $tahunAktif = $fiscalYears->firstWhere('id', $tahunId);
@endphp

<x-ui.toast />

<x-ui.workspace title="Anggaran (DPA)"
    description="Plafon per rekening belanja dalam sub kegiatan, beserta riwayat murni, pergeseran, dan perubahan.">
    <x-slot:actions>
        <div class="flex items-center gap-2 bg-slate-50 rounded-full px-4 py-1.5 text-sm text-slate-600 font-medium border border-slate-100 shadow-sm">
            <i data-lucide="calendar" class="w-4 h-4 text-emerald-500"></i>
            TA {{ $tahunAktif->tahun ?? '-' }}
        </div>
        <x-ui.button variant="primary" size="md" href="{{ route('admin.anggaran.create') }}">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Baris
        </x-ui.button>
    </x-slot:actions>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <x-ui.card padding="md">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Baris Anggaran</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $ringkas['baris'] }}</p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">rekening × sub kegiatan</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Plafon</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ $rupiah($ringkas['plafon']) }}</p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">pagu berlaku</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Terinput Paket</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ $rupiah($ringkas['terinput']) }}</p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">jumlah pagu paket RUP</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Belum Seimbang</p>
            <p class="text-2xl font-extrabold {{ $ringkas['belumSeimbang'] > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-1">
                {{ $ringkas['belumSeimbang'] }}
            </p>
            <p class="text-[11px] font-semibold text-slate-400 mt-1">
                {{ $ringkas['belumSeimbang'] > 0 ? 'baris perlu ditinjau' : 'semua sudah cocok' }}
            </p>
        </x-ui.card>
    </div>

    {{-- Filter --}}
    <x-ui.card padding="none" class="mb-6">
        <form method="GET" action="{{ route('admin.anggaran.index') }}" class="p-4 flex flex-col lg:flex-row gap-3">
            <div class="relative flex-1 min-w-0">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari kode/nama rekening atau sub kegiatan..."
                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
            </div>
            <select name="tahun" class="py-2.5 text-sm border-slate-200 rounded-xl bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500">
                @foreach($fiscalYears as $fy)
                    <option value="{{ $fy->id }}" @selected($tahunId == $fy->id)>TA {{ $fy->tahun }}</option>
                @endforeach
            </select>
            <select name="program" class="py-2.5 text-sm border-slate-200 rounded-xl bg-slate-50 focus:ring-emerald-500 focus:border-emerald-500 max-w-xs">
                <option value="">Semua Program</option>
                @foreach($programs as $p)
                    <option value="{{ $p->id }}" @selected($programId == $p->id)>{{ $p->kode }} — {{ Str::limit($p->nama, 40) }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 px-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                <input type="checkbox" name="selisih" value="1" @checked($hanyaSelisih)
                    class="rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                <span class="text-xs font-semibold text-slate-600 whitespace-nowrap">Hanya yang selisih</span>
            </label>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm shadow-emerald-200">
                    <i data-lucide="filter" class="w-4 h-4"></i> Terapkan
                </button>
                <a href="{{ route('admin.anggaran.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>
        </form>
    </x-ui.card>

    {{-- Tabel --}}
    <x-ui.card padding="none">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Rekening Belanja</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-32">Tahap</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-40">Plafon DPA</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-40">Terinput</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right w-44">Selisih</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($baris->groupBy(fn ($b) => $b['line']->sub_activity_id) as $grup)
                        @php
                            $sub = $grup->first()['line']->subActivity;
                            $gPlafon = $grup->sum(fn ($b) => (float) $b['line']->pagu_efektif);
                            $gTerinput = $grup->sum('terinput');
                            $gSelisih = $gPlafon - $gTerinput;
                        @endphp

                        {{-- Baris sub kegiatan: induk dari rekening di bawahnya (struktur DPA) --}}
                        <tr class="bg-slate-50/80">
                            <td class="px-5 py-3">
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">
                                    {{ $sub?->activity?->program?->nama ?? 'Tanpa program' }}
                                </p>
                                <p class="font-bold text-slate-800 leading-snug mt-0.5">
                                    <span class="font-mono text-blue-600">{{ $sub?->kode ?? '-' }}</span>
                                    <span class="text-slate-300 mx-1">·</span>{{ $sub?->nama ?? 'Sub kegiatan terhapus' }}
                                </p>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="text-[10px] font-bold text-slate-400">{{ $grup->count() }} rekening</span>
                            </td>
                            <td class="px-5 py-3 text-right font-extrabold text-slate-800 whitespace-nowrap">{{ $rupiah($gPlafon) }}</td>
                            <td class="px-5 py-3 text-right font-bold text-blue-600 whitespace-nowrap">{{ $rupiah($gTerinput) }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                @if(abs($gSelisih) < 0.01)
                                    <span class="text-xs font-bold text-emerald-600">Sesuai</span>
                                @else
                                    <span class="font-bold {{ $gSelisih > 0 ? 'text-amber-600' : 'text-rose-600' }}">{{ $rupiah($gSelisih) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3"></td>
                        </tr>

                        @foreach($grup as $b)
                        @php
                            $line = $b['line'];
                            $selisih = $b['selisih'];
                            $seimbang = abs($selisih) < 0.01;
                            $revisi = $line->revisions->last();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-5 py-4 pl-10">
                                <div class="flex items-start gap-2">
                                    <i data-lucide="corner-down-right" class="w-3.5 h-3.5 text-slate-300 mt-0.5 shrink-0"></i>
                                    <p class="font-semibold text-slate-900 leading-snug min-w-0">
                                        <span class="font-mono text-emerald-700">{{ $line->account?->kode ?? '-' }}</span>
                                        <span class="text-slate-400 mx-1">·</span>{{ $line->account?->nama ?? 'Rekening terhapus' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
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
                            <td class="px-5 py-4 text-right font-bold text-slate-900 whitespace-nowrap">{{ $rupiah($line->pagu_efektif) }}</td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="font-semibold text-blue-600">{{ $rupiah($b['terinput']) }}</span>
                                <span class="block text-[10px] text-slate-400 font-semibold">{{ $b['jumlahPaket'] }} paket</span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if($seimbang)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Sesuai
                                    </span>
                                @elseif($selisih > 0)
                                    <span class="font-bold text-amber-600">{{ $rupiah($selisih) }}</span>
                                    <span class="block text-[10px] font-bold text-amber-500 uppercase tracking-wide">belum terinput</span>
                                @else
                                    <span class="font-bold text-rose-600">{{ $rupiah($selisih) }}</span>
                                    <span class="block text-[10px] font-bold text-rose-500 uppercase tracking-wide">melebihi plafon</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('admin.anggaran.edit', $line) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-600 bg-emerald-50 border border-emerald-100 hover:bg-emerald-100 transition-colors" title="Kelola & riwayat">
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12">
                                <x-ui.empty-state icon="wallet" title="Belum Ada Baris Anggaran"
                                    description="Tambahkan baris secara manual, atau jalankan perintah anggaran:seed-dpa untuk membentuknya dari pagu paket yang sudah ada.">
                                    <x-ui.button variant="primary" size="md" href="{{ route('admin.anggaran.create') }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Baris
                                    </x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($baris->isNotEmpty())
            <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50 text-[11px] text-slate-400 font-semibold">
                Selisih positif berarti masih ada pagu yang belum dirinci menjadi paket RUP; negatif berarti rincian paket melebihi plafon DPA.
            </div>
        @endif
    </x-ui.card>
</x-ui.workspace>
@endcomponent
