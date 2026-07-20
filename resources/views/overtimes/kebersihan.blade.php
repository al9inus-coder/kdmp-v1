@component('layouts.kdmp')
@section('title', 'Lembur Petugas Kebersihan')

{{--
    Tampilan khusus lembur PETUGAS KEBERSIHAN (mode 'kebersihan').
    Dipakai tiga role lewat flag dari controller:
      $routePrefix : 'staf' | 'kabid' | 'admin'
      $canInput    : boleh upload / hapus kehadiran (staf & admin)
      $canLock     : boleh mengunci data (kabid & admin)
      $canUnlock   : boleh membuka kunci (admin)
      $canChangeMode : boleh mereset mode paket (kabid & admin)
      $backUrl     : tautan kembali sesuai role
    Konsep: kalender ringkas (lencana jumlah, tanpa nama), daftar tanggal lembur,
    rekapitulasi selebar halaman. Perhitungan dari Overtime::rekap().
--}}

@php
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $dowShort = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
    $monthName = $months[$month];
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $money = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $dateStrOf = fn ($d) => sprintf('%04d-%02d-%02d', $year, $month, $d);
    $isLocked = (bool) $overtime->is_locked;
    $bolehInput = $canInput && !$isLocked;

    // ===== Perhitungan terpusat =====
    $rekapData = $overtime->rekap($sbuRates);

    // Kehadiran per tanggal (untuk kalender, daftar tanggal, modal detail, matriks)
    $byDay = [];
    foreach ($rekapData['rows'] as $row) {
        foreach ($row['jamPerHari'] as $d => $jam) {
            $byDay[$d][] = [
                'employee_id' => $row['employee']->id,
                'nama' => $row['employee']->nama,
                'jam' => $jam,
            ];
        }
    }
    ksort($byDay);
    foreach ($byDay as &$list) { usort($list, fn ($a, $b) => strcmp($a['nama'], $b['nama'])); }
    unset($list);
    $filledDays = array_keys($byDay);

    // Libur nasional bulan ini: hari => keterangan
    $holidaysThisMonth = [];
    foreach ($holidaysDataFull as $h) {
        if (str_starts_with($h['date'], sprintf('%04d-%02d', $year, $month))) {
            $holidaysThisMonth[(int) substr($h['date'], 8, 2)] = $h['description'];
        }
    }
    $missingHolidays = array_values(array_diff(array_keys($holidaysThisMonth), $filledDays));

    // Daftar "Tanggal Lembur Bulan Ini": libur (selalu) ∪ tanggal terisi (apapun jenis harinya)
    $tanggalList = [];
    foreach (array_unique(array_merge(array_keys($holidaysThisMonth), $filledDays)) as $d) {
        $dow = (int) date('N', mktime(0, 0, 0, $month, $d, $year));
        $tanggalList[$d] = [
            'day' => $d,
            'dow' => $dow,
            'holiday' => $holidaysThisMonth[$d] ?? null,
            'weekend' => $dow >= 6,
            'count' => isset($byDay[$d]) ? count($byDay[$d]) : 0,
        ];
    }
    ksort($tanggalList);

    // Kalender: kolom mulai Senin (N=1)
    $firstDow = (int) date('N', mktime(0, 0, 0, $month, 1, $year));

    // Data modal detail per tanggal (dipakai JS)
    $attendanceByDay = [];
    foreach ($byDay as $d => $list) { $attendanceByDay[$dateStrOf($d)] = $list; }

    $statPetugas = $overtime->details->count();
@endphp

