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
        <x-ui.button variant="primary" size="md" type="button" onclick="bukaModal('modalProgram')">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Program
        </x-ui.button>
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

    @if($nonAktif->isNotEmpty())
        <div class="mb-6 flex items-start gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
            <div class="p-1.5 rounded-full bg-slate-200 shrink-0">
                <i data-lucide="eye-off" class="w-4 h-4 text-slate-600"></i>
            </div>
            <div class="min-w-0 w-full">
                <p class="text-sm font-bold text-slate-700">
                    {{ $nonAktif->count() }} sub kegiatan non-aktif masih menyimpan data
                </p>
                <p class="mt-1 text-xs text-slate-500">
                    Cabang yang dinonaktifkan dianggap tidak dijalankan di DPA, jadi tidak ikut ditampilkan
                    maupun dijumlah di halaman ini dan di Monev. Datanya tidak dihapus.
                </p>
                <ul class="mt-2 space-y-1">
                    @foreach($nonAktif as $n)
                        <li class="flex flex-wrap items-baseline gap-x-2 text-xs">
                            <a href="{{ route('admin.anggaran.sub-kegiatan', $n['sub']) }}"
                                class="font-mono font-bold text-slate-700 hover:text-blue-600 hover:underline">
                                {{ $n['sub']->kode }}
                            </a>
                            <span class="text-slate-500 truncate">{{ $n['sub']->nama }}</span>
                            <span class="text-slate-400">
                                &bull; {{ $n['jumlahRekening'] }} rekening ({{ $rupiah($n['plafon']) }})
                                &bull; {{ $n['jumlahPaket'] }} paket ({{ $rupiah($n['paguPaket']) }})
                            </span>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-2 text-[11px] text-slate-500 font-semibold">
                    Aktifkan kembali lewat menu Master bila memang dijalankan, atau pindahkan datanya
                    ke sub kegiatan yang berjalan.
                </p>
            </div>
        </div>
    @endif

    @php
        // Lebar kolom kanan: tiap tahap + terinput + status.
        $lebarKolom = 128;
        $minLebar = 340 + (count($kolomTahap) + 1) * $lebarKolom + 116;
    @endphp

    {{-- Struktur DPA sebagai pohon: Program - Kegiatan - Sub Kegiatan - Rekening.
         Label kolom menempel pada baris judul tiap Program, sehingga setiap
         kartu menerangkan dirinya sendiri tanpa bilah kepala terpisah. --}}
    <div class="overflow-x-auto pb-1">
      <div style="min-width: {{ $minLebar }}px" class="space-y-5">

        @forelse($programs as $program)
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                {{-- Program --}}
                {{-- pr-7 menyamakan tepi kanan dengan baris data (p-4 + px-3) --}}
                <div class="pl-5 pr-7 py-3 border-b border-slate-200 bg-slate-50/60 flex items-center gap-x-4">
                    <div class="flex-1 min-w-0 flex items-center gap-3">
                        <h2 class="text-base font-extrabold text-slate-800 flex items-center gap-2 min-w-0">
                            <i data-lucide="folder-kanban" class="w-5 h-5 text-blue-500 shrink-0"></i>
                            <span class="font-mono text-blue-600 text-sm">{{ $program->kode }}</span>
                            <span class="text-slate-300">&middot;</span>
                            <span class="truncate">{{ $program->nama }}</span>
                        </h2>
                        <button type="button"
                            onclick="bukaModal('modalKegiatan', { program_id: {{ $program->id }}, induk: @js($program->kode . ' - ' . $program->nama) })"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors shrink-0">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> Kegiatan
                        </button>
                    </div>

                    {{-- Label kolom, sebaris dengan judul Program --}}
                    @foreach($kolomTahap as $k)
                        <div class="shrink-0 text-right" style="width: {{ $lebarKolom }}px">
                            <p class="text-[10px] font-black uppercase tracking-wider {{ $k['kunci'] === 'perubahan' ? 'text-violet-500' : ($k['kunci'] === 'murni' ? 'text-slate-500' : 'text-amber-500') }}">
                                {{ $k['label'] }}
                            </p>
                        </div>
                    @endforeach
                    <div class="shrink-0 text-right" style="width: {{ $lebarKolom }}px">
                        <p class="text-[10px] font-black uppercase tracking-wider text-blue-500">Terinput</p>
                    </div>
                    <div class="shrink-0 text-right" style="width: 104px">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Status</p>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    @forelse($program->activities as $activity)
                        {{-- Kegiatan --}}
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2 px-2 py-1.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="corner-down-right" class="w-4 h-4 text-slate-300 shrink-0"></i>
                                    <span class="font-mono text-xs font-bold text-slate-500">{{ $activity->kode }}</span>
                                    <span class="text-slate-300">&middot;</span>
                                    <span class="text-sm font-bold text-slate-700 truncate">{{ $activity->nama }}</span>
                                </div>
                                <button type="button"
                                    onclick="bukaModal('modalSubKegiatan', { activity_id: {{ $activity->id }}, induk: @js($activity->kode . ' - ' . $activity->nama) })"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200 rounded-lg hover:bg-emerald-50 transition-colors shrink-0">
                                    <i data-lucide="plus" class="w-3 h-3"></i> Sub Kegiatan
                                </button>
                            </div>

                            {{-- Sub Kegiatan sebagai daun pohon --}}
                            @if($activity->subActivities->isEmpty())
                                <div class="ml-6 pl-4 border-l border-slate-200">
                                    <div class="flex items-center gap-2 px-3 py-2.5 text-xs text-slate-400 font-semibold">
                                        <i data-lucide="layers" class="w-3.5 h-3.5 shrink-0"></i>
                                        Belum ada sub kegiatan.
                                        <a href="{{ route('admin.sub-activities.index') }}" class="text-emerald-600 hover:text-emerald-700 font-bold">Kelola &rarr;</a>
                                    </div>
                                </div>
                            @else
                                <div class="ml-6 pl-4 border-l border-slate-200 space-y-0.5">
                                    @foreach($activity->subActivities as $subActivity)
                                        @php
                                            $r = $ringkasSub[$subActivity->id] ?? ['plafon' => 0, 'terinput' => 0, 'selisih' => 0, 'jumlahRekening' => 0, 'jumlahPaket' => 0, 'adaPlafon' => false, 'tanpaRekeningJumlah' => 0];
                                            $rekening = $rekeningPerSub[$subActivity->id] ?? collect();
                                            $seimbang = abs($r['selisih']) < 0.01;

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
                                                'emerald' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                                'amber' => 'text-amber-700 bg-amber-50 border-amber-200',
                                                'rose' => 'text-rose-700 bg-rose-50 border-rose-200',
                                                'slate' => 'text-slate-500 bg-slate-100 border-slate-200',
                                            ][$tone];
                                        @endphp

                                        <div class="relative">
                                            <span class="absolute -left-4 top-5 w-4 border-t border-slate-200" aria-hidden="true"></span>

                                            {{-- Sub kegiatan: klik untuk melipat rekening di bawahnya --}}
                                            <button type="button" onclick="lipatSub({{ $subActivity->id }})"
                                                class="w-full text-left group flex flex-wrap items-center gap-x-4 gap-y-1 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-colors">
                                                <i data-lucide="chevron-right" id="ikonSub{{ $subActivity->id }}"
                                                    class="w-4 h-4 text-slate-400 shrink-0 transition-transform"></i>

                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-bold text-slate-800 group-hover:text-emerald-700 leading-snug truncate">
                                                        <span class="font-mono text-xs text-slate-400 mr-1.5">{{ $subActivity->kode }}</span>
                                                        {{ $subActivity->nama }}
                                                    </p>
                                                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">
                                                        {{ $r['jumlahRekening'] }} rekening &middot; {{ $r['jumlahPaket'] }} paket
                                                        @if($r['tanpaRekeningJumlah'] > 0)
                                                            <span class="ml-1 inline-flex items-center gap-1 px-1.5 py-px rounded bg-amber-50 border border-amber-100 text-amber-700 font-bold">
                                                                <i data-lucide="link-2-off" class="w-2.5 h-2.5"></i>{{ $r['tanpaRekeningJumlah'] }} tanpa rekening
                                                            </span>
                                                        @endif
                                                    </p>
                                                </div>

                                                @php $tahapSub = $tahapPerSub[$subActivity->id] ?? []; @endphp
                                                @foreach($kolomTahap as $k)
                                                    @php
                                                        $nilaiTahap = $tahapSub[$k['kunci']] ?? 0;
                                                        $sebelum = $loop->first ? null : ($tahapSub[$kolomTahap[$loop->index - 1]['kunci']] ?? 0);
                                                        $naik = $sebelum !== null ? $nilaiTahap - $sebelum : 0;
                                                    @endphp
                                                    <div class="shrink-0 text-right" style="width: {{ $lebarKolom }}px">
                                                        <p class="text-sm font-extrabold text-slate-900 whitespace-nowrap">{{ $rupiah($nilaiTahap) }}</p>
                                                        @if(abs($naik) >= 0.01)
                                                            <p class="text-[10px] font-bold {{ $naik > 0 ? 'text-emerald-600' : 'text-rose-600' }} whitespace-nowrap">
                                                                {{ $naik > 0 ? '+' : '' }}{{ $rupiah($naik) }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endforeach

                                                <div class="shrink-0 text-right" style="width: {{ $lebarKolom }}px">
                                                    <p class="text-sm font-bold text-blue-600 whitespace-nowrap">{{ $rupiah($r['terinput']) }}</p>
                                                    @unless($seimbang)
                                                        <p class="text-[10px] font-bold {{ $r['selisih'] > 0 ? 'text-amber-600' : 'text-rose-600' }} whitespace-nowrap">
                                                            selisih {{ $r['selisih'] > 0 ? '+' : '' }}{{ $rupiah($r['selisih']) }}
                                                        </p>
                                                    @endunless
                                                </div>

                                                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full border text-[10px] font-bold {{ $toneClass }} shrink-0 whitespace-nowrap w-[104px]">
                                                    {{ $badge }}
                                                </span>
                                            </button>

                                            {{-- Tingkat 3: rekening belanja --}}
                                            <div id="sub{{ $subActivity->id }}" class="hidden ml-8 pl-4 border-l border-dashed border-slate-200 pb-2">
                                                @forelse($rekening as $b)
                                                    @php
                                                        $line = $b['line'];
                                                        $selRek = $b['selisih'];
                                                        $rekSeimbang = abs($selRek) < 0.01;
                                                        $revisi = $line->revisions->last();
                                                    @endphp
                                                    <div class="relative flex flex-wrap items-center gap-x-4 gap-y-1 px-3 py-2">
                                                        <span class="absolute -left-4 top-1/2 w-4 border-t border-dashed border-slate-200" aria-hidden="true"></span>

                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-xs font-bold text-slate-700 truncate">
                                                                <span class="font-mono text-emerald-700">{{ $line->account?->kode ?? '-' }}</span>
                                                                <span class="text-slate-300 mx-1">&middot;</span>{{ $line->account?->nama ?? 'Rekening terhapus' }}
                                                            </p>
                                                            <p class="text-[10.5px] font-semibold text-slate-400 mt-0.5">
                                                                @if($revisi){{ $revisi->label }}<span class="text-slate-300 mx-1">&middot;</span>@endif
                                                                {{ $b['jumlahPaket'] }} paket
                                                            </p>
                                                        </div>

                                                        @foreach($kolomTahap as $k)
                                                            @php $t = $b['tahap'][$k['kunci']] ?? ['nilai' => null, 'eksplisit' => false]; @endphp
                                                            <div class="shrink-0 text-right" style="width: {{ $lebarKolom }}px">
                                                                @if($t['nilai'] === null)
                                                                    <span class="text-[11px] text-slate-300">&mdash;</span>
                                                                @else
                                                                    <span class="text-xs whitespace-nowrap {{ $t['eksplisit'] ? 'font-bold text-slate-800' : 'text-slate-400' }}">
                                                                        {{ $rupiah($t['nilai']) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endforeach

                                                        <div class="shrink-0 text-right" style="width: {{ $lebarKolom }}px">
                                                            <span class="text-xs font-semibold text-blue-600 whitespace-nowrap">{{ $rupiah($b['terinput']) }}</span>
                                                        </div>

                                                        <span class="text-[10.5px] font-bold whitespace-nowrap shrink-0 w-[104px] text-right {{ $rekSeimbang ? 'text-emerald-600' : ($selRek > 0 ? 'text-amber-600' : 'text-rose-600') }}">
                                                            {{ $rekSeimbang ? 'Sesuai' : ($selRek > 0 ? '+' : '') . $rupiah($selRek) }}
                                                        </span>
                                                    </div>
                                                @empty
                                                    <p class="px-3 py-2 text-[11px] font-semibold text-slate-400">Belum ada rekening belanja pada sub kegiatan ini.</p>
                                                @endforelse

                                                <div class="px-3 pt-2">
                                                    <a href="{{ route('admin.anggaran.sub-kegiatan', [$subActivity, 'tahun' => $tahunId]) }}"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-bold text-emerald-700 bg-white border border-emerald-200 rounded-lg hover:bg-emerald-50 transition-colors">
                                                        <i data-lucide="pencil" class="w-3 h-3"></i>
                                                        {{ $rekening->isEmpty() ? 'Isi Rekening & Plafon' : 'Kelola Plafon & Revisi' }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="flex items-center gap-2 px-3 py-3 text-xs text-slate-400 font-semibold">
                            <i data-lucide="briefcase" class="w-3.5 h-3.5 shrink-0"></i>
                            Belum ada kegiatan pada program ini.
                            <a href="{{ route('admin.activities.index') }}" class="text-emerald-600 hover:text-emerald-700 font-bold">Kelola &rarr;</a>
                        </div>
                    @endforelse
                </div>
            </section>
        @empty
            <x-ui.card padding="md">
                <x-ui.empty-state icon="wallet" title="Belum Ada Program"
                    description="Mulai bangun struktur DPA dengan menambahkan program terlebih dahulu.">
                    <x-ui.button variant="primary" size="md" type="button" onclick="bukaModal('modalProgram')">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Tambah Program
                    </x-ui.button>
                </x-ui.empty-state>
            </x-ui.card>
        @endforelse
      </div>
    </div>

    {{-- ── Modal: tambah Program ───────────────────────────── --}}
    <div id="modalProgram" class="anggaran-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupModal('modalProgram')"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
            <form action="{{ route('admin.anggaran.program.store') }}" method="POST">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 bg-blue-50/60 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800">Tambah Program</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tingkat teratas struktur DPA.</p>
                    </div>
                    <button type="button" onclick="tutupModal('modalProgram')" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg font-black">✕</button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Program <span class="text-rose-500">*</span></label>
                        <x-ui.input type="text" name="kode" :value="old('kode')" placeholder="mis. 2.11.11" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Program <span class="text-rose-500">*</span></label>
                        <x-ui.input type="text" name="nama" :value="old('nama')" placeholder="mis. Pengelolaan Persampahan" required />
                    </div>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="tutupModal('modalProgram')" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                    <x-ui.button variant="primary" size="md" type="submit">Simpan Program</x-ui.button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal: tambah Kegiatan (induk diisi JS) ─────────── --}}
    <div id="modalKegiatan" class="anggaran-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupModal('modalKegiatan')"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
            <form action="{{ route('admin.anggaran.kegiatan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="program_id" data-field="program_id">
                <div class="px-5 py-4 border-b border-slate-100 bg-blue-50/60 flex items-center justify-between">
                    <div class="min-w-0">
                        <h3 class="font-bold text-slate-800">Tambah Kegiatan</h3>
                        <p class="text-xs text-slate-500 mt-0.5 truncate" data-field="induk"></p>
                    </div>
                    <button type="button" onclick="tutupModal('modalKegiatan')" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg font-black shrink-0">✕</button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Kegiatan <span class="text-rose-500">*</span></label>
                        <x-ui.input type="text" name="kode" placeholder="mis. 2.11.11.2.01" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Kegiatan <span class="text-rose-500">*</span></label>
                        <x-ui.input type="text" name="nama" placeholder="Nama kegiatan" required />
                    </div>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="tutupModal('modalKegiatan')" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                    <x-ui.button variant="primary" size="md" type="submit">Simpan Kegiatan</x-ui.button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Modal: tambah Sub Kegiatan ──────────────────────── --}}
    <div id="modalSubKegiatan" class="anggaran-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="tutupModal('modalSubKegiatan')"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
            <form action="{{ route('admin.anggaran.sub-kegiatan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="activity_id" data-field="activity_id">
                <input type="hidden" name="tahun" value="{{ $tahunId }}">
                <div class="px-5 py-4 border-b border-slate-100 bg-emerald-50/60 flex items-center justify-between">
                    <div class="min-w-0">
                        <h3 class="font-bold text-slate-800">Tambah Sub Kegiatan</h3>
                        <p class="text-xs text-slate-500 mt-0.5 truncate" data-field="induk"></p>
                    </div>
                    <button type="button" onclick="tutupModal('modalSubKegiatan')" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg font-black shrink-0">✕</button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Sub Kegiatan <span class="text-rose-500">*</span></label>
                        <x-ui.input type="text" name="kode" placeholder="mis. 2.11.11.2.01.0009" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Sub Kegiatan <span class="text-rose-500">*</span></label>
                        <x-ui.input type="text" name="nama" placeholder="Nama sub kegiatan" required />
                    </div>
                    <p class="text-[11px] text-slate-400">Setelah disimpan, Anda langsung dibawa ke halaman pengisian rekening &amp; plafonnya.</p>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="tutupModal('modalSubKegiatan')" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                    <x-ui.button variant="primary" size="md" type="submit">Simpan Sub Kegiatan</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</x-ui.workspace>

