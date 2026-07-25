@component('layouts.kdmp')
@section('title', 'Anggaran (DPA)')

@php
    $rupiah = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $tahunAktif = $fiscalYears->firstWhere('id', $tahunId);
@endphp

<x-ui.toast />

<x-ui.workspace title="Anggaran (DPA)"
    description="Plafon per rekening belanja, dikelompokkan mengikuti struktur Program → Kegiatan → Sub Kegiatan.">
    <x-slot:actions>
        <form method="GET" action="{{ route('admin.anggaran.index') }}">
            <select name="tahun" onchange="this.form.submit()"
                class="py-2 text-sm border-slate-200 rounded-xl bg-white focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
                @foreach($fiscalYears as $fy)
                    <option value="{{ $fy->id }}" @selected($tahunId == $fy->id)>TA {{ $fy->tahun }}</option>
                @endforeach
            </select>
        </form>
    </x-slot:actions>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Plafon</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ $rupiah($ringkas['plafon']) }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-2">{{ $ringkas['rekening'] }} rekening · {{ $ringkas['subKegiatan'] }} sub kegiatan</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Sudah Dirinci</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-2">{{ $rupiah($ringkas['terinput']) }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-2">jumlah pagu paket RUP</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Belum Dirinci</p>
            @php $sisaRinci = $ringkas['plafon'] - $ringkas['terinput']; @endphp
            <p class="text-2xl font-extrabold {{ abs($sisaRinci) < 0.01 ? 'text-emerald-600' : ($sisaRinci > 0 ? 'text-amber-600' : 'text-rose-600') }} mt-2">
                {{ $rupiah($sisaRinci) }}
            </p>
            <p class="text-xs font-semibold text-slate-500 mt-2">selisih plafon vs paket</p>
        </x-ui.card>
        <x-ui.card padding="md">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Perlu Ditinjau</p>
            <p class="text-2xl font-extrabold {{ $ringkas['belumSeimbang'] > 0 ? 'text-amber-600' : 'text-emerald-600' }} mt-2">
                {{ $ringkas['belumSeimbang'] }}
            </p>
            <p class="text-xs font-semibold text-slate-500 mt-2">
                {{ $ringkas['belumSeimbang'] > 0 ? 'sub kegiatan belum seimbang' : 'semua sudah seimbang' }}
                @if($ringkas['belumAdaPlafon'] > 0)
                    <span class="block text-slate-400 mt-0.5">{{ $ringkas['belumAdaPlafon'] }} belum diisi plafon</span>
                @endif
            </p>
        </x-ui.card>
    </div>

    {{-- Peringatan pemetaan paket: bukan soal plafon, tapi data yang belum lengkap --}}
    @if($ringkas['tanpaRekeningJumlah'] > 0 || $ringkas['yatimJumlah'] > 0)
        <div class="mb-6 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
            <div class="p-1.5 rounded-full bg-amber-100 shrink-0">
                <i data-lucide="link-2-off" class="w-4 h-4 text-amber-600"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-bold text-amber-800">Ada paket RUP yang belum terpetakan ke anggaran</p>
                <ul class="mt-1 text-xs text-amber-700 space-y-0.5">
                    @if($ringkas['tanpaRekeningJumlah'] > 0)
                        <li>
                            <b>{{ $ringkas['tanpaRekeningJumlah'] }} paket</b> ({{ $rupiah($ringkas['tanpaRekeningTotal']) }})
                            sudah punya sub kegiatan tetapi <b>belum punya rekening belanja</b> — sehingga tidak bisa
                            dibandingkan dengan plafon mana pun.
                        </li>
                    @endif
                    @if($ringkas['yatimJumlah'] > 0)
                        <li>
                            <b>{{ $ringkas['yatimJumlah'] }} paket</b> ({{ $rupiah($ringkas['yatimTotal']) }})
                            belum punya sub kegiatan sama sekali.
                        </li>
                    @endif
                </ul>
                <p class="mt-1.5 text-[11px] text-amber-600 font-semibold">
                    Lengkapi rekening paket lewat menu Paket RUP agar angkanya ikut terhitung di sini.
                </p>
            </div>
        </div>
    @endif

    {{-- Program → Kegiatan → kartu Sub Kegiatan --}}
    <div class="space-y-6">
        @forelse($programs as $program)
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                    <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2">
                        <i data-lucide="folder-kanban" class="w-5 h-5 text-blue-500"></i>
                        {{ $program->kode }} - {{ $program->nama }}
                    </h2>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($program->activities as $activity)
                        <div class="p-5">
                            <div class="mb-4">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kegiatan</p>
                                <h3 class="text-sm font-bold text-slate-800 mt-1">{{ $activity->kode }} - {{ $activity->nama }}</h3>
                            </div>

                            @if($activity->subActivities->isEmpty())
                                <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/60">
                                    <i data-lucide="layers" class="w-4 h-4 text-slate-300 shrink-0"></i>
                                    <p class="text-xs text-slate-500 font-semibold">Belum ada sub kegiatan pada kegiatan ini.</p>
                                    <a href="{{ route('admin.sub-activities.index') }}"
                                        class="ml-auto text-xs font-bold text-emerald-600 hover:text-emerald-700 whitespace-nowrap">
                                        Kelola Sub Kegiatan →
                                    </a>
                                </div>
                            @else
                            <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-4">
                                @foreach($activity->subActivities as $subActivity)
                                    @php
                                        $r = $ringkasSub[$subActivity->id] ?? ['plafon' => 0, 'terinput' => 0, 'selisih' => 0, 'jumlahRekening' => 0, 'jumlahPaket' => 0, 'adaPlafon' => false];
                                        $seimbang = abs($r['selisih']) < 0.01;
                                        $persen = $r['plafon'] > 0 ? min(100, $r['terinput'] / $r['plafon'] * 100) : 0;

                                        if (!$r['adaPlafon']) {
                                            $tone = 'slate'; $badge = 'Belum ada plafon';
                                        } elseif ($seimbang) {
                                            $tone = 'emerald'; $badge = 'Sesuai';
                                        } elseif ($r['selisih'] > 0) {
                                            $tone = 'amber'; $badge = 'Belum dirinci';
                                        } else {
                                            $tone = 'rose'; $badge = 'Melebihi plafon';
                                        }

                                        $toneClass = [
                                            'emerald' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
                                            'amber' => 'text-amber-600 bg-amber-50 border-amber-100',
                                            'rose' => 'text-rose-600 bg-rose-50 border-rose-100',
                                            'slate' => 'text-slate-500 bg-slate-100 border-slate-200',
                                        ][$tone];
                                        $barClass = [
                                            'emerald' => 'bg-emerald-500',
                                            'amber' => 'bg-amber-500',
                                            'rose' => 'bg-rose-500',
                                            'slate' => 'bg-slate-300',
                                        ][$tone];
                                    @endphp

                                    <a href="{{ route('admin.anggaran.sub-kegiatan', [$subActivity, 'tahun' => $tahunId]) }}"
                                        class="group block rounded-2xl border border-slate-200 bg-white hover:border-emerald-200 hover:shadow-md transition-all overflow-hidden">
                                        <div class="p-4">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-extrabold text-slate-500">{{ $subActivity->kode }}</p>
                                                    <h4 class="text-sm font-bold text-slate-800 mt-1 leading-snug group-hover:text-emerald-700">
                                                        {{ $subActivity->nama }}
                                                    </h4>
                                                </div>
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full border text-[11px] font-bold {{ $toneClass }} shrink-0 whitespace-nowrap">
                                                    {{ $badge }}
                                                </span>
                                            </div>

                                            <div class="space-y-2.5 mt-4 text-xs">
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-slate-400 font-semibold">Plafon DPA</span>
                                                    <span class="text-slate-800 font-bold text-right">{{ $rupiah($r['plafon']) }}</span>
                                                </div>
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-slate-400 font-semibold">Terinput</span>
                                                    <span class="text-blue-600 font-bold text-right">{{ $rupiah($r['terinput']) }}</span>
                                                </div>
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="text-slate-400 font-semibold">Selisih</span>
                                                    <span class="font-bold text-right {{ $seimbang ? 'text-emerald-600' : ($r['selisih'] > 0 ? 'text-amber-600' : 'text-rose-600') }}">
                                                        {{ $seimbang ? '—' : $rupiah($r['selisih']) }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if($r['tanpaRekeningJumlah'] > 0)
                                                <p class="mt-2.5 inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-amber-50 border border-amber-100 text-[10px] font-bold text-amber-700">
                                                    <i data-lucide="link-2-off" class="w-3 h-3"></i>
                                                    {{ $r['tanpaRekeningJumlah'] }} paket belum berrekening
                                                </p>
                                            @endif

                                            <div class="mt-3">
                                                <div class="h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                                    <div class="h-full {{ $barClass }} rounded-full" style="width: {{ $persen }}%"></div>
                                                </div>
                                                <div class="flex items-center justify-between mt-2 text-[11px] font-semibold text-slate-400">
                                                    <span>{{ $r['jumlahRekening'] }} rekening · {{ $r['jumlahPaket'] }} paket</span>
                                                    <span class="inline-flex items-center gap-1 text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        Kelola <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-5">
                            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-dashed border-slate-200 bg-slate-50/60">
                                <i data-lucide="briefcase" class="w-4 h-4 text-slate-300 shrink-0"></i>
                                <p class="text-xs text-slate-500 font-semibold">Belum ada kegiatan pada program ini.</p>
                                <a href="{{ route('admin.activities.index') }}"
                                    class="ml-auto text-xs font-bold text-emerald-600 hover:text-emerald-700 whitespace-nowrap">
                                    Kelola Kegiatan →
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <x-ui.card padding="md">
                <x-ui.empty-state icon="wallet" title="Belum Ada Data Anggaran"
                    description="Belum ada plafon maupun paket pada tahun anggaran ini. Jalankan perintah anggaran:seed-dpa untuk membentuk baris dari paket yang sudah ada." />
            </x-ui.card>
        @endforelse
    </div>
</x-ui.workspace>
@endcomponent
