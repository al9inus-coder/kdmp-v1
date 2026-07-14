<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Uang Lembur</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* @page {
            size: A4 portrait;
            margin: 10mm 15mm;
        } */
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            background: white;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
        }
        .kop-pemerintah {
            font-size: 14pt;
            font-weight: bold;
        }
        .kop-dinas {
            font-size: 16pt;
            font-weight: bold;
        }
        .kop-alamat {
            font-size: 11pt;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            text-decoration: underline;
            margin-bottom: 5px;
            letter-spacing: 2px;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .main-table {
            border: 1px solid #000;
            width: 100%;
            border-collapse: collapse;
        }
        .main-table > tbody > tr > td {
            vertical-align: top;
        }
        .sign-space {
            height: 60px;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            display: inline-block;
            width: 80%;
        }
        hr.kop-hr {
            border-top: 3px solid #000; 
            border-bottom: 1px solid #000; 
            margin-top: 5px; 
            margin-bottom: 20px; 
            padding-bottom: 2px;
        }
        @media print {
            body { margin: 0; }
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
        $totalKeseluruhan = 0;
        $totalPph = 0;
        
        foreach($overtime->details as $detail) {
            $emp = $detail->employee;
            $golongan = $detail->golongan_fix ?? $emp->golongan ?? '-';
            
            $totalJam = 0;
            for($d = 1; $d <= $daysInMonth; $d++) {
                $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                if($val >= 2) {
                    $totalJam += $val;
                }
            }
            if($totalJam == 0) continue;
            
            // Pemetaan golongan -> tarif SBU terpusat & toleran format (SbuLembur::pickRate).
            if (!is_null($detail->rate_lembur_fix)) {
                $valLembur = $detail->rate_lembur_fix;
            } else {
                $valLembur = \App\Models\SbuLembur::pickRate($sbuRates, 'Uang Lembur', $golongan)?->besaran ?? 0;
            }
            $uangLembur = $totalJam * $valLembur;
            
            // PPh calculation
            $pphRate = 0;
            if (str_contains(strtoupper($golongan), 'III')) {
                $pphRate = 0.05;
            } elseif (str_contains(strtoupper($golongan), 'IV')) {
                $pphRate = 0.15;
            }
            $pph = $uangLembur * $pphRate;
            $diterima = $uangLembur - $pph;

            $totalPph += $pph;
            // Total Keseluruhan (Bruto/Sebelum dipotong pajak)
            $totalKeseluruhan += $uangLembur;
        }

        $tahun = $overtime->tahun;
        $namaPpk = $skpd->nama_ppk ?? '..................................';
        $nipPpk = $skpd->nip_ppk ?? '..................................';
        $namaPptk = $skpd->nama_pptk ?? '..................................';
        $nipPptk = $skpd->nip_pptk ?? '..................................';
        $namaBendahara = $skpd->nama_bendahara ?? '..................................';
        $nipBendahara = $skpd->nip_bendahara ?? '..................................';
        $namaPa = $skpd->kepala_skpd ?? '..................................';
        $nipPa = $skpd->nip_kepala ?? '..................................';
    @endphp

    <div class="row align-items-center">
        <div class="col-2 text-center">
            <img src="{{ asset('images/logo-bengkayang.png') }}" style="width:80px;" onerror="this.style.display='none'">
        </div>
        <div class="col-10 text-center">
            <div class="kop-pemerintah">PEMERINTAH KABUPATEN BENGKAYANG</div>
            <div class="kop-dinas">{{ strtoupper($skpd->nama ?? 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP') }}</div>
            <div class="kop-alamat">{{ $skpd->alamat ?? 'Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat' }}</div>
            <div class="kop-alamat">Situs : bengkayangkab.go.id</div>
        </div>
    </div>
    <hr class="kop-hr">

    <table class="main-table">
        <tr>
            <td style="width: 75%; padding: 15px; border-right: 1px solid #000;">
                <div class="title">K W I T A N S I</div>
                <div class="subtitle">Nomor : ..............................</div>

                <table style="width: 100%; margin-top: 10px;">
                    <tr>
                        <td style="width: 25%; vertical-align: top;">Telah terima dari</td>
                        <td style="width: 2%; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">BENDAHARA PENGELUARAN {{ strtoupper($skpd->nama ?? 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP') }} KABUPATEN BENGKAYANG</td>
                    </tr>
                    <tr>
                        <td>Kode Rekening</td>
                        <td>:</td>
                        <td>{{ $package->account->kode ?? '..............................' }}</td>
                    </tr>
                    <tr>
                        <td>Uang sejumlah</td>
                        <td>:</td>
                        <td><b>Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Terbilang</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="font-style: italic; font-weight: bold; vertical-align: top;">{{ \App\Helpers\Terbilang::make($totalKeseluruhan) }} Rupiah</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Guna Membayar</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">Belanja Uang Lembur pada Kegiatan {{ $package->activity->nama ?? '..............................' }} Subkegiatan {{ $package->subActivity->nama ?? '..............................' }} Bulan {{ $monthName }} Tahun {{ $tahun }}</td>
                    </tr>
                </table>

                <table style="width: 100%; margin-top: 30px;">
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%; text-align: center;">
                            Bengkayang, ..................... {{ $tahun }}<br>
                            Yang Menerima
                            <br><br><br><br>
                            <b>.................</b>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 25%; padding: 0;">
                <div style="padding: 10px; border-bottom: 1px solid #000;">
                    <div style="text-align: center; text-decoration: underline; margin-bottom: 5px;">Masuk Buku :</div>
                    Tanggal &nbsp;: ..........................<br><br>
                    No. BKU : ..........................
                </div>
                <div style="padding: 10px; border-bottom: 1px solid #000; line-height: 1.8;">
                    Perhitungan Pajak Yang<br>Harus Dibayar :<br>
                    PPN &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: -<br>
                    PPh 21 &nbsp;&nbsp;&nbsp;: Rp {{ number_format($totalPph, 0, ',', '.') }}<br>
                    PPh 22 &nbsp;&nbsp;&nbsp;: -<br>
                    PPh 23 &nbsp;&nbsp;&nbsp;: -
                </div>
                <div style="padding: 10px; text-align: center;">
                    Diperiksa Pada Tanggal :
                    <br><br><br><br>
                    <span class="dotted-line"></span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 0; border-top: 1px solid #000;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 33.33%; border-right: 1px solid #000; text-align: center; padding: 10px;">
                            DIBUAT OLEH :<br>
                            <b>Pejabat Pelaksana Teknis Kegiatan</b>
                            <div class="sign-space"></div>
                            <b><span style="text-decoration: underline;">{{ strtoupper($namaPptk) }}</span></b><br>
                            NIP. {{ $nipPptk }}
                        </td>
                        <td style="width: 33.33%; border-right: 1px solid #000; text-align: center; padding: 10px;">
                            MENGETAHUI / MENYETUJUI :<br>
                            <b>Pengguna Anggaran</b>
                            <div class="sign-space"></div>
                            <b><span style="text-decoration: underline;">{{ strtoupper($namaPa) }}</span></b><br>
                            NIP. {{ $nipPa }}
                        </td>
                        <td style="width: 33.33%; text-align: center; padding: 10px;">
                            LUNAS DIBAYAR :<br>
                            <b>Bendahara Pengeluaran</b>
                            <div class="sign-space"></div>
                            <b><span style="text-decoration: underline;">{{ strtoupper($namaBendahara) }}</span></b><br>
                            NIP. {{ $nipBendahara }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
