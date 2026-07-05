<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Perjalanan Dinas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
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
        
        .header-table { width: 100%; border-bottom: 3px solid black; margin-bottom: 15px; }
        .header-table td { padding: 5px; vertical-align: middle; }
        .logo { width: 80px; }
        
        .content-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .content-table td { padding: 4px; vertical-align: top; }
        
        .rincian-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .rincian-table td { padding: 3px; vertical-align: top; }
        
        .signature-table { width: 100%; margin-top: 50px; text-align: left; }
        .signature-table td { vertical-align: top; width: 33%; padding-right: 10px; }
        
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
            @page {
                size: A4;
                margin: 10mm;
            }
        }
    </style>
</head>
<body onload="window.print()" onafterprint="window.close()">
    @php
        function terbilang($angka) {
            $angka = abs($angka);
            $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
            $terbilang = "";
            if ($angka < 12) {
                $terbilang = " " . $baca[$angka];
            } else if ($angka < 20) {
                $terbilang = terbilang($angka - 10) . " Belas";
            } else if ($angka < 100) {
                $terbilang = terbilang($angka / 10) . " Puluh" . terbilang($angka % 10);
            } else if ($angka < 200) {
                $terbilang = " Seratus" . terbilang($angka - 100);
            } else if ($angka < 1000) {
                $terbilang = terbilang($angka / 100) . " Ratus" . terbilang($angka % 100);
            } else if ($angka < 2000) {
                $terbilang = " Seribu" . terbilang($angka - 1000);
            } else if ($angka < 1000000) {
                $terbilang = terbilang($angka / 1000) . " Ribu" . terbilang($angka % 1000);
            } else if ($angka < 1000000000) {
                $terbilang = terbilang($angka / 1000000) . " Juta" . terbilang($angka % 1000000);
            }
            return $terbilang;
        }
        
        $skpd = \App\Models\Skpd::first();
        $namaPA = $skpd->kepala_skpd ?? '.......................................................';
        $nipPA = $skpd->nip_kepala ?? '...............................................';
        $namaBendahara = $skpd->nama_bendahara ?? '.......................................................';
        $nipBendahara = $skpd->nip_bendahara ?? '...............................................';

        $isEselon2 = ($personnel->employee->kategori_biaya === 'Eselon II') || (stripos($personnel->employee->jabatan ?? '', 'kepala dinas') !== false);
        $isLuarDaerah = ($travelOrder->tipe_perjalanan === 'luar_daerah');

        $totalBiaya = ($personnel->uang_harian ?? 0) + ($personnel->biaya_transport ?? 0) + ($personnel->biaya_penginapan ?? 0);
        if ($isEselon2) $totalBiaya += ($personnel->biaya_representasi ?? 0);
        if ($isLuarDaerah) $totalBiaya += ($personnel->biaya_taksi ?? 0);
    @endphp

    <div class="page">
        <!-- KOP SURAT -->
        <table style="width: 100%; border-bottom: 2px solid #000; margin-bottom: 15px;">
            <tr>
                <td style="width: 100px; text-align: center; padding-bottom: 10px;">
                    <img src="{{ asset('images/logo-bengkayang.png') }}" alt="Logo" style="width: 80px;">
                </td>
                <td style="text-align: center; padding-bottom: 10px;">
                    <div style="font-size: 14pt; font-weight: normal;">PEMERINTAH KABUPATEN BENGKAYANG</div>
                    <div style="font-size: 15pt; font-weight: bold;">{{ strtoupper($skpd->nama) }}</div>
                    <div style="font-size: 10pt;">Jalan Guna Baru Rangkang Bengkayang, Telp. (0562) 441938<br>BENGKAYANG Kode Pos : 79282</div>
                </td>
            </tr>
        </table>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div>No. Kuitansi: ........................</div>
            <div>Tahun Anggaran: {{ \Carbon\Carbon::now()->format('Y') }}</div>
        </div>
        
        <div class="text-center font-weight-bold" style="font-size: 14pt; text-decoration: underline; margin-bottom: 30px;">
            KUITANSI
        </div>
        
        <table class="content-table">
            <tr>
                <td style="width: 25%;">Sudah terima dari</td>
                <td style="width: 2%;">:</td>
                <td style="width: 73%;">Bendahara Pengeluaran / Bendahara Pengeluaran Pembantu</td>
            </tr>
            <tr>
                <td>Sebesar</td>
                <td>:</td>
                <td class="font-weight-bold">Rp. {{ number_format($totalBiaya, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Terbilang</td>
                <td>:</td>
                <td style="font-style: italic;">{{ trim(terbilang($totalBiaya)) }} Rupiah</td>
            </tr>
            <tr>
                <td>Untuk pengeluaran</td>
                <td>:</td>
                <td style="text-align: justify;">
                    @php
                        $tglBerangkat = \Carbon\Carbon::parse($travelOrder->tanggal_berangkat);
                        $tglKembali = \Carbon\Carbon::parse($travelOrder->tanggal_kembali);
                        
                        $tglText = "tanggal " . $tglBerangkat->translatedFormat('d F Y');
                        if ($tglBerangkat->format('Y-m-d') != $tglKembali->format('Y-m-d')) {
                            $tglText .= " s.d. " . $tglKembali->translatedFormat('d F Y');
                        }
                    @endphp
                    Biaya perjalanan dinas dalam rangka melaksanakan kegiatan {{ $travelOrder->maksud_perjalanan ?? '......................................................' }} di {{ $travelOrder->tempat_tujuan }}, {{ $tglText }}
                    dengan rincian:
                    
                    <table class="rincian-table">
                        @php $i = 1; @endphp
                        <tr>
                            <td style="width: 50%;">{{ $i++ }}. uang harian</td>
                            <td style="width: 5%;">:</td>
                            <td style="width: 45%;">Rp. {{ number_format($personnel->uang_harian ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>{{ $i++ }}. biaya transportasi</td>
                            <td>:</td>
                            <td>Rp. {{ number_format($personnel->biaya_transport ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>{{ $i++ }}. biaya penginapan</td>
                            <td>:</td>
                            <td>Rp. {{ number_format($personnel->biaya_penginapan ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>{{ $i++ }}. uang representasi perjalanan dinas</td>
                            <td>:</td>
                            <td>Rp. {{ number_format($personnel->biaya_representasi ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>{{ $i++ }}. biaya taksi</td>
                            <td>:</td>
                            <td>Rp. {{ number_format($personnel->biaya_taksi ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <!-- ROW 1: Penerima di Kanan Atas -->
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    Bengkayang, ........ {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}<br>
                    Penerima,<br><br><br><br><br>
                    <span style="text-decoration: underline; font-weight: bold;">{{ $personnel->employee->nama }}</span><br>
                    NIP. {{ $personnel->employee->nip ?? '...............................................' }}
                </td>
            </tr>
        </table>
        
        <!-- ROW 2: Menyetujui PA dan Bendahara -->
        <table style="width: 100%; margin-top: 40px;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    Menyetujui<br>
                    Pengguna Anggaran,<br><br><br><br>
                    <span style="text-decoration: underline; font-weight: bold;">{{ $namaPA }}</span><br>
                    NIP. {{ $nipPA }}
                </td>
                <td style="width: 50%; text-align: center;">
                    Bendahara Pengeluaran,<br><br><br><br><br>
                    <span style="text-decoration: underline; font-weight: bold;">{{ $namaBendahara }}</span><br>
                    NIP. {{ $nipBendahara }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
