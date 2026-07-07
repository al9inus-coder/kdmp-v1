@component('layouts.kdmp')
@section('title', 'Kalender Lembur')

@php
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $monthName = $months[$month];
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $firstDayOfMonth = sprintf('%04d-%02d-01', $year, $month);
    $money = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
    $userRole = auth()->user()->getRoleNames()->first() ?? '';
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
            @if($overtime->is_locked)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>Terkunci
                </span>
            @endif
        </div>
        <a href="{{ route('procurement-packages.show', $package) }}"
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shadow-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>Kembali ke Paket
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[320px_minmax(0,1fr)] gap-6 items-start">

        {{-- Kolom kiri --}}
        <div class="space-y-5">
            {{-- Daftar pegawai --}}
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-blue-500"></i>Daftar Pegawai
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Seret nama ke kalender · Hari kerja 2 jam, libur 5 jam.</p>
                </div>
                <div class="p-4 border-b border-slate-100 space-y-2">
                    @if(!$overtime->is_locked)
                        <button type="button" id="btnAutoFill"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm shadow-indigo-200 transition-colors">
                            <i data-lucide="wand-2" class="w-4 h-4"></i>Isi Otomatis 1 Bulan
                        </button>
                        <button type="button" id="btnReset"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-rose-700 bg-white border border-rose-200 hover:bg-rose-50 rounded-lg transition-colors">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>Hapus Semua (Reset)
                        </button>
                    @else
                        <div class="rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold text-center py-2 flex items-center justify-center gap-1.5">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>Data Terkunci
                        </div>
                    @endif
                </div>
                <div class="px-4 py-2.5 border-b border-slate-100 flex items-center gap-2">
                    <input type="checkbox" id="checkAllPegawai" class="rounded text-indigo-600 focus:ring-indigo-500 border-slate-300" {{ $overtime->is_locked ? 'disabled' : '' }}>
                    <label for="checkAllPegawai" class="text-xs font-semibold text-slate-500">Pilih Semua</label>
                </div>
                <div id="external-events" class="p-3 space-y-2 max-h-[46vh] overflow-y-auto">
                    @foreach($overtime->details as $detail)
                        @php $emp = $detail->employee; @endphp
                        <div class="external-event flex items-center gap-2.5 p-2.5 border border-slate-200 rounded-xl bg-white hover:border-indigo-200 hover:bg-indigo-50/40 transition-colors {{ $overtime->is_locked ? '' : 'cursor-grab active:cursor-grabbing' }}"
                            data-employee-id="{{ $emp->id }}" data-employee-name="{{ $emp->nama }}"
                            onclick="let cb = $(this).find('.employee-checkbox'); if(!cb.prop('disabled')) cb.prop('checked', !cb.prop('checked'));">
                            <input type="checkbox" class="employee-checkbox rounded text-indigo-600 focus:ring-indigo-500 border-slate-300" value="{{ $emp->id }}" onclick="event.stopPropagation();" {{ $overtime->is_locked ? 'disabled' : '' }}>
                            <span class="flex-1 min-w-0 text-sm font-bold text-slate-800 truncate">{{ $emp->nama }}</span>
                            <i data-lucide="grip-vertical" class="w-4 h-4 text-slate-300 shrink-0"></i>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Libur nasional --}}
            @if(count($holidaysDataFull) > 0)
                @php $hasHolidayThisMonth = collect($holidaysDataFull)->contains(fn($h) => str_starts_with($h['date'], sprintf('%04d-%02d', $year, $month))); @endphp
                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-xs font-black text-slate-700 flex items-center gap-2 uppercase tracking-wide">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-rose-500"></i>Libur Nasional Bulan Ini
                        </h3>
                    </div>
                    <ul class="p-4 space-y-1.5 text-xs text-slate-500">
                        @foreach($holidaysDataFull as $h)
                            @if(str_starts_with($h['date'], sprintf('%04d-%02d', $year, $month)))
                                <li class="flex gap-1.5"><span class="text-rose-500 font-bold shrink-0">{{ \Carbon\Carbon::parse($h['date'])->translatedFormat('d M') }}</span> {{ $h['description'] }}</li>
                            @endif
                        @endforeach
                        @unless($hasHolidayThisMonth)<li class="text-slate-400">Tidak ada libur nasional.</li>@endunless
                    </ul>
                </section>
            @endif

            {{-- Cetak laporan --}}
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xs font-black text-slate-700 flex items-center gap-2 uppercase tracking-wide">
                        <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>Cetak Laporan
                    </h3>
                </div>
                <div class="p-4 space-y-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Dasar Pelaksanaan</label>
                        <textarea id="dasarPelaksanaan" rows="3" placeholder="1. SK Kepala Dinas...&#10;2. Surat Tugas..."
                            class="w-full rounded-lg border-slate-300 text-xs focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-50"
                            {{ $overtime->is_locked ? 'disabled' : '' }}>{{ $overtime->dasar_pelaksanaan }}</textarea>
                        @if(!$overtime->is_locked)
                            <button id="btnSaveDasar" class="mt-2 w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition-colors">
                                <i data-lucide="save" class="w-3.5 h-3.5"></i>Simpan Dasar Surat
                            </button>
                        @endif
                    </div>
                    <div class="pt-2 border-t border-slate-100 space-y-2">
                        <button onclick="printReport('{{ route('packages.overtimes.print', [$package, $overtime, 'rekap']) }}')" class="w-full inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="table" class="w-4 h-4 text-blue-500"></i>Rekapitulasi
                        </button>
                        <button onclick="printReport('{{ route('packages.overtimes.print', [$package, $overtime, 'tanda_terima']) }}')" class="w-full inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-500"></i>Tanda Terima
                        </button>
                        <button onclick="printReport('{{ route('packages.overtimes.print', [$package, $overtime, 'kwitansi']) }}')" class="w-full inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 rounded-lg transition-colors">
                            <i data-lucide="receipt" class="w-4 h-4 text-slate-500"></i>Kwitansi
                        </button>
                    </div>
                </div>
            </section>

            {{-- Keamanan data --}}
            @if(in_array($userRole, ['Admin', 'Kabid']))
                <section class="bg-white border {{ $overtime->is_locked ? 'border-rose-200' : 'border-amber-200' }} rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-xs font-black text-slate-700 flex items-center gap-2 uppercase tracking-wide">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 {{ $overtime->is_locked ? 'text-rose-500' : 'text-amber-500' }}"></i>Keamanan Data
                        </h3>
                    </div>
                    <div class="p-4">
                        @if(!$overtime->is_locked)
                            <form action="{{ route('packages.overtimes.lock', [$package, $month]) }}" method="POST" onsubmit="return confirm('Yakin mengunci data lembur bulan ini? Setelah dikunci tidak bisa diubah.')">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-sm transition-colors">
                                    <i data-lucide="lock" class="w-4 h-4"></i>Kunci Data SPJ
                                </button>
                            </form>
                        @elseif($userRole === 'Admin')
                            <form action="{{ route('packages.overtimes.unlock', [$package, $month]) }}" method="POST" onsubmit="return confirm('Buka kunci data? Data bisa diedit lagi.')">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-amber-700 bg-white border border-amber-200 hover:bg-amber-50 rounded-lg transition-colors">
                                    <i data-lucide="unlock" class="w-4 h-4"></i>Buka Kunci (Admin)
                                </button>
                            </form>
                        @else
                            <div class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-sm font-bold text-slate-400 bg-slate-100 rounded-lg">
                                <i data-lucide="lock" class="w-4 h-4"></i>Terkunci
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </div>

        {{-- Kolom kanan --}}
        <div class="space-y-5 min-w-0">
            <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">
                <div id="calendar" style="min-height: 600px;"></div>
            </section>

            {{-- Rekap --}}
            @php
                $totalKeseluruhan = 0; $sumUpahLembur = 0; $sumUangMakan = 0; $sumPajak = 0;
                $hasUangMakanBulanIni = false; $processedDetails = [];
                foreach($overtime->details as $detail) {
                    $emp = $detail->employee;
                    $golongan = $detail->golongan_fix ?? $emp->golongan ?? 'P3K Paruh Waktu';
                    $totalJam = 0; $daysWithOvertime = 0;
                    for($d = 1; $d <= $daysInMonth; $d++) {
                        $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                        if($val >= 2) { $totalJam += $val; $daysWithOvertime++; }
                    }
                    if($totalJam == 0) continue;
                    $empGol = strtoupper($golongan); $mappedGolongan = 'P3K Paruh Waktu';
                    if (str_contains($empGol, 'IV-') || str_contains($empGol, '/IV') || str_contains($empGol, 'GOLONGAN IV')) $mappedGolongan = 'Golongan IV';
                    elseif (str_contains($empGol, 'III-') || str_contains($empGol, '/III') || str_contains($empGol, 'GOLONGAN III')) $mappedGolongan = 'Golongan III';
                    elseif (str_contains($empGol, 'II-') || str_contains($empGol, '/II') || str_contains($empGol, 'GOLONGAN II') || str_contains($empGol, 'VII')) $mappedGolongan = 'Golongan II';
                    elseif (str_contains($empGol, 'I-') || str_contains($empGol, '/I') || str_contains($empGol, 'GOLONGAN I')) $mappedGolongan = 'Golongan I';
                    if (!is_null($detail->rate_lembur_fix)) $valLembur = $detail->rate_lembur_fix;
                    else { $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->where('golongan', $mappedGolongan)->first(); if(!$rateLembur) $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->sortBy('besaran')->first(); $valLembur = $rateLembur ? $rateLembur->besaran : 0; }
                    if (!is_null($detail->rate_makan_fix)) $valMakan = $detail->rate_makan_fix;
                    else {
                        $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->where('golongan', $mappedGolongan)->first();
                        if(!$rateMakan && (str_contains($mappedGolongan, 'II') || str_contains($mappedGolongan, 'I'))) $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->where('golongan', 'Golongan II dan Golongan I')->first();
                        if(!$rateMakan) $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->sortBy('besaran')->first();
                        $valMakan = $rateMakan ? $rateMakan->besaran : 0;
                    }
                    $uangLembur = $totalJam * $valLembur;
                    $uangMakan = $detail->use_uang_makan ? ($daysWithOvertime * $valMakan) : 0;
                    if($uangMakan > 0) $hasUangMakanBulanIni = true;
                    $pphRate = 0;
                    if (str_contains(strtoupper($golongan), 'III')) $pphRate = 0.05;
                    elseif (str_contains(strtoupper($golongan), 'IV')) $pphRate = 0.15;
                    $pph = $uangLembur * $pphRate;
                    $totalDiterima = ($uangLembur - $pph) + $uangMakan;
                    $totalKeseluruhan += $totalDiterima; $sumUpahLembur += $uangLembur; $sumUangMakan += $uangMakan; $sumPajak += $pph;
                    $processedDetails[] = compact('detail','emp','valLembur','valMakan','totalJam','uangLembur','uangMakan','pph','totalDiterima');
                }
            @endphp
            <section id="rekapSection" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-black text-slate-900 flex items-center gap-2">
                        <i data-lucide="calculator" class="w-4 h-4 text-emerald-500"></i>Estimasi Rekapitulasi Bulan Ini
                    </h2>
                    <span class="text-sm font-black text-emerald-700">{{ $money($totalKeseluruhan) }}</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3 text-left">Nama Pegawai</th>
                                <th class="px-4 py-3 text-right">SBU/Jam</th>
                                <th class="px-4 py-3 text-center">Jam</th>
                                <th class="px-4 py-3 text-right">Upah Lembur</th>
                                @if($hasUangMakanBulanIni)<th class="px-4 py-3 text-right">Uang Makan</th>@endif
                                <th class="px-4 py-3 text-right">Pajak</th>
                                <th class="px-4 py-3 text-right">Diterima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($processedDetails as $index => $row)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3 text-center text-slate-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['emp']->nama }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        {{ $money($row['valLembur']) }}
                                        @if(!$overtime->is_locked)
                                            <button type="button" class="ml-1 text-indigo-500 hover:text-indigo-700 btn-edit-sbu align-middle"
                                                data-detail-id="{{ $row['detail']->id }}" data-val-lembur="{{ $row['valLembur'] }}" data-val-makan="{{ $row['valMakan'] }}" data-emp-name="{{ $row['emp']->nama }}" onclick="editSbu(this)" title="Sesuaikan SBU">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5 inline-block"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">{{ $row['totalJam'] }} jam</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">{{ $money($row['uangLembur']) }}</td>
                                    @if($hasUangMakanBulanIni)<td class="px-4 py-3 text-right whitespace-nowrap">{{ $money($row['uangMakan']) }}</td>@endif
                                    <td class="px-4 py-3 text-right whitespace-nowrap text-slate-500">{{ $row['pph'] > 0 ? $money($row['pph']) : '-' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap font-bold text-emerald-700">{{ $money($row['totalDiterima']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $hasUangMakanBulanIni ? 8 : 7 }}" class="px-4 py-10 text-center text-slate-400">Belum ada data lembur di bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right text-slate-700">TOTAL</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">{{ $money($sumUpahLembur) }}</td>
                                @if($hasUangMakanBulanIni)<td class="px-4 py-3 text-right whitespace-nowrap">{{ $money($sumUangMakan) }}</td>@endif
                                <td class="px-4 py-3 text-right whitespace-nowrap">{{ $money($sumPajak) }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap text-emerald-700">{{ $money($totalKeseluruhan) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Modal Edit Lembur --}}
    <div id="eventModal" class="kdmp-modal hidden fixed inset-0 z-[70] items-center justify-center p-4">
        <div class="kdmp-modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-amber-50/60 flex items-center justify-between">
                <h3 class="font-bold text-slate-800" id="modalTitle">Edit Lembur</h3>
                <button type="button" data-dismiss="modal" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <div class="p-5">
                <p id="modalEmployeeName" class="font-bold text-slate-800"></p>
                <p id="modalDate" class="text-xs text-slate-400 mb-3"></p>
                <input type="hidden" id="modalEmployeeId"><input type="hidden" id="modalEventDate">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Berapa Jam? <span class="font-normal text-slate-400">(min. 2)</span></label>
                <input type="number" id="modalHours" min="2" max="24" value="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-amber-500 focus:ring-amber-500">
                <label class="flex items-center gap-2 mt-3 text-sm font-semibold text-slate-600 cursor-pointer">
                    <input type="checkbox" id="modalUangMakan" class="rounded text-amber-600 focus:ring-amber-500 border-slate-300">Dapat Uang Makan?
                </label>
            </div>
            <div class="px-5 py-4 bg-slate-50/70 border-t border-slate-100 flex justify-between gap-2">
                <button type="button" id="btnDeleteEvent" class="px-4 py-2 text-sm font-bold text-rose-700 bg-white border border-rose-200 hover:bg-rose-50 rounded-lg">Hapus</button>
                <button type="button" id="btnSaveEvent" class="px-4 py-2 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow-sm">Simpan</button>
            </div>
        </div>
    </div>

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

    <iframe id="printIframe" style="display:none;"></iframe>
</div>

<style>
    .fc-event { cursor: pointer; }
    #external-events .external-event:hover { background-color: #eef2ff !important; }
    .holiday-bg { background-color: #fff1f2 !important; }
    .weekend-bg { background-color: #f8fafc !important; }
    #calendar .fc .fc-toolbar-title { font-size: 1.1rem; font-weight: 800; color: #0f172a; }
    #calendar .fc .fc-button { background: #fff; border: 1px solid #e2e8f0; color: #334155; box-shadow: none; }
    #calendar .fc .fc-button:hover { background: #f1f5f9; }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Shim modal ala Bootstrap agar JS lama tetap jalan di KDMP (Tailwind).
    jQuery.fn.modal = function (action) {
        return this.each(function () {
            if (action === 'show') { this.classList.remove('hidden'); this.classList.add('flex'); }
            else { this.classList.add('hidden'); this.classList.remove('flex'); }
        });
    };
    jQuery(document).on('click', '[data-dismiss="modal"], .kdmp-modal-backdrop', function () {
        jQuery(this).closest('.kdmp-modal').modal('hide');
    });

    function printReport(url) { document.getElementById('printIframe').src = url; }

    function editSbu(btn) {
        let detailId = $(btn).data('detail-id');
        $('#sbuEmployeeName').text($(btn).data('emp-name'));
        $('#inputRateLembur').val($(btn).data('val-lembur'));
        $('#inputRateMakan').val($(btn).data('val-makan'));
        let formUrl = "{{ route('packages.overtimes.update_rates', ['package' => $package->id, 'overtime' => $overtime->id, 'detail' => ':detail']) }}";
        $('#formEditSbu').attr('action', formUrl.replace(':detail', detailId));
        $('#sbuModal').modal('show');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>

@php
    $eventsData = [];
    foreach($overtime->details as $detail) {
        $emp = $detail->employee;
        if(!$detail->daily_hours) continue;
        foreach($detail->daily_hours as $d => $hours) {
            if((int)$hours < 2) continue;
            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
            $title = $emp->nama . ' (' . $hours . ' Jam)';
            if($detail->use_uang_makan) $title .= ' 🍽️';
            $eventsData[] = [
                'id' => $emp->id . '_' . $d, 'title' => $title, 'start' => $dateStr, 'allDay' => true,
                'backgroundColor' => '#f59e0b', 'borderColor' => '#d97706', 'textColor' => '#fff',
                'extendedProps' => ['employee_id' => $emp->id, 'employee_name' => $emp->nama, 'hours' => $hours, 'use_uang_makan' => $detail->use_uang_makan ? true : false, 'day' => $d],
            ];
        }
    }
@endphp

<script>
    const UPDATE_URL = "{{ route('packages.overtimes.updateAjax', [$package, $overtime]) }}";
    const AUTOFILL_URL = "{{ route('packages.overtimes.autoFill', [$package, $overtime]) }}";
    const RESET_URL = "{{ route('packages.overtimes.reset', [$package, $overtime]) }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
    const INITIAL_EVENTS = @json($eventsData);
    const CURRENT_MONTH = "{{ $firstDayOfMonth }}";
    const IS_LOCKED = {{ $overtime->is_locked ? 'true' : 'false' }};
    let holidaysDataFull = @json($holidaysDataFull);
    let holidaysData = holidaysDataFull.map(h => h.date);
    const PREV_MONTH_URL = "{{ $month > 1 ? route('packages.overtimes.show', [$package, $month - 1]) : '' }}";
    const NEXT_MONTH_URL = "{{ $month < 12 ? route('packages.overtimes.show', [$package, $month + 1]) : '' }}";

    $(document).ready(function () {
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

        $('#checkAllPegawai').change(function () { $('.employee-checkbox:not(:disabled)').prop('checked', $(this).prop('checked')); });

        new FullCalendar.Draggable(document.getElementById('external-events'), {
            itemSelector: '.external-event',
            eventData: function (eventEl) {
                return { title: eventEl.dataset.employeeName + ' (2 Jam)', backgroundColor: '#f59e0b', borderColor: '#d97706', textColor: '#fff',
                    extendedProps: { employee_id: eventEl.dataset.employeeId, employee_name: eventEl.dataset.employeeName, hours: 2, use_uang_makan: false } };
            }
        });

        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView: 'dayGridMonth', initialDate: CURRENT_MONTH,
            customButtons: {
                prevMonthBtn: { text: '‹', click: function () { if (PREV_MONTH_URL) window.location.href = PREV_MONTH_URL; else Swal.fire('Info', 'Ini bulan pertama (Januari)', 'info'); } },
                nextMonthBtn: { text: '›', click: function () { if (NEXT_MONTH_URL) window.location.href = NEXT_MONTH_URL; else Swal.fire('Info', 'Ini bulan terakhir (Desember)', 'info'); } }
            },
            headerToolbar: { left: 'prevMonthBtn,nextMonthBtn', center: 'title', right: '' },
            locale: 'id', droppable: true, editable: false, events: INITIAL_EVENTS,
            eventReceive: function (info) {
                if (IS_LOCKED) { info.event.remove(); Swal.fire('Error', 'Data sudah dikunci.', 'error'); return; }
                let event = info.event, employeeId = event.extendedProps.employee_id, employeeName = event.extendedProps.employee_name;
                let dateStr = event.startStr, day = parseInt(dateStr.split('-')[2], 10), targetId = employeeId + '_' + day;
                let existingEvent = calendar.getEventById(targetId);
                if (existingEvent && existingEvent !== event) { event.remove(); Swal.fire('Info', 'Pegawai sudah punya jadwal di tanggal ini. Klik namanya di kalender untuk mengubah jam.', 'info'); return; }
                let isHoliday = holidaysData.includes(dateStr), d = new Date(dateStr), dow = d.getDay(), isWeekend = (dow === 0 || dow === 6);
                let defaultHours = (isHoliday || isWeekend) ? 5 : 2;
                saveEventAjax(employeeId, dateStr, defaultHours, false, 'update').then(res => {
                    if (res.success) {
                        event.setProp('id', targetId); event.setExtendedProp('day', day); event.setExtendedProp('hours', defaultHours);
                        event.setProp('title', employeeName + ' (' + defaultHours + ' Jam)'); Toast.fire({ icon: 'success', title: 'Disimpan' }); refreshRekap();
                    } else { event.remove(); Swal.fire('Error', res.message || 'Gagal menyimpan', 'error'); }
                });
            },
            eventClick: function (info) {
                if (IS_LOCKED) { Swal.fire('Info', 'Data sudah dikunci. Anda hanya bisa melihat rekap.', 'info'); return; }
                let props = info.event.extendedProps;
                $('#modalEmployeeName').text(props.employee_name); $('#modalDate').text('Tanggal: ' + info.event.startStr);
                $('#modalEmployeeId').val(props.employee_id); $('#modalEventDate').val(info.event.startStr);
                $('#modalHours').val(props.hours); $('#modalUangMakan').prop('checked', props.use_uang_makan);
                window.currentEditEvent = info.event;
                $('#eventModal').modal('show'); if (typeof lucide !== 'undefined') lucide.createIcons();
            },
            dayCellDidMount: function (info) {
                let dow = info.date.getDay();
                let d = new Date(info.date.getTime() - (info.date.getTimezoneOffset() * 60000));
                let dateStr = d.toISOString().split('T')[0];
                if (dow === 0 || dow === 6) info.el.classList.add('weekend-bg');
                if (holidaysData.includes(dateStr)) {
                    info.el.classList.add('holiday-bg');
                    let numEl = info.el.querySelector('.fc-daygrid-day-number');
                    if (numEl) { numEl.style.color = '#e11d48'; numEl.style.fontWeight = 'bold'; }
                }
            }
        });
        calendar.render();

        $('#btnSaveEvent').click(function () {
            let hours = parseInt($('#modalHours').val(), 10);
            if (hours < 2) { Swal.fire('Error', 'Minimal lembur 2 jam', 'error'); return; }
            let useUangMakan = $('#modalUangMakan').is(':checked');
            saveEventAjax($('#modalEmployeeId').val(), $('#modalEventDate').val(), hours, useUangMakan, 'update').then(res => {
                if (res.success) {
                    if (window.currentEditEvent) {
                        let title = window.currentEditEvent.extendedProps.employee_name + ' (' + hours + ' Jam)'; if (useUangMakan) title += ' 🍽️';
                        window.currentEditEvent.setProp('title', title); window.currentEditEvent.setExtendedProp('hours', hours); window.currentEditEvent.setExtendedProp('use_uang_makan', useUangMakan);
                    }
                    $('#eventModal').modal('hide'); Toast.fire({ icon: 'success', title: 'Data diperbarui' }); refreshRekap();
                } else Swal.fire('Error', res.message || 'Gagal menyimpan', 'error');
            });
        });

        $('#btnDeleteEvent').click(function () {
            Swal.fire({ title: 'Hapus data lembur?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus!' }).then((r) => {
                if (r.isConfirmed) saveEventAjax($('#modalEmployeeId').val(), $('#modalEventDate').val(), 0, false, 'delete').then(res => {
                    if (res.success) { if (window.currentEditEvent) window.currentEditEvent.remove(); $('#eventModal').modal('hide'); Toast.fire({ icon: 'success', title: 'Data dihapus' }); refreshRekap(); }
                });
            });
        });

        $('#btnAutoFill').click(function () {
            let selectedIds = []; $('.employee-checkbox:checked').each(function () { selectedIds.push($(this).val()); });
            if (selectedIds.length === 0) { Swal.fire('Peringatan', 'Pilih minimal satu pegawai', 'warning'); return; }
            Swal.fire({ title: 'Isi Otomatis 1 Bulan?', html: 'Mengisi jam lembur penuh bulan ini untuk ' + selectedIds.length + ' pegawai.<br><br><b>Hari Kerja: 2 Jam<br>Libur/Akhir Pekan: 5 Jam</b>', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Lanjutkan' }).then((r) => {
                if (r.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    fetch(AUTOFILL_URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }, body: JSON.stringify({ employee_ids: selectedIds, holidays: holidaysData }) })
                        .then(res => res.json()).then(res => { if (res.success) window.location.reload(); else Swal.fire('Error', 'Gagal menyimpan', 'error'); });
                }
            });
        });

        $('#btnReset').click(function () {
            Swal.fire({ title: 'Hapus Semua Data Bulan Ini?', text: 'Semua jam lembur bulan ini akan dihapus permanen!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#e11d48', confirmButtonText: 'Ya, Hapus Semua!' }).then((r) => {
                if (r.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    fetch(RESET_URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' } })
                        .then(res => res.json()).then(res => { if (res.success) window.location.reload(); else Swal.fire('Error', 'Gagal mereset data', 'error'); });
                }
            });
        });

        $('#btnSaveDasar').click(function () {
            let btn = $(this); btn.prop('disabled', true).text('Menyimpan...');
            fetch(UPDATE_URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }, body: JSON.stringify({ action: 'save_dasar', dasar_pelaksanaan: $('#dasarPelaksanaan').val() }) })
                .then(res => res.json()).then(res => { btn.prop('disabled', false).text('Simpan Dasar Surat'); if (res.success) Toast.fire({ icon: 'success', title: 'Dasar Surat disimpan' }); else Swal.fire('Error', 'Gagal menyimpan', 'error'); })
                .catch(() => { btn.prop('disabled', false).text('Simpan Dasar Surat'); Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'); });
        });

        function saveEventAjax(employeeId, dateStr, hours, useUangMakan, action) {
            return fetch(UPDATE_URL, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }, body: JSON.stringify({ employee_id: employeeId, date: dateStr, hours: hours, use_uang_makan: useUangMakan, action: action }) }).then(res => res.json());
        }

        // Muat ulang tabel Estimasi Rekapitulasi tanpa refresh halaman penuh.
        // Perhitungan tarif/pajak tetap di server; di sini hanya menukar HTML section-nya.
        function refreshRekap() {
            fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => {
                    const fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('rekapSection');
                    const current = document.getElementById('rekapSection');
                    if (fresh && current) {
                        current.innerHTML = fresh.innerHTML;
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                })
                .catch(() => {});
        }
    });
</script>
@endcomponent
