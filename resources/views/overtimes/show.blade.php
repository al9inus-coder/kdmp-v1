@extends('adminlte::page')

@section('title', 'Input Jam Lembur')

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
            @endphp
            <h1 class="m-0"><i class="fas fa-edit text-warning"></i> Input Lembur {{ $monthName }} {{ $year }}</h1>
            <p class="text-muted mt-2 mb-0">Paket: {{ $package->nama_paket }}</p>
        </div>
        <div>
            <a href="{{ route('packages.overtimes.index', $package) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('overtimeForm').submit()">
                <i class="fas fa-save mr-1"></i> Simpan Data
            </button>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> Sukses!</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">Spreadsheet Lembur</h3>
            <div class="card-tools">
                <div class="btn-group">
                    <a href="{{ route('packages.overtimes.print', [$package, $overtime, 'rekap']) }}" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-print"></i> Cetak Rekap
                    </a>
                    <a href="{{ route('packages.overtimes.print', [$package, $overtime, 'tanda_terima']) }}" target="_blank" class="btn btn-sm btn-success">
                        <i class="fas fa-file-invoice-dollar"></i> Cetak Tanda Terima
                    </a>
                    <a href="{{ route('packages.overtimes.print', [$package, $overtime, 'kwitansi']) }}" target="_blank" class="btn btn-sm btn-secondary">
                        <i class="fas fa-receipt"></i> Cetak Kwitansi
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <form id="overtimeForm" action="{{ route('packages.overtimes.update', [$package, $overtime]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="table-responsive" style="max-height: 70vh;">
                    <table class="table table-bordered table-sm table-head-fixed text-nowrap mb-0" id="overtimeTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center align-middle" rowspan="2" style="position: sticky; left: 0; z-index: 2; background-color: #f8f9fa;">No</th>
                                <th class="align-middle" rowspan="2" style="position: sticky; left: 40px; z-index: 2; background-color: #f8f9fa;">Nama / Jabatan</th>
                                <th class="text-center align-middle" colspan="{{ $daysInMonth }}">Tanggal</th>
                                <th class="text-center align-middle" rowspan="2">Uang Makan?</th>
                                <th class="text-center align-middle" rowspan="2">Total Jam</th>
                                <th class="text-center align-middle" rowspan="2">Tarif Lembur</th>
                                <th class="text-center align-middle" rowspan="2">Jumlah Kotor</th>
                            </tr>
                            <tr>
                                @for($d = 1; $d <= $daysInMonth; $d++)
                                    @php
                                        $dateString = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                        $dayOfWeek = date('N', strtotime($dateString));
                                        $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                    @endphp
                                    <th class="text-center" style="width: 40px; {{ $isWeekend ? 'background-color: #e9ecef;' : '' }}">
                                        {{ $d }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overtime->details as $index => $detail)
                                @php
                                    $emp = $detail->employee;
                                    $golongan = $emp->golongan ?? 'P3K Paruh Waktu'; // Default per user request
                                    
                                    // Get rate
                                    $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->where('golongan', $golongan)->first();
                                    if(!$rateLembur) $rateLembur = $sbuRates->where('jenis', 'Uang Lembur')->sortBy('besaran')->first();
                                    
                                    $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->where('golongan', $golongan)->first();
                                    if(!$rateMakan && str_contains($golongan, 'II') || str_contains($golongan, 'I')) {
                                        $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->where('golongan', 'Golongan II dan Golongan I')->first();
                                    }
                                    if(!$rateMakan) $rateMakan = $sbuRates->where('jenis', 'Uang Makan Lembur')->sortBy('besaran')->first();

                                    $valLembur = $rateLembur ? $rateLembur->besaran : 0;
                                    $valMakan = $rateMakan ? $rateMakan->besaran : 0;
                                @endphp
                                <tr data-rate-lembur="{{ $valLembur }}" data-rate-makan="{{ $valMakan }}">
                                    <td class="text-center align-middle" style="position: sticky; left: 0; background-color: #fff; z-index: 1;">{{ $index + 1 }}</td>
                                    <td class="align-middle" style="position: sticky; left: 40px; background-color: #fff; z-index: 1; min-width: 250px;">
                                        <strong>{{ $emp->nama }}</strong><br>
                                        <small class="text-muted">{{ $emp->jabatan }} | Gol: {{ $emp->golongan ?? '-' }}</small>
                                    </td>
                                    
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        @php
                                            $dateString = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                            $dayOfWeek = date('N', strtotime($dateString));
                                            $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
                                            $hours = isset($detail->daily_hours[$d]) ? $detail->daily_hours[$d] : '';
                                        @endphp
                                        <td class="p-0 align-middle" style="{{ $isWeekend ? 'background-color: #e9ecef;' : '' }}">
                                            <input type="number" 
                                                name="details[{{ $detail->id }}][daily_hours][{{ $d }}]" 
                                                class="form-control form-control-sm border-0 text-center day-input" 
                                                style="width: 45px; background: transparent; padding: 2px;"
                                                value="{{ $hours }}"
                                                min="0" max="24"
                                            >
                                        </td>
                                    @endfor
                                    
                                    <td class="text-center align-middle">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input makan-checkbox" 
                                                id="makan_{{ $detail->id }}" 
                                                name="details[{{ $detail->id }}][use_uang_makan]" 
                                                value="1" 
                                                {{ $detail->use_uang_makan ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="makan_{{ $detail->id }}"></label>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center align-middle font-weight-bold total-jam">0</td>
                                    <td class="text-right align-middle text-muted">
                                        Rp {{ number_format($valLembur, 0, ',', '.') }}/jam
                                    </td>
                                    <td class="text-right align-middle font-weight-bold text-success total-kotor">Rp 0</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
<style>
    input[type="number"]::-webkit-inner-spin-button, 
    input[type="number"]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type="number"] {
        -moz-appearance: textfield;
    }
    .day-input:focus {
        background-color: #fff3cd !important;
        outline: 1px solid #ffc107;
        box-shadow: none;
    }
    table th {
        font-size: 13px;
    }
    table td {
        font-size: 14px;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        function calculateRow(row) {
            let totalJam = 0;
            let daysWithOvertime = 0;
            
            // Loop inputs
            row.find('.day-input').each(function() {
                let val = parseInt($(this).val()) || 0;
                // Rule: minimum 2 hours per day
                if (val >= 2) {
                    totalJam += val;
                    daysWithOvertime++;
                }
            });
            
            let rateLembur = parseFloat(row.data('rate-lembur')) || 0;
            let rateMakan = parseFloat(row.data('rate-makan')) || 0;
            
            let uangLembur = totalJam * rateLembur;
            
            let useMakan = row.find('.makan-checkbox').is(':checked');
            let uangMakan = 0;
            if (useMakan) {
                // Assuming uang makan is given for each day they do valid overtime
                uangMakan = daysWithOvertime * rateMakan;
            }
            
            let totalKotor = uangLembur + uangMakan;
            
            // Update UI
            row.find('.total-jam').text(totalJam);
            row.find('.total-kotor').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalKotor));
        }

        // Initialize calculations
        $('#overtimeTable tbody tr').each(function() {
            calculateRow($(this));
        });

        // Event listeners
        $('.day-input').on('input', function() {
            calculateRow($(this).closest('tr'));
        });
        
        $('.makan-checkbox').on('change', function() {
            calculateRow($(this).closest('tr'));
        });
    });
</script>
@stop
