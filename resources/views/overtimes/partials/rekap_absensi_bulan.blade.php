{{-- Grid rekapitulasi absensi lembur SATU bulan. Dipakai print_rekap (1 bulan)
     dan spj_rekap (per bulan dalam periode SPJ). Butuh: $overtime, $package, $holidays, $skpd. --}}
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
                        @endphp
                        @if($val > 0)
                            <td class="{{ $holidayColumns[$d] ? 'bg-holiday' : '' }}">{{ $val }}</td>
                        @else
                            <td class="crossed-cell {{ $holidayColumns[$d] ? 'bg-holiday' : '' }}"></td>
                        @endif
                    @endfor
                    
                    <td rowspan="2"><strong>{{ $totalJam }}</strong></td>
                </tr>
                <tr>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                        @endphp
                        @if($val > 0)
                            <td class="{{ $holidayColumns[$d] ? 'bg-holiday' : '' }}" style="height: 15px;"></td>
                        @else
                            <td class="crossed-cell {{ $holidayColumns[$d] ? 'bg-holiday' : '' }}" style="height: 15px;"></td>
                        @endif
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
                    <td style="border: none; width: 25px;"></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0 5px 2px 0;">**)</td>
                    <td style="border: none; padding: 0 10px 2px 0;">Hari Kerja</td>
                    <td style="border: 1px solid #000; width: 25px; height: 12px; background-color: #ffffff;"></td>
                </tr>
                <tr>
                    <td style="border: none; padding: 0 5px 0 0;">***)</td>
                    <td style="border: none; padding: 0 10px 0 0;">Hari Libur</td>
                    <td class="bg-holiday" style="border: 1px solid #000; width: 25px; height: 12px;"></td>
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
