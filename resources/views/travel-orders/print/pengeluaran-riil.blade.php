<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pengeluaran Riil</title>
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
            padding: 15mm;
            border: 1px #D3D3D3 solid;
            background: white;
            box-sizing: border-box;
        }
        .identitas-table { border-collapse: collapse; margin-left: 30px; }
        .identitas-table td { padding: 1px 4px; vertical-align: top; }

        .riil-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .riil-table th, .riil-table td { border: 1px solid #000; padding: 5px 8px; }
        .riil-table th { font-weight: bold; text-align: center; }
        .riil-table .row-kosong td { border-left: 1px solid #000; border-right: 1px solid #000; border-top: none; border-bottom: none; height: 24px; }

        .signature-table { width: 100%; margin-top: 45px; }
        .signature-table td { width: 50%; vertical-align: top; padding: 0 20px; }

        /* Jendela popup langsung memunculkan dialog print lalu menutup sendiri,
           jadi scrollbar halaman di belakang dialog disembunyikan agar tidak
           tampak dobel dengan scrollbar panel preview. */
        @media screen {
            html, body { overflow: hidden; }
        }

        @media print {
            .page {
                margin: 0;
                border: initial;
                width: initial;
                min-height: initial;
                box-shadow: initial;
                background: initial;
            }
            /* Break hanya di antara halaman, bukan setelah yang terakhir,
               supaya tidak muncul halaman kosong ekstra. */
            .page:not(:last-child) {
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
        $personnels = $personnels ?? collect([$personnel]);
        $dots = fn ($len = 50) => str_repeat('.', $len);

        $skpd = \App\Models\Skpd::first();
        $namaPpk = $skpd->nama_ppk ?? '...........................................';
        $nipPpk = $skpd->nip_ppk ?? '';

        if (!function_exists('terbilang')) {
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
        }
    @endphp

    @foreach($personnels as $personnel)
        @php
            $employee = $personnel->employee;
            $nomorSpd = $personnel->nomor_sppd ?: '................';
            $tanggalSpd = $travelOrder->tanggal_surat
                ? \Carbon\Carbon::parse($travelOrder->tanggal_surat)->translatedFormat('d F Y')
                : '................';

            // Baris pengeluaran riil: komponen biaya yang ditandai pada SPJ.
            $items = [];
            if ($personnel->transport_riil && $personnel->biaya_transport > 0) {
                $items[] = ['uraian' => 'Biaya transportasi (tidak diperoleh bukti pengeluaran)', 'jumlah' => $personnel->biaya_transport];
            }
            if ($personnel->taksi_riil && ($personnel->biaya_taksi ?? 0) > 0) {
                $items[] = ['uraian' => 'Biaya taksi (tidak diperoleh bukti pengeluaran)', 'jumlah' => $personnel->biaya_taksi];
            }
            if ($personnel->penginapan_riil && $personnel->biaya_penginapan > 0) {
                $items[] = ['uraian' => 'Biaya penginapan sebesar 30% dari tarif hotel sesuai SBU (tidak menginap di hotel)', 'jumlah' => $personnel->biaya_penginapan];
            }
            $totalRiil = collect($items)->sum('jumlah');
            $barisKosong = max(0, 8 - count($items));
        @endphp

        <div class="page">
            <div style="text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 30px;">
                DAFTAR PENGELUARAN RIIL
            </div>

            <p style="margin-bottom: 4px;">Yang bertandatangan di bawah ini :</p>
            <table class="identitas-table">
                <tr>
                    <td style="width: 90px;">Nama</td>
                    <td>:</td>
                    <td>{{ $employee->nama ?? $dots(55) }}</td>
                </tr>
                <tr>
                    <td>NIP</td>
                    <td>:</td>
                    <td>{{ $employee->nip ?? $dots(55) }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>{{ $employee->jabatan ?? $dots(55) }}</td>
                </tr>
            </table>

            <p style="text-align: justify; margin-top: 14px;">
                Berdasarkan Surat Perjalanan Dinas (SPD) Nomor {{ $nomorSpd }} Tanggal {{ $tanggalSpd }},
                dengan ini kami menyatakan dengan sesungguhnya bahwa :
            </p>

            <table style="border-collapse: collapse; margin-top: 10px;">
                <tr>
                    <td style="vertical-align: top; padding-right: 6px;">1.</td>
                    <td>Biaya di bawah ini yang tidak dapat diperoleh bukti &ndash; bukti pengeluarannya meliputi :</td>
                </tr>
            </table>

            <table class="riil-table">
                <tr>
                    <th style="width: 8%;">No</th>
                    <th style="width: 67%;">Uraian</th>
                    <th style="width: 25%;">Jumlah</th>
                </tr>
                @foreach ($items as $no => $item)
                    <tr class="row-kosong">
                        <td style="text-align: center;">{{ $no + 1 }}</td>
                        <td>{{ $item['uraian'] }}</td>
                        <td style="text-align: right;">Rp. {{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @for ($i = 0; $i < $barisKosong; $i++)
                    <tr class="row-kosong">
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
                <tr>
                    <td style="border-top: 1px solid #000;"></td>
                    <td style="border-top: 1px solid #000; text-align: right; font-weight: bold;">Jumlah</td>
                    <td style="border-top: 1px solid #000; text-align: right; font-weight: bold;">
                        {{ $totalRiil > 0 ? 'Rp. ' . number_format($totalRiil, 0, ',', '.') : '' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="3">Terbilang : @if($totalRiil > 0)<span style="font-style: italic;">{{ trim(terbilang($totalRiil)) }} Rupiah</span>@endif</td>
                </tr>
            </table>

            <table style="border-collapse: collapse; margin-top: 14px;">
                <tr>
                    <td style="vertical-align: top; padding-right: 6px;">2.</td>
                    <td style="text-align: justify;">
                        Jumlah uang tersebut pada angka 1 di atas benar-benar dikeluarkan untuk pelaksanaan
                        Perjalanan Dinas dimaksud dan apabila di kemudian hari terdapat kelebihan atas
                        pembayaran, kami bersedia untuk menyetorkan kelebihan tersebut ke Kas Daerah.
                    </td>
                </tr>
            </table>

            <p style="text-align: justify; margin-top: 14px;">
                Demikian pernyataan ini kami buat dengan sebenarnya, untuk dipergunakan sebagaimana mestinya.
            </p>

            <table class="signature-table">
                <tr>
                    <td style="text-align: center;">
                        Mengetahui/Menyetujui<br>
                        Pejabat Pembuat Komitmen,<br><br><br><br><br>
                        <span style="text-decoration: underline; font-weight: bold;">{{ $namaPpk }}</span><br>
                        NIP. {{ $nipPpk }}
                    </td>
                    <td style="text-align: center;">
                        Bengkayang, ........ {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}<br>
                        Pelaksana SPD,<br><br><br><br><br>
                        <span style="text-decoration: underline; font-weight: bold;">{{ $employee->nama ?? '(...........................................)' }}</span><br>
                        NIP. {{ $employee->nip ?? '' }}
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>
