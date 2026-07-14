<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPPD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            border: 1px #D3D3D3 solid;
            background: white;
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        table.sppd-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.sppd-table th, table.sppd-table td {
            border: 1px solid black;
            padding: 5px;
            vertical-align: top;
        }
        .header-table { width: 100%; border-bottom: 3px solid black; margin-bottom: 15px; }
        .header-table td { padding: 5px; vertical-align: middle; }
        .logo { width: 80px; }
        
        @media print {
            .page {
                margin: 0;
                border: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
                page-break-after: always;
            }
            /* @page {
                size: A4;
                margin: 8mm;
            } */
        }
    </style>
</head>
<body onload="window.print()" onafterprint="window.close()">
    <!-- PAGE 1: DEPAN SPPD -->
    <div class="page">
        @php
            $isLuarDaerah = (strtolower($travelOrder->tipe_perjalanan) === 'luar_daerah' || strtolower($travelOrder->tipe_perjalanan) === 'luar daerah');
        @endphp

        <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px; border-bottom-style: double; border-bottom-width: 4px;">
            <tr>
                <td style="width: 100px; text-align: center; padding-bottom: 10px;">
                    <img src="{{ asset('images/logo-bengkayang.png') }}" alt="Logo" style="width: 80px;">
                </td>
                <td style="text-align: center; padding-bottom: 10px;">
                    <div style="font-size: 14pt; font-weight: normal;">PEMERINTAH KABUPATEN BENGKAYANG</div>
                    <div style="font-size: 15pt; font-weight: bold;">{{ strtoupper($skpd->nama ?? 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP') }}</div>
                    <div style="font-size: 11pt;">{{ $skpd->alamat ?? 'Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat' }}<br>Situs : bengkayangkab.go.id</div>
                </td>
            </tr>
        </table>

        <div style="text-align: right; margin-bottom: 1px; font-size: 10pt;">
            <table style="display: inline-block; text-align: left;">
                <tr><td>Lembar Ke</td><td>:</td><td>................</td></tr>
                <tr><td>Kode No</td><td>:</td><td>................</td></tr>
                <tr><td>Nomor</td><td>:</td><td>................</td></tr>
            </table>
        </div>

        <div class="text-center mb-2" style="font-size: 12pt">
            <u>SURAT PERJALANAN DINAS</u><br>
            (SPD)
        </div>

        @php
            $ketua = $travelOrder->personnels->first()->employee;
            $pengikut = $travelOrder->personnels->slice(1);
            $skpd = \App\Models\Skpd::first();

            $tglBerangkatStr = $travelOrder->tanggal_berangkat->translatedFormat('d F Y');
            $tglKembaliStr = $travelOrder->tanggal_kembali->translatedFormat('d F Y');
            $tglPelaksanaan = $tglBerangkatStr === $tglKembaliStr ? $tglBerangkatStr : $tglBerangkatStr . ' s.d. ' . $tglKembaliStr;
            $maksudDenganTanggal = $travelOrder->maksud_perjalanan . ', tanggal ' . $tglPelaksanaan;

            $adaPesawat = $travelOrder->personnels->where('jenis_kendaraan', 'pesawat')->count() > 0;
            $angkutan = $adaPesawat ? 'Transportasi darat dan udara' : 'Transportasi darat';
        @endphp

        <table class="sppd-table">
            <tr>
                <td style="width: 5%; text-align: center;">1</td>
                <td style="width: 30%;">PPK/PA/Pejabat yang berwenang</td>
                <td colspan="2" style="width: 55%;">{{ $skpd->nama_ppk ?? 'NAMA PPK' }}</td>
            </tr>
            <tr>
                <td class="text-center">2</td>
                <td>Nama/NIP Pegawai yang melaksanakan perjalanan dinas</td>
                <td colspan="2">{{ $ketua->nama }} <br> NIP. {{ $ketua->nip ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-center">3</td>
                <td>a. Pangkat dan Golongan<br>b. Jabatan/Instansi<br>c. Tingkat Biaya Perjalanan Dinas</td>
                <td colspan="2">a. {{ $ketua->golongan ?? '-' }}<br>b. {{ $ketua->jabatan ?? '-' }}<br>c.</td>
            </tr>
            <tr>
                <td class="text-center">4</td>
                <td>Maksud Perjalanan Dinas</td>
                <td colspan="2">{{ $maksudDenganTanggal }}</td>
            </tr>
            <tr>
                <td class="text-center">5</td>
                <td>Alat angkutan yang dipergunakan</td>
                <td colspan="2">{{ $angkutan }}</td>
            </tr>
            <tr>
                <td class="text-center">6</td>
                <td>a. Tempat Berangkat<br>b. Tempat Tujuan</td>
                <td colspan="2">a. Bengkayang<br>b. {{ $travelOrder->tempat_tujuan }}</td>
            </tr>
            <tr>
                <td class="text-center">7</td>
                <td>a. Lamanya Perjalanan Dinas<br>b. Tanggal berangkat<br>c. Tanggal harus kembali</td>
                <td colspan="2">
                    a. {{ $travelOrder->tanggal_berangkat->diffInDays($travelOrder->tanggal_kembali) + 1 }} Hari<br>
                    b. {{ $travelOrder->tanggal_berangkat->translatedFormat('d F Y') }}<br>
                    c. {{ $travelOrder->tanggal_kembali->translatedFormat('d F Y') }}
                </td>
            </tr>
            <tr>
                <td class="text-center" rowspan="2">8</td>
                <td>Pengikut : Nama</td>
                <td class="text-center" style="width: 27%;">Tanggal Lahir</td>
                <td class="text-center" style="width: 28%;">Keterangan</td>
            </tr>
            <tr>
                <td>
                    @if($pengikut->count() > 0)
                        @foreach($pengikut as $p)
                            {{ $loop->iteration }}. {{ $p->employee->nama }}<br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($pengikut->count() > 0)
                        @foreach($pengikut as $p)
                            {{ $p->employee->tanggal_lahir ? \Carbon\Carbon::parse($p->employee->tanggal_lahir)->translatedFormat('d F Y') : '-' }}<br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($pengikut->count() > 0)
                        @foreach($pengikut as $p)
                            <br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-center" rowspan="3" style="vertical-align: top;">9</td>
                <td style="border-bottom: none; padding-bottom: 0;">Pembebanan Anggaran</td>
                <td colspan="2" style="border-bottom: none; padding-bottom: 0;"></td>
            </tr>
            <tr>
                <td style="border-top: none; border-bottom: none; padding-top: 5px; padding-bottom: 0;">
                    <table style="width:100%; border-collapse:collapse; border:none; margin:0; padding:0;"><tr><td style="border:none; padding:0; width:15px; vertical-align:top;">a.</td><td style="border:none; padding:0; vertical-align:top;">Instansi</td></tr></table>
                </td>
                <td colspan="2" style="border-top: none; border-bottom: none; padding-top: 5px; padding-bottom: 0;">
                    <table style="width:100%; border-collapse:collapse; border:none; margin:0; padding:0;"><tr><td style="border:none; padding:0; width:15px; vertical-align:top;">a.</td><td style="border:none; padding:0; vertical-align:top;">{{ strtoupper($skpd->nama ?? 'NAMA DINAS') }}</td></tr></table>
                </td>
            </tr>
            <tr>
                <td style="border-top: none; padding-top: 5px;">
                    <table style="width:100%; border-collapse:collapse; border:none; margin:0; padding:0;"><tr><td style="border:none; padding:0; width:15px; vertical-align:top;">b.</td><td style="border:none; padding:0; vertical-align:top;">Akun</td></tr></table>
                </td>
                <td colspan="2" style="border-top: none; padding-top: 5px;">
                    <table style="width:100%; border-collapse:collapse; border:none; margin:0; padding:0;"><tr><td style="border:none; padding:0; width:15px; vertical-align:top;">b.</td><td style="border:none; padding:0; vertical-align:top;">{{ $package->account?->kode ?? '...' }} - {{ $package->account?->nama ?? '...' }}</td></tr></table>
                </td>
            </tr>
            <tr>
                <td class="text-center">10</td>
                <td>Keterangan lain-lain</td>
                <td colspan="2"></td>
            </tr>
        </table>

        <table style="width: 100%; margin-top: 30px;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    Dikeluarkan di Bengkayang<br>
                    pada tanggal {{ $travelOrder->tanggal_surat ? $travelOrder->tanggal_surat->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    <br>
                    PPK/PA/Pejabat yang berwenang,
                    <br><br><br><br>
                    <span style="text-decoration: underline; font-weight: bold;">{{ $skpd->nama_ppk ?? 'NAMA PPK' }}</span><br>
                    NIP. {{ $skpd->nip_ppk ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
