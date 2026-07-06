@component('layouts.kdmp')
@section('title', 'Jadwal Rencana Pengadaan')

<x-ui.toast />

<x-ui.workspace title="Jadwal Pengadaan" description="Timeline Rencana Umum Pengadaan (RUP) tahun anggaran berjalan.">
    <x-slot:actions>
        <form method="GET" action="{{ route('schedules.index') }}" class="flex items-center gap-2">
            <label class="text-sm font-semibold text-slate-600">Tahun Anggaran:</label>
            <select name="fiscal_year_id" onchange="this.form.submit()"
                class="px-4 py-2 text-sm font-semibold border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                @foreach($fiscalYears as $year)
                    <option value="{{ $year->id }}" @selected($fiscalYearId == $year->id)>
                        {{ $year->tahun }} {{ $year->is_active ? '(Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
        </form>
    </x-slot:actions>

    <x-ui.card padding="none">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="calendar-range" class="w-4 h-4 text-emerald-500"></i> Timeline Master RUP
            </h3>
            <div class="flex items-center gap-4 text-xs">
                <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="w-3 h-3 rounded bg-amber-400"></span> Pemilihan</span>
                <span class="inline-flex items-center gap-1.5 text-slate-600"><span class="w-3 h-3 rounded bg-sky-500"></span> Kontrak</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 border border-slate-200 font-semibold">Top {{ $packages->count() }} Paket</span>
            </div>
        </div>

        <div class="overflow-auto" style="max-height: 620px;">
            <table class="w-full text-sm border-collapse" style="min-width: 900px;">
                <thead class="sticky top-0 z-10">
                    <tr class="bg-slate-50">
                        <th rowspan="2" class="align-middle text-left px-5 py-3 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50" style="width: 320px;">Nama Paket</th>
                        <th colspan="12" class="text-center px-3 py-2 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-l border-slate-200 bg-slate-50">Bulan Pelaksanaan</th>
                    </tr>
                    <tr class="bg-slate-50">
                        @for($i = 1; $i <= 12; $i++)
                            <th class="px-1 py-2 text-[11px] font-bold text-slate-500 uppercase border-b border-l border-slate-200 bg-slate-50" style="width: 48px;">{{ \Carbon\Carbon::create()->month($i)->translatedFormat('M') }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($packages as $paket)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3 align-middle border-r border-slate-100" style="max-width: 320px;">
                                <a href="{{ route('procurement-packages.show', $paket) }}" class="font-semibold text-slate-800 hover:text-emerald-600 transition-colors leading-snug block" title="{{ $paket->nama_paket }}">
                                    {{ \Illuminate\Support\Str::limit($paket->nama_paket, 55) }}
                                </a>
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                @php
                                    $isPemilihan = ($i >= $paket->pemilihan_mulai_bulan && $i <= $paket->pemilihan_selesai_bulan);
                                    $isKontrak = ($i >= $paket->kontrak_mulai_bulan && $i <= $paket->kontrak_selesai_bulan);
                                    $style = '';
                                    if ($isPemilihan && $isKontrak) {
                                        $style = 'background: linear-gradient(135deg, #fbbf24 50%, #0ea5e9 50%);';
                                    } elseif ($isPemilihan) {
                                        $style = 'background-color: #fbbf24;';
                                    } elseif ($isKontrak) {
                                        $style = 'background-color: #0ea5e9;';
                                    }
                                @endphp
                                <td class="border-l border-slate-100 p-1">
                                    <div class="h-6 rounded" style="{{ $style }}"></div>
                                </td>
                            @endfor
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <i data-lucide="calendar-off" class="w-10 h-10 opacity-40"></i>
                                    <p class="text-sm">Belum ada data jadwal paket (Bulan Pemilihan/Kontrak belum diatur di Master Paket).</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-start gap-2 text-sm text-slate-500">
            <i data-lucide="info" class="w-4 h-4 text-sky-500 mt-0.5 shrink-0"></i>
            <p>Data ditarik otomatis dari Rencana Umum Pengadaan (RUP). Kotak <span class="font-semibold text-amber-600">kuning</span> menandakan rentang jadwal <strong>Pemilihan Penyedia</strong>, kotak <span class="font-semibold text-sky-600">biru</span> menandakan rentang <strong>Pelaksanaan Kontrak</strong>.</p>
        </div>
    </x-ui.card>
</x-ui.workspace>
@endcomponent
