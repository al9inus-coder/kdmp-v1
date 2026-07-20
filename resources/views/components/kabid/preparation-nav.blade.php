@props(['procurementPackage'])

@php
    $ts = $procurementPackage->technicalSpecification;

    // Status tiap langkah dihitung dari kelengkapan data paket.
    // Navigasi antar panel dikendalikan Alpine `step` milik pembungkus halaman.
    $items = [
        [
            'title' => 'Informasi Kontrak',
            'done'  => filled($procurementPackage->jenis_kontrak) && filled($procurementPackage->jangka_waktu_nilai),
        ],
        [
            'title' => 'Barang / Jasa',
            'done'  => $ts && $ts->items->isNotEmpty(),
        ],
        [
            'title' => 'Spesifikasi Teknis',
            'done'  => $ts && filled($ts->latar_belakang) && filled($ts->uraian_pekerjaan),
        ],
        [
            'title' => 'Referensi Harga',
            'done'  => ($procurementPackage->price_references_count ?? 0) > 0,
        ],
        [
            'title' => 'Surat Permohonan',
            'done'  => (bool) $procurementPackage->procurementRequest,
        ],
        [
            'title' => 'Selesaikan',
            'done'  => $procurementPackage->workflow_status !== \App\Models\ProcurementPackage::WORKFLOW_DRAFT,
        ],
    ];

    // Langkah "berjalan" = langkah pertama yang datanya belum lengkap
    $runningIndex = collect($items)->search(fn($i) => !$i['done']);
    $allDone      = $runningIndex === false;
    $doneCount    = collect($items)->where('done', true)->count();
    $percent      = (int) round($doneCount / count($items) * 100);
@endphp

<nav class="w-full" aria-label="Alur Persiapan">
    {{-- Judul + ringkasan progres --}}
    <div class="flex items-center justify-between mb-1 px-1">
        <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Alur Persiapan</p>
        <span class="text-[11px] font-bold {{ $allDone ? 'text-emerald-600' : 'text-slate-400' }}">
            {{ $doneCount }}/{{ count($items) }}
        </span>
    </div>
    <div class="h-1 rounded-full bg-slate-200/70 overflow-hidden mb-4 mx-1">
        <div class="h-full rounded-full transition-all duration-700 {{ $allDone ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : 'bg-gradient-to-r from-amber-400 to-amber-500' }}"
             style="width: {{ $percent }}%;"></div>
    </div>

    <ol class="relative space-y-0.5">
        @foreach($items as $index => $item)
            @php
                $stepNo  = $index + 1;
                $done    = $item['done'];
                $running = !$allDone && $runningIndex === $index;
            @endphp
            <li class="relative">
                {{-- Garis penghubung vertikal --}}
                @if($index < count($items) - 1)
                    <span class="absolute left-[21px] top-11 h-3 w-px z-0
                        {{ $done ? 'bg-emerald-300' : 'bg-slate-200' }}"></span>
                @endif

                <button type="button" @click="step = {{ $stepNo }}"
                    class="relative w-full flex items-center gap-3 py-2 pl-1.5 pr-3 rounded-xl text-left border transition-all duration-200"
                    :class="step === {{ $stepNo }}
                        ? 'bg-white border-slate-200 shadow-md shadow-slate-200/60 translate-x-1'
                        : 'border-transparent hover:bg-white/70 hover:translate-x-0.5'">

                    {{-- Bulatan status (dari data) --}}
                    <span class="relative shrink-0">
                        @if($running)
                            <span class="absolute inset-0 rounded-full bg-amber-400 opacity-30 animate-ping"></span>
                        @endif
                        <span class="relative flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold transition-all duration-200
                            {{ $done
                                ? 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-sm shadow-emerald-200'
                                : ($running
                                    ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-white shadow-md shadow-amber-200 ring-2 ring-amber-200'
                                    : 'bg-white border-2 border-slate-200 text-slate-400') }}">
                            @if($done)
                                <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                            @else
                                {{ $stepNo }}
                            @endif
                        </span>
                    </span>

                    {{-- Teks --}}
                    <span class="leading-tight min-w-0">
                        <span class="block text-sm truncate transition-colors
                            {{ $done ? 'text-emerald-700 font-semibold' : ($running ? 'text-slate-900 font-bold' : 'text-slate-500 font-medium') }}"
                            :class="step === {{ $stepNo }} ? 'text-slate-900' : ''">
                            {{ $item['title'] }}
                        </span>
                        <span class="block text-[10px] font-bold uppercase tracking-wide
                            {{ $done ? 'text-emerald-500' : ($running ? 'text-amber-600' : 'text-slate-300') }}">
                            @if($done) Lengkap @elseif($running) Berjalan @else Menunggu @endif
                        </span>
                    </span>

                    {{-- Penanda panel yang sedang dibuka --}}
                    <i data-lucide="chevron-left" class="w-4 h-4 ml-auto shrink-0 text-slate-400 transition-opacity"
                       x-show="step === {{ $stepNo }}" style="display: none;"></i>
                </button>
            </li>
        @endforeach
    </ol>

    @if($allDone)
        <div class="mt-3 mx-1 flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100">
            <i data-lucide="party-popper" class="w-4 h-4 text-emerald-500 shrink-0"></i>
            <p class="text-xs font-semibold text-emerald-700">Seluruh tahap persiapan lengkap.</p>
        </div>
    @endif
</nav>
