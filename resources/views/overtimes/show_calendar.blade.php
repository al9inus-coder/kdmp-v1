@extends('adminlte::page')

@section('title', 'Kalender Lembur')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            @php
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
                    4 => 'April', 5 => 'Mei', 6 => 'Juni', 
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
                    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $monthName = $months[$month];
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                $firstDayOfMonth = sprintf('%04d-%02d-01', $year, $month);
            @endphp
            <h1 class="m-0"><i class="fas fa-calendar-alt text-warning"></i> Kalender Lembur {{ $monthName }} {{ $year }}</h1>
            <p class="text-muted mt-2 mb-0">Paket: {{ $package->nama_paket }}</p>
        </div>
        <div>
            <a href="{{ route('procurement-packages.show', $package) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Paket
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <!-- Kolom Kiri: Daftar Pegawai -->
        <div class="col-md-3">
            <div class="card card-outline card-primary shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users mr-1"></i> Daftar Pegawai</h3>
                </div>
                <div class="card-body p-0">
                    <div class="p-3 bg-light border-bottom">
                        @if(!$overtime->is_locked)
                            <button type="button" class="btn btn-primary btn-block btn-sm mb-2" id="btnAutoFill">
                                <i class="fas fa-magic"></i> Isi Otomatis 1 Bulan
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-block btn-sm mb-2" id="btnReset">
                                <i class="fas fa-trash-alt"></i> Hapus Semua (Reset Bulan)
                            </button>
                        @else
                            <div class="alert alert-success p-2 mb-2 text-center" style="font-size: 13px;">
                                <i class="fas fa-lock mb-1 d-block"></i> Terkunci
                            </div>
                        @endif
                        <small class="text-muted d-block text-center">
                            Hari Kerja: 2 Jam | Hari Libur: 5 Jam
                        </small>
                    </div>
                    
                    <div class="p-2 bg-white border-bottom d-flex justify-content-between align-items-center">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="checkAllPegawai">
                            <label class="custom-control-label" for="checkAllPegawai"><small>Pilih Semua</small></label>
                        </div>
                    </div>

                    <div id="external-events" style="max-height: 50vh; overflow-y: auto; padding: 10px;">
                        @foreach($overtime->details as $detail)
                            @php $emp = $detail->employee; @endphp
                            <div class="d-flex align-items-center mb-2 p-2 border rounded bg-white external-event" 
                                 data-employee-id="{{ $emp->id }}" 
                                 data-employee-name="{{ $emp->nama }}"
                                 style="cursor: grab;"
                                 onclick="let cb = $(this).find('.employee-checkbox'); cb.prop('checked', !cb.prop('checked'));">
                                <div class="mr-3 ml-2">
                                    <input type="checkbox" class="employee-checkbox" value="{{ $emp->id }}" style="transform: scale(1.5); cursor: pointer;" onclick="event.stopPropagation();" {{ $overtime->is_locked ? 'disabled' : '' }}>
                                </div>
                                <div style="flex-grow: 1;">
                                    <strong>{{ $emp->nama }}</strong>
                                </div>
                                <div><i class="fas fa-arrows-alt text-muted"></i></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if(count($holidaysDataFull) > 0)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="card-title font-weight-bold mb-0" style="font-size: 14px;"><i class="fas fa-info-circle text-info mr-1"></i> Libur Nasional Bulan Ini</h6>
                </div>
                <div class="card-body p-2">
                    <ul class="mb-0 text-muted pl-3" style="font-size: 12px;">
                        @php $hasHolidayThisMonth = false; @endphp
                        @foreach($holidaysDataFull as $h)
                            @if(str_starts_with($h['date'], sprintf('%04d-%02d', $year, $month)))
                                @php $hasHolidayThisMonth = true; @endphp
                                <li><strong>{{ \Carbon\Carbon::parse($h['date'])->translatedFormat('d F Y') }}:</strong><br>{{ $h['description'] }}</li>
                            @endif
                        @endforeach
                        @if(!$hasHolidayThisMonth)
                            <li>Tidak ada libur nasional.</li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif

            <!-- Tombol Cetak -->
            <div class="card card-outline card-secondary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-print mr-1"></i> Cetak Laporan</h3>
                </div>
                <div class="card-body p-2">
                    <div class="form-group mb-3">
                        <label style="font-size: 13px;">Dasar Pelaksanaan (SK & Surat Tugas)</label>
                        <textarea id="dasarPelaksanaan" class="form-control" rows="3" style="font-size: 12px;" placeholder="1. SK Kepala Dinas...&#10;2. Surat Tugas..." {{ $overtime->is_locked ? 'disabled' : '' }}>{{ $overtime->dasar_pelaksanaan }}</textarea>
                        @if(!$overtime->is_locked)
                        <button id="btnSaveDasar" class="btn btn-xs btn-outline-primary mt-2 w-100"><i class="fas fa-save mr-1"></i> Simpan Dasar Surat</button>
                        @endif
                    </div>
                    <hr>
                    <button onclick="printReport('{{ route('packages.overtimes.print', [$package, $overtime, 'rekap']) }}')" class="btn btn-block btn-sm btn-info mb-2 text-left">
                        <i class="fas fa-table mr-2"></i> Rekapitulasi
                    </button>
                    <button onclick="printReport('{{ route('packages.overtimes.print', [$package, $overtime, 'tanda_terima']) }}')" class="btn btn-block btn-sm btn-success mb-2 text-left">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Tanda Terima
                    </button>
                    <button onclick="printReport('{{ route('packages.overtimes.print', [$package, $overtime, 'kwitansi']) }}')" class="btn btn-block btn-sm btn-secondary text-left">
                        <i class="fas fa-receipt mr-2"></i> Kwitansi
                    </button>
                </div>
            </div>

            <!-- Panel Kunci Data -->
            @php
                $userRole = auth()->user()->getRoleNames()->first() ?? '';
            @endphp
            @if(in_array($userRole, ['Admin', 'Kabid']))
            <div class="card card-outline {{ $overtime->is_locked ? 'card-danger' : 'card-warning' }} shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt mr-1"></i> Keamanan Data</h3>
                </div>
                <div class="card-body p-2">
                    @if(!$overtime->is_locked)
                        <form action="{{ route('packages.overtimes.lock', [$package, $month]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-block btn-sm btn-danger font-weight-bold" onclick="return confirm('Yakin ingin mengunci data lembur bulan ini? Setelah dikunci, data tidak bisa diubah.')">
                                <i class="fas fa-lock mr-2"></i> Kunci Data SPJ
                            </button>
                        </form>
                    @else
                        @if($userRole === 'Admin')
                        <form action="{{ route('packages.overtimes.unlock', [$package, $month]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-block btn-sm btn-outline-warning" onclick="return confirm('Buka kunci data? Data bisa diedit lagi.')">
                                <i class="fas fa-unlock mr-2"></i> Buka Kunci (Admin)
                            </button>
                        </form>
                        @else
                        <button class="btn btn-block btn-sm btn-secondary disabled">
                            <i class="fas fa-lock mr-2"></i> Terkunci
                        </button>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </div>

            <!-- Kolom Kanan: Kalender -->
        <div class="col-md-9">
            <div class="card card-outline card-warning shadow-sm mb-3">
                <div class="card-body p-0">
                    <div id="calendar" style="min-height: 600px;"></div>
                </div>
            </div>

            <!-- Summary Rekap -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-calculator mr-1 text-success"></i> Estimasi Rekapitulasi Bulan Ini</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    @php 
                        $totalKeseluruhan = 0;
                        $sumUpahLembur = 0;
                        $sumUangMakan = 0;
                        $sumPajak = 0;
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $hasUangMakanBulanIni = false;
                        $processedDetails = [];

                        foreach($overtime->details as $index => $detail) {
                            $emp = $detail->employee;
                            $golongan = $detail->golongan_fix ?? $emp->golongan ?? 'P3K Paruh Waktu';
                            
                            $totalJam = 0;
                            $daysWithOvertime = 0;
                            for($d = 1; $d <= $daysInMonth; $d++) {
                                $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                                if($val >= 2) {
                                    $totalJam += $val;
                                    $daysWithOvertime++;
                                }
                            }
                            
                            if($totalJam == 0) continue;
                            
                            $empGol = strtoupper($golongan);
                            $mappedGolongan = 'P3K Paruh Waktu'; // default

                            if (str_contains($empGol, 'IV-') || str_contains($empGol, '/IV') || str_contains($empGol, 'GOLONGAN IV')) {
                                $mappedGolongan = 'Golongan IV';
                            } elseif (str_contains($empGol, 'III-') || str_contains($empGol, '/III') || str_contains($empGol, 'GOLONGAN III')) {
                                $mappedGolongan = 'Golongan III';
                            } elseif (str_contains($empGol, 'II-') || str_contains($empGol, '/II') || str_contains($empGol, 'GOLONGAN II') || str_contains($empGol, 'VII')) {
                                $mappedGolongan = 'Golongan II';
                            } elseif (str_contains($empGol, 'I-') || str_contains($empGol, '/I') || str_contains($empGol, 'GOLONGAN I')) {
                                $mappedGolongan = 'Golongan I';
                            }
                            
                            // Rate Lembur
                            if (!is_null($detail->rate_lembur_fix)) {
                                $valLembur = $detail->rate_lembur_fix;
                            } else {
                                $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->where('golongan', $mappedGolongan)->first();
                                if(!$rateLembur) $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->sortBy('besaran')->first();
                                $valLembur = $rateLembur ? $rateLembur->besaran : 0;
                            }
                            
                            // Rate Makan
                            if (!is_null($detail->rate_makan_fix)) {
                                $valMakan = $detail->rate_makan_fix;
                            } else {
                                $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->where('golongan', $mappedGolongan)->first();
                                if(!$rateMakan && (str_contains($mappedGolongan, 'II') || str_contains($mappedGolongan, 'I'))) {
                                    $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->where('golongan', 'Golongan II dan Golongan I')->first();
                                }
                                if(!$rateMakan) $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->sortBy('besaran')->first();
                                $valMakan = $rateMakan ? $rateMakan->besaran : 0;
                            }
                            
                            $uangLembur = $totalJam * $valLembur;
                            $uangMakan = $detail->use_uang_makan ? ($daysWithOvertime * $valMakan) : 0;
                            
                            if($uangMakan > 0) {
                                $hasUangMakanBulanIni = true;
                            }
                            
                            // PPh
                            $pphRate = 0;
                            if (str_contains(strtoupper($golongan), 'III')) {
                                $pphRate = 0.05;
                            } elseif (str_contains(strtoupper($golongan), 'IV')) {
                                $pphRate = 0.15;
                            }
                            $pph = $uangLembur * $pphRate;
                            $totalDiterima = ($uangLembur - $pph) + $uangMakan;

                            $totalKeseluruhan += $totalDiterima;
                            $sumUpahLembur += $uangLembur;
                            $sumUangMakan += $uangMakan;
                            $sumPajak += $pph;

                            $processedDetails[] = [
                                'detail' => $detail,
                                'emp' => $emp,
                                'valLembur' => $valLembur,
                                'valMakan' => $valMakan,
                                'totalJam' => $totalJam,
                                'uangLembur' => $uangLembur,
                                'uangMakan' => $uangMakan,
                                'pph' => $pph,
                                'totalDiterima' => $totalDiterima,
                            ];
                        }
                    @endphp
                    <table class="table table-sm table-striped table-bordered mb-0" style="font-size: 14px;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th>Nama Pegawai</th>
                                <th class="text-right">SBU Lembur per Jam</th>
                                <th class="text-center">Total Jam</th>
                                <th class="text-right">Upah Lembur</th>
                                @if($hasUangMakanBulanIni)
                                <th class="text-right">Uang Makan</th>
                                @endif
                                <th class="text-right">Potongan Pajak</th>
                                <th class="text-right">Upah Lembur Diterima</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processedDetails as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $row['emp']->nama }}</td>
                                    <td class="text-right">
                                        Rp {{ number_format($row['valLembur'], 0, ',', '.') }}
                                        @if(!$overtime->is_locked)
                                        <button type="button" class="btn btn-xs btn-link text-primary p-0 ml-1 btn-edit-sbu" 
                                            data-detail-id="{{ $row['detail']->id }}" 
                                            data-val-lembur="{{ $row['valLembur'] }}" 
                                            data-val-makan="{{ $row['valMakan'] }}" 
                                            data-emp-name="{{ $row['emp']->nama }}"
                                            onclick="editSbu(this)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $row['totalJam'] }} Jam</td>
                                    <td class="text-right">Rp {{ number_format($row['uangLembur'], 0, ',', '.') }}</td>
                                    @if($hasUangMakanBulanIni)
                                    <td class="text-right">Rp {{ number_format($row['uangMakan'], 0, ',', '.') }}</td>
                                    @endif
                                    <td class="text-right">Rp {{ $row['pph'] > 0 ? number_format($row['pph'], 0, ',', '.') : '-' }}</td>
                                    <td class="text-right font-weight-bold text-success">Rp {{ number_format($row['totalDiterima'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            @if(count($processedDetails) == 0)
                                <tr>
                                    <td colspan="{{ $hasUangMakanBulanIni ? 8 : 7 }}" class="text-center text-muted">Belum ada data lembur di bulan ini.</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <th colspan="4" class="text-right font-weight-bold">TOTAL JUMLAH</th>
                                <th class="text-right font-weight-bold">Rp {{ number_format($sumUpahLembur, 0, ',', '.') }}</th>
                                @if($hasUangMakanBulanIni)
                                <th class="text-right font-weight-bold">Rp {{ number_format($sumUangMakan, 0, ',', '.') }}</th>
                                @endif
                                <th class="text-right font-weight-bold">Rp {{ number_format($sumPajak, 0, ',', '.') }}</th>
                                <th class="text-right font-weight-bold text-primary" style="font-size: 16px;">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form (Hidden) -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Edit Lembur</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="modalEmployeeName" class="font-weight-bold mb-3"></p>
                    <p id="modalDate" class="text-muted mb-3"></p>
                    
                    <input type="hidden" id="modalEmployeeId">
                    <input type="hidden" id="modalEventDate">
                    
                    <div class="form-group">
                        <label>Berapa Jam?</label>
                        <input type="number" id="modalHours" class="form-control" min="2" max="24" value="2">
                        <small class="text-muted">Minimal 2 jam.</small>
                    </div>
                    
                    <div class="custom-control custom-checkbox mt-3">
                        <input type="checkbox" class="custom-control-input" id="modalUangMakan">
                        <label class="custom-control-label" for="modalUangMakan">Dapat Uang Makan?</label>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" id="btnDeleteEvent">Hapus</button>
                    <button type="button" class="btn btn-primary" id="btnSaveEvent">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit SBU -->
    <div class="modal fade" id="sbuModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <form id="formEditSbu" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Penyesuaian SBU</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p id="sbuEmployeeName" class="font-weight-bold mb-3"></p>
                        <div class="form-group">
                            <label>Tarif Uang Lembur (Rp)</label>
                            <input type="number" name="rate_lembur_fix" id="inputRateLembur" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Tarif Uang Makan (Rp)</label>
                            <input type="number" name="rate_makan_fix" id="inputRateMakan" class="form-control">
                        </div>
                        <small class="text-muted">Kosongkan/hapus untuk menggunakan tarif bawaan master SBU.</small>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Iframe for Printing -->
    <iframe id="printIframe" style="display:none;"></iframe>
@stop

@section('css')
    <!-- FullCalendar CSS -->
    <style>
        .fc-event { cursor: pointer; }
        #external-events .external-event:hover { background-color: #f8f9fa !important; }
        .holiday-bg { background-color: #ffeaea !important; }
        .weekend-bg { background-color: #f8f9fa !important; }
    </style>
@stop

@section('js')
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function printReport(url) {
            let iframe = document.getElementById('printIframe');
            iframe.src = url;
        }

        function editSbu(btn) {
            let detailId = $(btn).data('detail-id');
            let rateLembur = $(btn).data('val-lembur');
            let rateMakan = $(btn).data('val-makan');
            let empName = $(btn).data('emp-name');

            $('#sbuEmployeeName').text(empName);
            $('#inputRateLembur').val(rateLembur);
            $('#inputRateMakan').val(rateMakan);
            
            let formUrl = "{{ route('packages.overtimes.update_rates', ['package' => $package->id, 'overtime' => $overtime->id, 'detail' => ':detail']) }}";
            formUrl = formUrl.replace(':detail', detailId);
            $('#formEditSbu').attr('action', formUrl);
            
            $('#sbuModal').modal('show');
        }
    </script>

    @php
        // Prepare events data
        $eventsData = [];
        foreach($overtime->details as $detail) {
            $emp = $detail->employee;
            if(!$detail->daily_hours) continue;
            
            foreach($detail->daily_hours as $d => $hours) {
                if((int)$hours < 2) continue; // Skip invalid
                
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $title = $emp->nama . ' (' . $hours . ' Jam)';
                if($detail->use_uang_makan) {
                    $title .= ' 🍽️';
                }
                
                $eventsData[] = [
                    'id' => $emp->id . '_' . $d, // employeeId_day
                    'title' => $title,
                    'start' => $dateStr,
                    'allDay' => true,
                    'backgroundColor' => '#ffc107',
                    'borderColor' => '#d39e00',
                    'textColor' => '#000',
                    'extendedProps' => [
                        'employee_id' => $emp->id,
                        'employee_name' => $emp->nama,
                        'hours' => $hours,
                        'use_uang_makan' => $detail->use_uang_makan ? true : false,
                        'day' => $d
                    ]
                ];
            }
        }
    @endphp

    <script>
        const UPDATE_URL = "{{ route('packages.overtimes.updateAjax', [$package, $overtime]) }}";
        const AUTOFILL_URL = "{{ route('packages.overtimes.autoFill', [$package, $overtime]) }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
        const INITIAL_EVENTS = @json($eventsData);
        const CURRENT_MONTH = "{{ $firstDayOfMonth }}";
        const MONTH = "{{ $month }}";
        const IS_LOCKED = {{ $overtime->is_locked ? 'true' : 'false' }};
        
        let holidaysDataFull = @json($holidaysDataFull);
        let holidaysData = holidaysDataFull.map(h => h.date);

        const PREV_MONTH_URL = "{{ $month > 1 ? route('packages.overtimes.show', [$package, $month - 1]) : '' }}";
        const NEXT_MONTH_URL = "{{ $month < 12 ? route('packages.overtimes.show', [$package, $month + 1]) : '' }}";

        $(document).ready(function() {
            
            // Check all pegawai
            $('#checkAllPegawai').change(function() {
                $('.employee-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Initialize Draggables
            new FullCalendar.Draggable(document.getElementById('external-events'), {
                itemSelector: '.external-event',
                eventData: function(eventEl) {
                    return {
                        title: eventEl.dataset.employeeName + ' (2 Jam)',
                        backgroundColor: '#ffc107',
                        borderColor: '#d39e00',
                        textColor: '#000',
                        extendedProps: {
                            employee_id: eventEl.dataset.employeeId,
                            employee_name: eventEl.dataset.employeeName,
                            hours: 2, // default 2 jam
                            use_uang_makan: false // default false
                        }
                    };
                }
            });

            // Initialize Calendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: CURRENT_MONTH,
                customButtons: {
                    prevMonthBtn: {
                        text: '<',
                        click: function() {
                            if(PREV_MONTH_URL) {
                                window.location.href = PREV_MONTH_URL;
                            } else {
                                Swal.fire('Info', 'Ini adalah bulan pertama (Januari)', 'info');
                            }
                        }
                    },
                    nextMonthBtn: {
                        text: '>',
                        click: function() {
                            if(NEXT_MONTH_URL) {
                                window.location.href = NEXT_MONTH_URL;
                            } else {
                                Swal.fire('Info', 'Ini adalah bulan terakhir (Desember)', 'info');
                            }
                        }
                    }
                },
                headerToolbar: {
                    left: 'prevMonthBtn,nextMonthBtn',
                    center: 'title',
                    right: '' // hide navigation since it's locked to a specific month
                },
                locale: 'id',
                droppable: true,
                editable: false, // We don't need them to drag an event from one day to another yet, but we could.
                events: INITIAL_EVENTS,
                
                // When dropped from external list (Fullcalendar automatically creates an event first)
                eventReceive: function(info) {
                    if (IS_LOCKED) {
                        info.event.remove();
                        Swal.fire('Error', 'Data sudah dikunci. Tidak bisa menambah jadwal.', 'error');
                        return;
                    }
                    let event = info.event;
                    let employeeId = event.extendedProps.employee_id;
                    let employeeName = event.extendedProps.employee_name;
                    let dateStr = event.startStr; // YYYY-MM-DD
                    let day = parseInt(dateStr.split('-')[2], 10);
                    let targetId = employeeId + '_' + day;
                    
                    // Cek apakah sudah ada event untuk pegawai ini di tanggal ini
                    let existingEvent = calendar.getEventById(targetId);
                    if(existingEvent && existingEvent !== event) {
                        event.remove(); // Hapus seketika
                        Swal.fire('Info', 'Pegawai sudah memiliki jadwal lembur di tanggal ini. Silakan klik namanya di kalender untuk mengubah jam.', 'info');
                        return;
                    }
                    
                    // Cek apakah jatuh di hari libur atau weekend, kalau ya jadikan default 5 jam
                    let isHoliday = holidaysData.includes(dateStr);
                    let d = new Date(dateStr);
                    let dayOfWeek = d.getDay(); // 0 is Sunday, 6 is Saturday
                    let isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    let defaultHours = (isHoliday || isWeekend) ? 5 : 2;
                    
                    // We need to save it immediately
                    saveEventAjax(employeeId, dateStr, defaultHours, false, 'update').then(res => {
                        if(res.success) {
                            // Update id to proper format so we can edit it later
                            event.setProp('id', targetId);
                            event.setExtendedProp('day', day);
                            event.setExtendedProp('hours', defaultHours);
                            event.setProp('title', employeeName + ' (' + defaultHours + ' Jam)');
                            
                            Toast.fire({
                                icon: 'success',
                                title: 'Disimpan'
                            });
                        } else {
                            // Hapus event yang gagal disimpan
                            event.remove();
                            Swal.fire('Error', res.message || 'Gagal menyimpan', 'error');
                        }
                    });
                },
                
                // When clicking an existing event
                eventClick: function(info) {
                    if (IS_LOCKED) {
                        Swal.fire('Info', 'Data sudah dikunci (Selesai Di-SPJ-kan). Anda hanya bisa melihat rekap.', 'info');
                        return;
                    }
                    let props = info.event.extendedProps;
                    let dateStr = info.event.startStr;
                    
                    $('#modalTitle').text('Edit Lembur');
                    $('#modalEmployeeName').text(props.employee_name);
                    $('#modalDate').text('Tanggal: ' + dateStr);
                    $('#modalEmployeeId').val(props.employee_id);
                    $('#modalEventDate').val(dateStr);
                    $('#modalHours').val(props.hours);
                    $('#modalUangMakan').prop('checked', props.use_uang_makan);
                    
                    // Store the fullcalendar event object in a global var so we can update/remove it later
                    window.currentEditEvent = info.event;
                    
                    $('#eventModal').modal('show');
                },

                // Highlight weekends and holidays
                dayCellDidMount: function(info) {
                    let dayOfWeek = info.date.getDay(); // 0 is Sunday, 6 is Saturday
                    let dateStr = info.date.toISOString().split('T')[0]; // Simple YYYY-MM-DD for local dates might need timezone adjust. better to use info.dateStr if available?
                    // Actually dayCellDidMount doesn't give dateStr directly like event drops.
                    // Let's format manually:
                    let d = new Date(info.date.getTime() - (info.date.getTimezoneOffset() * 60000));
                    dateStr = d.toISOString().split('T')[0];
                    
                    if (dayOfWeek === 0 || dayOfWeek === 6) {
                        info.el.classList.add('weekend-bg');
                    }
                    
                    if(holidaysData.includes(dateStr)) {
                        info.el.classList.add('holiday-bg');
                        // Add a small red text indicating holiday
                        let numEl = info.el.querySelector('.fc-daygrid-day-number');
                        if(numEl) {
                            numEl.style.color = 'red';
                            numEl.style.fontWeight = 'bold';
                        }
                    }
                }
            });

            // Render calendar directly
            calendar.render();

            // Toast setup
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            // Save Event manually
            $('#btnSaveEvent').click(function() {
                let employeeId = $('#modalEmployeeId').val();
                let dateStr = $('#modalEventDate').val();
                let hours = parseInt($('#modalHours').val(), 10);
                let useUangMakan = $('#modalUangMakan').is(':checked');
                
                if(hours < 2) {
                    Swal.fire('Error', 'Minimal lembur 2 jam', 'error');
                    return;
                }

                saveEventAjax(employeeId, dateStr, hours, useUangMakan, 'update').then(res => {
                    if(res.success) {
                        // Update the event in calendar
                        if(window.currentEditEvent) {
                            let title = window.currentEditEvent.extendedProps.employee_name + ' (' + hours + ' Jam)';
                            if(useUangMakan) title += ' 🍽️';
                            
                            window.currentEditEvent.setProp('title', title);
                            window.currentEditEvent.setExtendedProp('hours', hours);
                            window.currentEditEvent.setExtendedProp('use_uang_makan', useUangMakan);
                        }
                        $('#eventModal').modal('hide');
                        Toast.fire({ icon: 'success', title: 'Data diperbarui' });
                    } else {
                        Swal.fire('Error', res.message || 'Gagal menyimpan', 'error');
                    }
                });
            });

            // Delete Event
            $('#btnDeleteEvent').click(function() {
                let employeeId = $('#modalEmployeeId').val();
                let dateStr = $('#modalEventDate').val();

                Swal.fire({
                    title: 'Hapus data lembur?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        saveEventAjax(employeeId, dateStr, 0, false, 'delete').then(res => {
                            if(res.success) {
                                if(window.currentEditEvent) {
                                    window.currentEditEvent.remove();
                                }
                                $('#eventModal').modal('hide');
                                Toast.fire({ icon: 'success', title: 'Data dihapus' });
                            }
                        });
                    }
                });
            });

            // Auto Fill
            $('#btnAutoFill').click(function() {
                let selectedIds = [];
                $('.employee-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if(selectedIds.length === 0) {
                    Swal.fire('Peringatan', 'Pilih minimal satu pegawai', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Isi Otomatis 1 Bulan?',
                    html: 'Sistem akan mengisi jam lembur secara penuh pada bulan ini untuk ' + selectedIds.length + ' pegawai terpilih.<br><br><b>Hari Kerja: 2 Jam<br>Hari Libur/Akhir Pekan: 5 Jam</b>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Lanjutkan'
                }).then((result) => {
                    if(result.isConfirmed) {
                        
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch(AUTOFILL_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                employee_ids: selectedIds,
                                holidays: holidaysData
                            })
                        })
                        .then(res => res.json())
                        .then(res => {
                            if(res.success) {
                                // Reload page to reflect all changes in calendar easily
                                window.location.reload();
                            } else {
                                Swal.fire('Error', 'Gagal menyimpan', 'error');
                            }
                        });
                    }
                });
            });

            // Reset Month
            $('#btnReset').click(function() {
                Swal.fire({
                    title: 'Hapus Semua Data Bulan Ini?',
                    text: 'Semua jam lembur yang sudah terinput di bulan ini akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus Semua!'
                }).then((result) => {
                    if(result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch("{{ route('packages.overtimes.reset', [$package, $overtime]) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(res => {
                            if(res.success) {
                                window.location.reload();
                            } else {
                                Swal.fire('Error', 'Gagal mereset data', 'error');
                            }
                        });
                    }
                });
            });

            // Save Dasar Pelaksanaan
            $('#btnSaveDasar').click(function() {
                let dasarText = $('#dasarPelaksanaan').val();
                let btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
                
                fetch(UPDATE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_dasar',
                        dasar_pelaksanaan: dasarText
                    })
                })
                .then(res => res.json())
                .then(res => {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Dasar Surat');
                    if(res.success) {
                        Toast.fire({ icon: 'success', title: 'Dasar Surat berhasil disimpan' });
                    } else {
                        Swal.fire('Error', 'Gagal menyimpan', 'error');
                    }
                })
                .catch(err => {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Dasar Surat');
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                });
            });

            // AJAX Save helper
            function saveEventAjax(employeeId, dateStr, hours, useUangMakan, action) {
                return fetch(UPDATE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        date: dateStr,
                        hours: hours,
                        use_uang_makan: useUangMakan,
                        action: action
                    })
                }).then(res => res.json());
            }

        });
    </script>
@stop