<div class="space-y-6">
    <x-ui.toast />

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 min-w-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-lg shadow-sm">
                <i data-lucide="hash" class="w-3.5 h-3.5 text-sky-500"></i>{{ $package->id_rup ?? '-' }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-lg">
                <i data-lucide="calendar-clock" class="w-3.5 h-3.5"></i>Lembur {{ $monthName }} {{ $year }}
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-sky-700 bg-sky-50 border border-sky-100 rounded-lg">
                <i data-lucide="spray-can" class="w-3.5 h-3.5"></i>Petugas Kebersihan
            </span>
            @if($isLocked)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>Terkunci
                </span>
            @elseif(!$canInput)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 rounded-lg">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>Baca Saja
                </span>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $backUrl }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali
            </a>
            {{-- Staf membuat SPJ dari halaman daftar /staf/lembur, bukan dari sini. --}}
            @if($routePrefix !== 'staf')
                <button type="button" id="btnSpj"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i>Buat SPJ
                </button>
            @endif
            @if($canLock && !$isLocked)
                <form action="{{ route($routePrefix . '.packages.overtimes.lock', [$package, $month]) }}" method="POST"
                    onsubmit="return confirm('Yakin mengunci data lembur bulan ini? Setelah dikunci tidak bisa diubah.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm">
                        <i data-lucide="lock" class="w-4 h-4"></i>Kunci Data SPJ
                    </button>
                </form>
            @elseif($canUnlock && $isLocked)
                <form action="{{ route($routePrefix . '.packages.overtimes.unlock', [$package, $month]) }}" method="POST"
                    onsubmit="return confirm('Buka kunci data? Data bisa diedit lagi.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-bold text-amber-700 bg-white border border-amber-200 hover:bg-amber-50 rounded-lg">
                        <i data-lucide="unlock" class="w-4 h-4"></i>Buka Kunci (Admin)
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Baris atas: kalender ringkas + panel kanan --}}
    <div class="grid grid-cols-1 xl:grid-cols-[400px_minmax(0,1fr)] gap-5 items-start">

        {{-- Kalender ringkas --}}
        <section id="pageKalender" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-xs font-black text-slate-700 uppercase tracking-wide flex items-center gap-2">
                    <i data-lucide="calendar-days" class="w-4 h-4 text-sky-500"></i>Kalender Kehadiran
                </h2>
                <span class="text-[11px] text-slate-400 font-semibold">
                    {{ $bolehInput ? 'klik tanggal utk detail / upload' : 'klik tanggal terisi utk detail' }}
                </span>
            </div>
            <div class="p-4">
                <div class="flex items-center justify-between mb-3 px-1">
                    <b class="text-sm font-extrabold text-slate-900">{{ $monthName }} {{ $year }}</b>
                    <span class="flex gap-1">
                        @if($month > 1)
                            <a href="{{ route($routePrefix . '.packages.overtimes.show', [$package, $month - 1]) }}"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 font-extrabold">‹</a>
                        @endif
                        @if($month < 12)
                            <a href="{{ route($routePrefix . '.packages.overtimes.show', [$package, $month + 1]) }}"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 font-extrabold">›</a>
                        @endif
                    </span>
                </div>
                <div class="grid grid-cols-7 gap-1">
                    @foreach($dowShort as $n => $label)
                        <span class="text-[10px] font-extrabold text-center py-1 uppercase {{ $n >= 6 ? 'text-rose-300' : 'text-slate-400' }}">{{ $label }}</span>
                    @endforeach

                    @for($i = 1; $i < $firstDow; $i++)
                        <span></span>
                    @endfor

                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $dow = (int) date('N', mktime(0, 0, 0, $month, $d, $year));
                            $isWe = $dow >= 6;
                            $isHol = isset($holidaysThisMonth[$d]);
                            $cnt = isset($byDay[$d]) ? count($byDay[$d]) : 0;
                            $isMiss = $isHol && $cnt === 0;
                            $classes = 'relative h-12 rounded-xl border text-left pl-2 pt-1 transition-colors ';
                            $classes .= $isHol ? 'bg-rose-50 border-rose-200 ' : ($isWe ? 'bg-slate-50 border-slate-100 ' : 'bg-white border-slate-100 ');
                            if ($isMiss) $classes .= 'border-dashed !border-amber-400 ';
                            $klikable = $cnt > 0 || $bolehInput;
                            $classes .= $klikable ? 'cursor-pointer hover:border-sky-300 hover:bg-sky-50 ' : 'cursor-default ';
                        @endphp
                        <button type="button" class="{{ $classes }}"
                            @if($isHol) title="{{ $holidaysThisMonth[$d] }}" @endif
                            @if($cnt > 0)
                                onclick="openDetail('{{ $dateStrOf($d) }}')"
                            @elseif($bolehInput)
                                onclick="openUpload('{{ $dateStrOf($d) }}')"
                            @endif>
                            <span class="text-xs font-bold {{ $isHol ? 'text-rose-600' : 'text-slate-600' }}">{{ $d }}</span>
                            @if($cnt > 0)
                                <span class="absolute right-1 bottom-1 text-[10px] font-extrabold px-1.5 py-px rounded-full bg-sky-100 text-sky-700 border border-sky-200">{{ $cnt }}</span>
                            @elseif($isMiss)
                                <span class="absolute right-1 bottom-1 w-4 h-4 rounded-full bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-black flex items-center justify-center">!</span>
                            @endif
                        </button>
                    @endfor
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-1.5 pt-3 px-1 text-[10.5px] font-semibold text-slate-400">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-sky-100 border border-sky-200 inline-block"></span>terisi (angka = jml petugas)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-rose-50 border border-rose-200 inline-block"></span>libur nasional</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-white border border-dashed border-amber-400 inline-block"></span>libur belum diisi</span>
                </div>
            </div>
        </section>

        {{-- Panel kanan --}}
        <div class="space-y-5 min-w-0">
            {{-- Statistik --}}
            <div id="pageStats" class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Petugas Terdaftar</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $statPetugas }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">dari upload kehadiran</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Hari Terisi</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ count($filledDays) }}</p>
                    @if(count($missingHolidays) > 0)
                        <p class="text-[11px] font-semibold text-amber-600 mt-0.5">{{ count($missingHolidays) }} libur belum diisi</p>
                    @else
                        <p class="text-[11px] font-semibold text-slate-400 mt-0.5">bulan ini</p>
                    @endif
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Jam</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $rekapData['totalJam'] }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">jam lembur</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-2xl p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Estimasi Dibayar</p>
                    <p class="text-xl font-extrabold text-emerald-600 mt-1">{{ $money($rekapData['totalDiterima']) }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 mt-0.5">termasuk potongan pajak</p>
                </div>
            </div>

            {{-- Daftar tanggal lembur --}}
            <section id="pageTanggal" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-xs font-black text-slate-700 uppercase tracking-wide flex items-center gap-2">
                        <i data-lucide="list-checks" class="w-4 h-4 text-sky-500"></i>Tanggal Lembur Bulan Ini
                    </h2>
                    <span class="text-[11px] text-slate-400 font-semibold">libur tampil duluan · hari kerja muncul saat diisi</span>
                </div>
                <div class="p-2.5 space-y-1.5 max-h-72 overflow-y-auto">
                    @forelse($tanggalList as $t)
                        <div class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl border {{ $t['holiday'] && $t['count'] === 0 ? 'border-amber-200 bg-amber-50/40' : 'border-slate-100' }}">
                            <span class="text-xs font-extrabold min-w-[52px] {{ $t['holiday'] ? 'text-rose-600' : ($t['weekend'] ? 'text-slate-500' : 'text-sky-700') }}">
                                {{ $dowShort[$t['dow']] }}, {{ $t['day'] }}
                            </span>
                            <span class="flex-1 min-w-0 truncate text-xs font-semibold text-slate-600">
                                {{ $t['holiday'] ?? ($t['weekend'] ? 'Akhir pekan' : 'Hari kerja — lembur insidental') }}
                            </span>
                            @if($t['count'] > 0)
                                <span class="text-[10.5px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 whitespace-nowrap">✓ {{ $t['count'] }} petugas</span>
                                <button type="button" onclick="openDetail('{{ $dateStrOf($t['day']) }}')"
                                    class="text-[11px] font-extrabold px-2.5 py-1.5 rounded-lg text-sky-700 bg-white border border-sky-200 hover:bg-sky-50">Lihat</button>
                            @else
                                <span class="text-[10.5px] font-extrabold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 whitespace-nowrap">! belum diisi</span>
                                @if($bolehInput)
                                    <button type="button" onclick="openUpload('{{ $dateStrOf($t['day']) }}')"
                                        class="text-[11px] font-extrabold px-2.5 py-1.5 rounded-lg text-white bg-sky-600 hover:bg-sky-700">Upload</button>
                                @endif
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-xs text-slate-400 py-5">Belum ada tanggal lembur maupun hari libur pada bulan ini.</p>
                    @endforelse

                    @if($bolehInput)
                        <button type="button" onclick="openUpload(null)"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl border border-dashed border-sky-200 bg-sky-50/30 hover:bg-sky-50 text-left">
                            <span class="text-sky-600 font-black">＋</span>
                            <span class="text-xs font-bold text-sky-700">Tambah tanggal lembur lain (hari kerja mana pun)</span>
                        </button>
                    @endif
                </div>
            </section>
        </div>
    </div>

    {{-- Rekapitulasi full-width --}}
    <section id="pageRekap" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center gap-3">
            <h2 class="text-xs font-black text-slate-700 uppercase tracking-wide flex items-center gap-2 mr-auto">
                <i data-lucide="calculator" class="w-4 h-4 text-emerald-500"></i>Rekapitulasi &amp; Perhitungan
            </h2>
            <div class="relative w-64 max-w-full">
                <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" id="cariNama" placeholder="Cari nama petugas…"
                    class="w-full pl-8 pr-3 py-2 text-xs font-semibold rounded-lg border-slate-200 bg-white focus:border-sky-500 focus:ring-sky-500">
            </div>
            <span class="text-sm font-black text-emerald-700 whitespace-nowrap">{{ $money($rekapData['totalDiterima']) }}</span>
        </div>
        <div class="overflow-auto max-h-[480px]">
            <table class="min-w-full text-xs" id="tabelRekap">
                <thead>
                    <tr>
                        <th class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 px-3 py-2.5 text-center w-10 text-[10px] font-extrabold uppercase tracking-wide text-slate-500">No</th>
                        <th class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 px-3 py-2.5 text-left text-[10px] font-extrabold uppercase tracking-wide text-slate-500">Nama Petugas</th>
                        @foreach($filledDays as $d)
                            <th class="sticky top-0 z-10 bg-sky-50 border-b border-slate-200 px-3 py-2.5 text-center text-[10px] font-extrabold uppercase tracking-wide text-sky-700 whitespace-nowrap">
                                {{ $dowShort[(int) date('N', mktime(0, 0, 0, $month, $d, $year))] }} {{ $d }}
                            </th>
                        @endforeach
                        <th class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 px-3 py-2.5 text-center text-[10px] font-extrabold uppercase tracking-wide text-slate-500">Hari</th>
                        <th class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 px-3 py-2.5 text-right text-[10px] font-extrabold uppercase tracking-wide text-slate-500">Jam</th>
                        <th class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 px-3 py-2.5 text-right text-[10px] font-extrabold uppercase tracking-wide text-slate-500">SBU/Jam</th>
                        <th class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 px-3 py-2.5 text-right text-[10px] font-extrabold uppercase tracking-wide text-slate-500">Pajak</th>
                        <th class="sticky top-0 z-10 bg-slate-50 border-b border-slate-200 px-3 py-2.5 text-right text-[10px] font-extrabold uppercase tracking-wide text-slate-500">Diterima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rekapData['rows'] as $i => $row)
                        <tr class="hover:bg-slate-50/60" data-nama="{{ mb_strtolower($row['employee']->nama) }}">
                            <td class="px-3 py-2.5 text-center text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-2.5">
                                <p class="font-bold text-slate-800 whitespace-nowrap">{{ $row['employee']->nama }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold truncate max-w-[220px]">{{ $row['employee']->jabatan ?? 'Petugas Kebersihan' }}</p>
                            </td>
                            @foreach($filledDays as $d)
                                @php $jamD = $row['jamPerHari'][$d] ?? 0; @endphp
                                <td class="px-3 py-2.5 text-center {{ $jamD ? 'font-bold text-sky-700 bg-sky-50/40' : 'text-slate-300' }}">{{ $jamD ?: '·' }}</td>
                            @endforeach
                            <td class="px-3 py-2.5 text-center text-slate-600">{{ $row['hari'] }}</td>
                            <td class="px-3 py-2.5 text-right font-extrabold text-slate-800">{{ $row['totalJam'] }}</td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-600">
                                {{ $money($row['valLembur']) }}
                                @if($canEditSbu && !$isLocked)
                                    <button type="button" class="ml-1 text-indigo-500 hover:text-indigo-700 align-middle"
                                        data-detail-id="{{ $row['detail']->id }}" data-val-lembur="{{ $row['valLembur'] }}" data-val-makan="{{ $row['valMakan'] }}" data-emp-name="{{ $row['employee']->nama }}"
                                        onclick="editSbu(this)" title="Sesuaikan SBU">
                                        <i data-lucide="pencil" class="w-3 h-3 inline-block"></i>
                                    </button>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-500">{{ $row['pajak'] > 0 ? $money($row['pajak']) : '-' }}</td>
                            <td class="px-3 py-2.5 text-right whitespace-nowrap font-extrabold text-emerald-700">{{ $money($row['diterima']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 7 + count($filledDays) }}" class="px-6 py-12 text-center text-slate-400">
                                Belum ada data kehadiran.
                                @if($bolehInput) Klik tanggal di kalender atau tombol Upload untuk mengisi dari file Excel/CSV. @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($rekapData['rows']) > 0)
                    <tfoot>
                        <tr class="font-black">
                            <td colspan="{{ 3 + count($filledDays) }}" class="sticky bottom-0 bg-slate-50 border-t-2 border-slate-200 px-3 py-2.5 text-right text-slate-700">TOTAL</td>
                            <td class="sticky bottom-0 bg-slate-50 border-t-2 border-slate-200 px-3 py-2.5 text-right">{{ $rekapData['totalJam'] }}</td>
                            <td class="sticky bottom-0 bg-slate-50 border-t-2 border-slate-200"></td>
                            <td class="sticky bottom-0 bg-slate-50 border-t-2 border-slate-200 px-3 py-2.5 text-right whitespace-nowrap">{{ $rekapData['totalPajak'] > 0 ? $money($rekapData['totalPajak']) : '-' }}</td>
                            <td class="sticky bottom-0 bg-slate-50 border-t-2 border-slate-200 px-3 py-2.5 text-right whitespace-nowrap text-emerald-700">{{ $money($rekapData['totalDiterima']) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400 font-semibold">
            <span>Jam per tanggal = jam bersih dari file upload · kolom tanggal hanya untuk tanggal yang terisi · minimal 2 jam/hari</span>
            @if($canChangeMode && !$isLocked)
                <form action="{{ route($routePrefix . '.packages.overtimes.reset-mode', [$package, $overtime]) }}" method="POST"
                    onsubmit="return confirm('Ubah mode lembur paket ini? Seluruh roster dan jam lembur pada bulan-bulan yang belum dikunci akan dihapus, lalu mode dipilih ulang.')">
                    @csrf
                    <button type="submit" class="underline underline-offset-2 hover:text-slate-600">⟳ Ubah Mode Lembur</button>
                </form>
            @endif
        </div>
    </section>

    {{-- Modal Detail Kehadiran --}}
    <div id="detailModal" class="kdmp-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="kdmp-modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
            <div class="px-5 py-4 border-b border-slate-100 bg-sky-50/60 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="font-bold text-slate-800">Kehadiran Lembur</h3>
                    <p id="detailDateLabel" class="text-xs text-slate-500 mt-0.5"></p>
                </div>
                <button type="button" data-dismiss="modal" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="px-5 py-2.5 border-b border-slate-100 shrink-0">
                <p id="detailSummary" class="text-xs font-semibold text-slate-500"></p>
            </div>
            <div id="detailList" class="p-3 space-y-1.5 overflow-y-auto"></div>
            @if($bolehInput)
                {{-- Tambah satu petugas yang terlewat dari file upload --}}
                <div class="px-3 pb-3 shrink-0">
                    <div class="flex items-center gap-2 p-2 rounded-xl border border-dashed border-sky-200 bg-sky-50/40">
                        <input type="text" id="tambahNama" list="namaPetugasList" placeholder="Nama petugas yang terlewat…"
                            class="flex-1 min-w-0 rounded-lg border-slate-200 text-sm py-1.5 focus:border-sky-500 focus:ring-sky-500">
                        <input type="number" id="tambahJam" min="2" max="24" value="2" title="Jam lembur (2–24)"
                            class="w-16 rounded-lg border-slate-200 text-sm py-1.5 text-center focus:border-sky-500 focus:ring-sky-500">
                        <button type="button" id="btnTambahPetugas"
                            class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-lg disabled:opacity-50">
                            ＋ Tambah
                        </button>
                    </div>
                </div>
            @endif
            <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-between gap-2 shrink-0">
                <button type="button" data-dismiss="modal" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Tutup</button>
                @if($bolehInput)
                    <button type="button" id="btnReupload" class="px-4 py-2 text-sm font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm inline-flex items-center gap-1.5">
                        <i data-lucide="upload" class="w-4 h-4"></i>Upload ulang tanggal ini
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($bolehInput)
    {{-- Modal Upload Kehadiran --}}
    <div id="uploadModal" class="kdmp-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="kdmp-modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
            <form method="POST" action="{{ route($routePrefix . '.packages.overtimes.import-attendance', [$package, $overtime]) }}" enctype="multipart/form-data">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 bg-sky-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Upload Kehadiran Lembur</h3>
                    <button type="button" data-dismiss="modal" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Lembur</label>
                        <input type="date" name="date" id="uploadDate" required
                            min="{{ $dateStrOf(1) }}" max="{{ $dateStrOf($daysInMonth) }}"
                            class="w-full rounded-lg border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                        <p class="text-[11px] text-slate-400 mt-1">Harus dalam bulan {{ $monthName }} {{ $year }}.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">File Kehadiran</label>
                        <input type="file" name="file" required accept=".xlsx,.csv"
                            class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    </div>
                    <div class="rounded-lg bg-slate-50 border border-slate-100 p-3 text-[11px] text-slate-500 leading-relaxed">
                        <p class="font-bold text-slate-600 mb-1">Format file (Excel / CSV):</p>
                        <p>Kolom <b>Nama Pegawai</b> dan <b>Jam</b> wajib; <b>NIP</b> &amp; <b>Jabatan</b> opsional. Kolom lain (No., TK, TAP, TL) diabaikan.</p>
                        <p class="mt-1"><b>Jam</b> = jam lembur <b>bersih</b>. Kosong / 0 = tidak lembur; di bawah 2 jam tidak dicatat.</p>
                        <p class="mt-1">Upload ulang untuk tanggal yang sama akan <b>mengganti</b> data tanggal itu.</p>
                        <a href="{{ route($routePrefix . '.packages.overtimes.import-template', [$package, $overtime]) }}" class="inline-flex items-center gap-1 mt-2 font-semibold text-sky-600 hover:text-sky-700">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>Unduh template Excel (.xlsx)
                        </a>
                    </div>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" data-dismiss="modal" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-sky-600 hover:bg-sky-700 rounded-lg shadow-sm">Proses Upload</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if($canEditSbu && !$isLocked)
    {{-- Modal Edit SBU --}}
    <div id="sbuModal" class="kdmp-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="kdmp-modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden">
            <form id="formEditSbu" method="POST" action="">
                @csrf
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800">Penyesuaian SBU</h3>
                    <button type="button" data-dismiss="modal" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
                <div class="p-5 space-y-3">
                    <p id="sbuEmployeeName" class="font-bold text-slate-800"></p>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tarif Uang Lembur (Rp)</label>
                        <input type="number" name="rate_lembur_fix" id="inputRateLembur" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tarif Uang Makan (Rp)</label>
                        <input type="number" name="rate_makan_fix" id="inputRateMakan" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <p class="text-[11px] text-slate-400">Kosongkan untuk memakai tarif bawaan master SBU.</p>
                </div>
                <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" data-dismiss="modal" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    {{-- Modal Buat SPJ (periode rentang bulan; hanya bulan terkunci) — kabid/admin --}}
    @if($routePrefix !== 'staf')
    <div id="spjModal" class="kdmp-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
            @php
                $defaultDari = count($lockedMonths) > 0 ? min($lockedMonths) : (int) $month;
                $defaultSampai = count($lockedMonths) > 0 ? max($lockedMonths) : (int) $month;
            @endphp
            <div class="px-5 py-4 border-b border-slate-100 bg-emerald-50/60 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800">Buat SPJ Lembur</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih periode, lalu cetak dokumennya satu per satu.</p>
                </div>
                <button type="button" onclick="tutupSpj()" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Bulan</label>
                        <select id="spjDari" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($months as $num => $nama)
                                <option value="{{ $num }}" @disabled(!in_array($num, $lockedMonths)) @selected($num === $defaultDari)>
                                    {{ $nama }}{{ in_array($num, $lockedMonths) ? '' : ' — belum dikunci' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Bulan</label>
                        <select id="spjSampai" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach($months as $num => $nama)
                                <option value="{{ $num }}" @disabled(!in_array($num, $lockedMonths)) @selected($num === $defaultSampai)>
                                    {{ $nama }}{{ in_array($num, $lockedMonths) ? '' : ' — belum dikunci' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dibuat Oleh (Pegawai Dinas)</label>
                    <select id="spjPembuatId" class="w-full rounded-lg border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">-- Pilih Pegawai Pembuat --</option>
                        @foreach($dinasEmployees ?? [] as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->nama }} {{ $emp->jabatan ? '('.$emp->jabatan.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rounded-lg bg-slate-50 border border-slate-100 p-3 text-[11px] text-slate-500 leading-relaxed">
                    <p>Hanya bulan yang <b>sudah dikunci</b> yang dapat masuk SPJ — angka dokumen dijamin sama dengan sistem. Seluruh bulan dalam rentang harus terkunci.</p>
                    <p class="mt-1">Rekap jam lembur, tanda terima &amp; kwitansi berisi <b>total gabungan periode</b>.</p>
                </div>
                @if(count($lockedMonths) === 0)
                    <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-[11px] font-semibold text-amber-700">
                        Belum ada bulan yang dikunci pada paket ini — kunci bulan terlebih dulu untuk membuat SPJ.
                    </div>
                @endif
                <div class="space-y-2">
                    <button type="button" onclick="cetakSpj('rekap')" @disabled(count($lockedMonths) === 0)
                        class="w-full inline-flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed">
                        <i data-lucide="table" class="w-4 h-4 text-blue-500"></i>Rekap Jam Lembur <span class="ml-auto text-[10px] text-slate-400 font-bold">gabungan periode</span>
                    </button>
                    <button type="button" onclick="cetakSpj('tanda_terima')" @disabled(count($lockedMonths) === 0)
                        class="w-full inline-flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed">
                        <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-500"></i>Tanda Terima <span class="ml-auto text-[10px] text-slate-400 font-bold">gabungan periode</span>
                    </button>
                    <button type="button" onclick="cetakSpj('kwitansi')" @disabled(count($lockedMonths) === 0)
                        class="w-full inline-flex items-center gap-2 px-3 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg disabled:opacity-40 disabled:cursor-not-allowed">
                        <i data-lucide="receipt" class="w-4 h-4 text-slate-500"></i>Kwitansi <span class="ml-auto text-[10px] text-slate-400 font-bold">total periode</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Data kehadiran per tanggal: dalam tag JSON agar bisa ikut diperbarui
         saat partial refresh (bukan konstanta yang beku sejak halaman dimuat). --}}
    <script type="application/json" id="attendanceData">@json($attendanceByDay)</script>

    {{-- Saran nama utk form "Tambah Petugas" (ikut di-swap agar nama baru muncul). --}}
    <datalist id="namaPetugasList">
        @foreach(\App\Models\Employee::where('tipe', \App\Models\Employee::TIPE_KEBERSIHAN)->orderBy('nama')->pluck('nama') as $nm)
            <option value="{{ $nm }}"></option>
        @endforeach
    </datalist>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Shim modal ala Bootstrap (pola yang sama dengan halaman lembur dinas).
    jQuery.fn.modal = function (action) {
        return this.each(function () {
            if (action === 'show') { this.classList.remove('hidden'); this.classList.add('flex'); }
            else { this.classList.add('hidden'); this.classList.remove('flex'); }
        });
    };
    jQuery(document).on('click', '[data-dismiss="modal"], .kdmp-modal-backdrop', function () {
        jQuery(this).closest('.kdmp-modal').modal('hide');
    });

    let ATTENDANCE_BY_DAY = JSON.parse(document.getElementById('attendanceData').textContent);
    const CAN_INPUT = {{ $bolehInput ? 'true' : 'false' }};
    const AJAX_URL = "{{ route($routePrefix . '.packages.overtimes.updateAjax', [$package, $overtime]) }}";
    const ADD_URL = "{{ route($routePrefix . '.packages.overtimes.add-attendance', [$package, $overtime]) }}";
    const HOLIDAY_DATES = @json(array_map($dateStrOf, array_keys($holidaysThisMonth)));
    const CSRF_TOKEN = "{{ csrf_token() }}";

    // Jam bawaan mengikuti aturan isi-otomatis: 5 utk akhir pekan/libur, 2 hari kerja.
    function defaultJam(dateStr) {
        const dow = new Date(dateStr + 'T00:00:00').getDay();
        return (dow === 0 || dow === 6 || HOLIDAY_DATES.includes(dateStr)) ? 5 : 2;
    }

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2200 });

    function labelTanggal(dateStr) {
        return new Date(dateStr + 'T00:00:00')
            .toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }

    function kirimAjax(payload) {
        return fetch(AJAX_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        }).then(res => res.json().catch(() => ({ success: false, message: 'Respons server tidak valid.' })));
    }

    // ===== Partial refresh: perbarui kalender/stats/daftar/rekap tanpa reload =====
    let refreshTimer = null;
    let refreshBusy = false;
    let refreshQueued = false;

    function refreshPage() {
        // Debounce: perubahan beruntun (klik stepper cepat) cukup satu fetch.
        clearTimeout(refreshTimer);
        refreshTimer = setTimeout(doRefresh, 400);
    }

    function doRefresh() {
        if (refreshBusy) { refreshQueued = true; return; }
        refreshBusy = true;

        fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(function (html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const cari = document.getElementById('cariNama')?.value || '';

                ['pageStats', 'pageKalender', 'pageTanggal', 'pageRekap', 'attendanceData', 'namaPetugasList'].forEach(function (id) {
                    const fresh = doc.getElementById(id);
                    const current = document.getElementById(id);
                    if (fresh && current) current.replaceWith(fresh);
                });

                ATTENDANCE_BY_DAY = JSON.parse(document.getElementById('attendanceData').textContent);

                // Pulihkan kata pencarian + terapkan ulang filternya.
                const inputCari = document.getElementById('cariNama');
                if (inputCari && cari) { inputCari.value = cari; filterNama(cari); }

                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(function () { /* biarkan; data server sudah benar, tampilan menyusul saat interaksi berikutnya */ })
            .finally(function () {
                refreshBusy = false;
                if (refreshQueued) { refreshQueued = false; doRefresh(); }
            });
    }

    // ===== Modal detail =====
    function ringkasanDetail(dateStr) {
        const list = ATTENDANCE_BY_DAY[dateStr] || [];
        const totalJam = list.reduce((a, b) => a + b.jam, 0);
        $('#detailSummary').text(list.length + ' petugas · total ' + totalJam + ' jam');
    }

    function buatBarisDetail(p, i, dateStr) {
        const $row = $('<div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-100 hover:bg-slate-50" data-employee-id="' + p.employee_id + '"></div>');
        $row.append('<span class="baris-no w-6 text-xs text-slate-400 shrink-0">' + (i + 1) + '</span>');
        $row.append($('<span class="flex-1 min-w-0 text-sm font-semibold text-slate-800 truncate"></span>').text(p.nama));

        if (CAN_INPUT) {
            const $stepper = $('<span class="flex items-center gap-1 shrink-0"></span>');
            const $minus = $('<button type="button" class="w-6 h-6 rounded-md border border-slate-200 text-slate-500 font-black text-sm leading-none hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed" title="Kurangi 1 jam (min. 2)">−</button>');
            const $jam = $('<span class="baris-jam min-w-[52px] text-center text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-md px-2 py-1">' + p.jam + ' Jam</span>');
            const $plus = $('<button type="button" class="w-6 h-6 rounded-md border border-slate-200 text-slate-500 font-black text-sm leading-none hover:bg-slate-100 disabled:opacity-30 disabled:cursor-not-allowed" title="Tambah 1 jam (maks. 24)">+</button>');

            const setDisabled = function (jam) {
                $minus.prop('disabled', jam <= 2);
                $plus.prop('disabled', jam >= 24);
            };
            setDisabled(p.jam);

            const ubah = function (delta) {
                const entry = (ATTENDANCE_BY_DAY[dateStr] || []).find(e => e.employee_id === p.employee_id);
                if (!entry) return;
                const target = entry.jam + delta;
                if (target < 2 || target > 24) return;

                $minus.prop('disabled', true); $plus.prop('disabled', true);
                kirimAjax({ employee_id: p.employee_id, date: dateStr, hours: target, use_uang_makan: false, action: 'update' })
                    .then(function (res) {
                        if (res.success) {
                            entry.jam = target;
                            $jam.text(target + ' Jam');
                            setDisabled(target);
                            ringkasanDetail(dateStr);
                            Toast.fire({ icon: 'success', title: p.nama.split(' ')[0] + ' → ' + target + ' jam' });
                            refreshPage();
                        } else {
                            setDisabled(entry.jam);
                            Swal.fire('Gagal', res.message || 'Perubahan tidak tersimpan.', 'error');
                        }
                    })
                    .catch(function () {
                        setDisabled(entry.jam);
                        Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error');
                    });
            };

            $minus.on('click', function () { ubah(-1); });
            $plus.on('click', function () { ubah(1); });
            $stepper.append($minus, $jam, $plus);
            $row.append($stepper);

            const $del = $('<button type="button" class="p-1 ml-0.5 text-slate-300 hover:text-rose-600 shrink-0 font-black" title="Hapus kehadiran">✕</button>');
            $del.on('click', function () { hapusKehadiran(p.employee_id, dateStr, p.nama, $row); });
            $row.append($del);
        } else {
            $row.append('<span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-md px-2 py-0.5 shrink-0">' + p.jam + ' Jam</span>');
        }

        return $row;
    }

    function openDetail(dateStr) {
        const list = ATTENDANCE_BY_DAY[dateStr] || [];

        $('#detailDateLabel').text(labelTanggal(dateStr));
        ringkasanDetail(dateStr);

        const $list = $('#detailList').empty();
        if (list.length === 0) {
            $list.append('<p class="text-center text-sm text-slate-400 py-6">Belum ada kehadiran pada tanggal ini.</p>');
        } else {
            list.forEach(function (p, i) { $list.append(buatBarisDetail(p, i, dateStr)); });
        }

        window.tanggalDetailAktif = dateStr;
        $('#tambahNama').val('');
        $('#tambahJam').val(defaultJam(dateStr));
        $('#detailModal').modal('show');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Tambah satu petugas yang terlewat dari file (tanpa upload ulang).
    function tambahPetugas() {
        const dateStr = window.tanggalDetailAktif;
        const nama = ($('#tambahNama').val() || '').trim();
        const jam = parseInt($('#tambahJam').val(), 10);

        if (nama === '') { Swal.fire('Perhatian', 'Isi nama petugas terlebih dulu.', 'warning'); return; }
        if (!(jam >= 2 && jam <= 24)) { Swal.fire('Perhatian', 'Jam lembur minimal 2 dan maksimal 24.', 'warning'); return; }

        const $btn = $('#btnTambahPetugas').prop('disabled', true);
        fetch(ADD_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            body: JSON.stringify({ date: dateStr, nama: nama, jam: jam }),
        }).then(res => res.json().catch(() => ({ success: false, message: 'Respons server tidak valid.' })))
            .then(function (res) {
                if (!res.success) { Swal.fire('Gagal', res.message || 'Petugas tidak tersimpan.', 'error'); return; }

                const list = ATTENDANCE_BY_DAY[dateStr] = ATTENDANCE_BY_DAY[dateStr] || [];
                const entry = list.find(e => e.employee_id === res.employee.id);
                if (entry) { entry.jam = res.jam; }
                else {
                    list.push({ employee_id: res.employee.id, nama: res.employee.nama, jam: res.jam });
                    list.sort((a, b) => a.nama.localeCompare(b.nama));
                }

                openDetail(dateStr); // render ulang daftar; modal tetap terbuka
                Toast.fire({ icon: 'success', title: res.updated
                    ? res.employee.nama + ' sudah tercatat — jam diperbarui ke ' + res.jam
                    : (res.created ? res.employee.nama + ' didaftarkan & ditambahkan' : res.employee.nama + ' ditambahkan') });
                refreshPage();
            })
            .catch(function () { Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'); })
            .finally(function () { $btn.prop('disabled', false); });
    }
    $(document).on('click', '#btnTambahPetugas', tambahPetugas);
    $(document).on('keydown', '#tambahNama, #tambahJam', function (e) { if (e.key === 'Enter') { e.preventDefault(); tambahPetugas(); } });

    // ===== SPJ periode =====
    const SPJ_URL = "{{ route($routePrefix . '.packages.overtimes.spj', [$package, ':type']) }}";

    function bukaSpj() {
        $('#spjModal').removeClass('hidden').addClass('flex');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    function tutupSpj() {
        $('#spjModal').addClass('hidden').removeClass('flex');
    }
    $(document).on('click', '#btnSpj', function () {
        bukaSpj();
    });

    function cetakSpj(type) {
        const dari = parseInt($('#spjDari').val(), 10);
        const sampai = parseInt($('#spjSampai').val(), 10);
        const pembuatId = $('#spjPembuatId').val() || '';
        if (!(dari >= 1) || !(sampai >= 1)) { Swal.fire('Perhatian', 'Pilih periode bulan terlebih dulu.', 'warning'); return; }
        if (sampai < dari) { Swal.fire('Perhatian', '"Sampai Bulan" tidak boleh mendahului "Dari Bulan".', 'warning'); return; }
        let finalUrl = SPJ_URL.replace(':type', type) + '?dari=' + dari + '&sampai=' + sampai;
        if (pembuatId) {
            finalUrl += '&pembuat_id=' + pembuatId;
        }
        window.open(finalUrl, '_blank');
    }

    function openUpload(dateStr) {
        if (!CAN_INPUT) return;
        $('#uploadDate').val(dateStr || '');
        $('#uploadModal').modal('show');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function hapusKehadiran(employeeId, dateStr, nama, $row) {
        Swal.fire({
            title: 'Hapus kehadiran?',
            text: nama + ' pada ' + labelTanggal(dateStr),
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#e11d48', confirmButtonText: 'Ya, hapus',
        }).then(function (r) {
            if (!r.isConfirmed) return;
            kirimAjax({ employee_id: employeeId, date: dateStr, hours: 0, use_uang_makan: false, action: 'delete' })
                .then(function (res) {
                    if (!res.success) { Swal.fire('Gagal', res.message || 'Gagal menghapus.', 'error'); return; }

                    // Perbarui data lokal + baris modal tanpa menutupnya.
                    ATTENDANCE_BY_DAY[dateStr] = (ATTENDANCE_BY_DAY[dateStr] || []).filter(e => e.employee_id !== employeeId);
                    if ($row) $row.remove();
                    $('#detailList .baris-no').each(function (i) { $(this).text(i + 1); });
                    if ((ATTENDANCE_BY_DAY[dateStr] || []).length === 0) {
                        $('#detailList').html('<p class="text-center text-sm text-slate-400 py-6">Belum ada kehadiran pada tanggal ini.</p>');
                    }
                    ringkasanDetail(dateStr);
                    Toast.fire({ icon: 'success', title: 'Kehadiran dihapus' });
                    refreshPage();
                })
                .catch(function () { Swal.fire('Gagal', 'Tidak dapat terhubung ke server.', 'error'); });
        });
    }

    $('#btnReupload').on('click', function () {
        $('#detailModal').modal('hide');
        openUpload(window.tanggalDetailAktif);
    });

    // Cari nama pada tabel rekap — delegated, agar tetap hidup setelah partial refresh.
    function filterNama(q) {
        q = q.toLowerCase();
        $('#tabelRekap tbody tr[data-nama]').each(function () {
            $(this).toggle(this.dataset.nama.indexOf(q) >= 0);
        });
    }
    $(document).on('input', '#cariNama', function () { filterNama(this.value); });

    @if($canEditSbu && !$isLocked)
    function editSbu(btn) {
        $('#sbuEmployeeName').text($(btn).data('emp-name'));
        $('#inputRateLembur').val($(btn).data('val-lembur'));
        $('#inputRateMakan').val($(btn).data('val-makan'));
        const formUrl = "{{ route($routePrefix . '.packages.overtimes.update_rates', ['package' => $package->id, 'overtime' => $overtime->id, 'detail' => ':detail']) }}";
        $('#formEditSbu').attr('action', formUrl.replace(':detail', $(btn).data('detail-id')));
        $('#sbuModal').modal('show');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    @endif
</script>
@endcomponent
