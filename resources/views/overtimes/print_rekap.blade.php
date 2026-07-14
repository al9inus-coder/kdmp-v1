<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Absensi Lembur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3, .header h4 {
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        th {
            background-color: #ffffffff;
        }
        .text-left {
            text-align: left;
        }
        .signature-area {
            width: 100%;
            margin-top: 30px;
        }
        .signature-box {
            width: 30%;
            float: right;
            text-align: center;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .bg-holiday {
            background-color: #f2f2f2 !important;
            /* Pola arsiran bintik yang lebih renggang dan ringan */
            background-image: url("data:image/svg+xml,%3Csvg width='6' height='6' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='1.5' cy='1.5' r='1' fill='%23999' /%3E%3C/svg%3E") !important;
            background-repeat: repeat !important;
        }
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            body { margin: 0; padding: 0; }
            /* @page { size: 330mm 210mm; margin: 10mm; } */
        }
    </style>
</head>
<body onload="window.print()" onafterprint="window.close()">

    @php
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 
            4 => 'April', 5 => 'Mei', 6 => 'Juni', 
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 
            10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $months[$overtime->bulan];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $overtime->bulan, $overtime->tahun);
        
        $jumlahHL = 0;
        $jumlahHK = 0;
        
        $holidayColumns = [];
        $dayNames = [];
        
        $namaHari = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        
        for($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $overtime->tahun, $overtime->bulan, $d);
            $dayOfWeek = date('N', strtotime($dateStr));
            $dayNames[$d] = $namaHari[$dayOfWeek];
            
            $isWeekend = ($dayOfWeek == 6 || $dayOfWeek == 7);
            $isHoliday = in_array($dateStr, $holidays);
            
            if($isWeekend || $isHoliday) {
                $jumlahHL++;
                $holidayColumns[$d] = true;
            } else {
                $jumlahHK++;
                $holidayColumns[$d] = false;
            }
        }
    @endphp

    <div class="header">
        <h3>REKAPITULASI ABSENSI LEMBUR</h3>
    </div>

    <table style="width: 100%; border: none; margin-bottom: 20px; font-size: 11px;">
        <tr>
            <td style="border: none; text-align: left; width: 120px; vertical-align: top;">Dasar</td>
            <td style="border: none; text-align: left; width: 10px; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">
                {!! nl2br(e($overtime->dasar_pelaksanaan ?? '...................................................')) !!}
            </td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Program</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $package->program->kode ?? '...' }} &nbsp;&nbsp;&nbsp;&nbsp; {{ $package->program->nama ?? '...' }}</td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Kegiatan</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $package->activity->kode ?? '...' }} &nbsp;&nbsp;&nbsp;&nbsp; {{ $package->activity->nama ?? '...' }}</td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Sub Kegiatan</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $package->subActivity->kode ?? '...' }} &nbsp;&nbsp;&nbsp;&nbsp; {{ $package->subActivity->nama ?? '...' }}</td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Tahun</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $overtime->tahun }}</td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Bulan</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $monthName }}</td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Jumlah HB *)</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $daysInMonth }}</td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Jumlah HK **)</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $jumlahHK }}</td>
        </tr>
        <tr>
            <td style="border: none; text-align: left; vertical-align: top;">Jumlah HL ***)</td>
            <td style="border: none; text-align: left; vertical-align: top;">:</td>
            <td style="border: none; text-align: left; vertical-align: top;">{{ $jumlahHL }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width: 3%;">NO.</th>
                <th rowspan="3" style="width: 17%;">NAMA/ NIP</th>
                <th rowspan="3" style="width: 15%;">PANGKAT/GOLONGAN</th>
                <th colspan="{{ $daysInMonth }}">JUMLAH LEMBUR PER HARI (JAM)</th>
                <th rowspan="3" style="width: 5%;">TOTAL JAM LEMBUR</th>
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="{{ $holidayColumns[$d] ? 'bg-holiday' : '' }}" style="height: 35px;">
                        <div style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 9px; margin: auto;">
                            {{ $dayNames[$d] }}
                        </div>
                    </th>
                @endfor
            </tr>
            <tr>
                @for($d = 1; $d <= $daysInMonth; $d++)
                    <th class="{{ $holidayColumns[$d] ? 'bg-holiday' : '' }}" style="width: 2%;">{{ sprintf('%02d', $d) }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($overtime->details as $index => $detail)
                @php
                    $emp = $detail->employee;
                    
                    $totalJam = 0;
                    for($d = 1; $d <= $daysInMonth; $d++) {
                        $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                        if($val >= 2) {
                            $totalJam += $val;
                        }
                    }
                    
                    if($totalJam == 0) continue; // Jangan tampilkan yang tidak ada lembur
                @endphp
                <tr>
                    <td rowspan="2">{{ $no++ }}</td>
                    <td rowspan="2" class="text-left">{{ $emp->nama }}<br>NIP: {{ $emp->nip ?? '-' }}</td>
                    <td rowspan="2" class="text-left">{{ $emp->golongan }}</td>
                    
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                            $display = ($val > 0) ? $val : 'X';
                        @endphp
                        <td class="{{ $holidayColumns[$d] ? 'bg-holiday' : '' }}">{{ $display }}</td>
                    @endfor
                    
                    <td rowspan="2"><strong>{{ $totalJam }}</strong></td>
                </tr>
                <tr>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        <td class="{{ $holidayColumns[$d] ? 'bg-holiday' : '' }}" style="height: 15px;"></td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix signature-area" style="margin-top: 20px;">
        <div style="float: left; width: 40%; font-size: 11px;">
            <p style="margin-bottom: 2px;">Keterangan :</p>
            <table style="border: none; width: auto;">
                <tr>
                    <td style="border: none; padding: 0 5px 2px 0;">*)</td>
                    <td style="border: none; padding: 0 10px 2px 0;">Hari Bulan</td>
                    <td rowspan="3" style="border: 1px solid #000; width: 25px; vertical-align: bottom;">
                        <div style="height: 15px; border-bottom: 1px solid #000; background-color: #fff;"></div>
                        <div class="bg-holiday" style="height: 15px;"></div>
                    </td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0 5px 2px 0;">**)</td>
                    <td style="border: none; padding: 0 10px 2px 0;">Hari Kerja</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0 5px 0 0;">***)</td>
                    <td style="border: none; padding: 0 10px 0 0;">Hari Libur</td>
                </tr>
            </table>
        </div>

        <div class="signature-box">
            <p>Bengkayang, .............................. {{ $overtime->tahun }}<br><br>
            Pejabat Pelaksana Teknis Kegiatan (PPTK)</p>
            <br><br><br>
            <p><strong><u>{{ $skpd->nama_pptk ?? '..........................' }}</u></strong><br>
            {{ $skpd->pangkat_pptk ?? '..........................' }}<br>
            NIP. {{ $skpd->nip_pptk ?? '..........................' }}</p>
        </div>
    </div>

</body>
</html>