<script>
    // Lipat/buka rekening belanja di bawah sub kegiatan.
    function lipatSub(id) {
        const isi = document.getElementById('sub' + id);
        const ikon = document.getElementById('ikonSub' + id);
        if (!isi) return;
        const terbuka = !isi.classList.contains('hidden');
        isi.classList.toggle('hidden', terbuka);
        if (ikon) ikon.style.transform = terbuka ? '' : 'rotate(90deg)';
    }

    // Modal sederhana: induk (program/kegiatan) diisi lewat data-field
    // sehingga satu modal cukup untuk semua kartu.
    function bukaModal(id, data) {
        const modal = document.getElementById(id);
        if (!modal) return;

        Object.entries(data || {}).forEach(function ([key, val]) {
            modal.querySelectorAll('[data-field="' + key + '"]').forEach(function (el) {
                if (el.tagName === 'INPUT') el.value = val;
                else el.textContent = val;
            });
        });

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const first = modal.querySelector('input:not([type="hidden"])');
        if (first) setTimeout(() => first.focus(), 50);
    }

    function tutupModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.anggaran-modal').forEach(m => {
            m.classList.add('hidden');
            m.classList.remove('flex');
        });
    });

    // Buka kembali modal yang gagal validasi agar isian tidak terasa hilang.
    @if($errors->any() && old('kode'))
        @if(old('activity_id'))
            bukaModal('modalSubKegiatan', { activity_id: {{ (int) old('activity_id') }} });
        @elseif(old('program_id'))
            bukaModal('modalKegiatan', { program_id: {{ (int) old('program_id') }} });
        @else
            bukaModal('modalProgram');
        @endif
    @endif
</script>
@endcomponent
