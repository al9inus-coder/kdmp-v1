<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi {{ $externalRecord->kwitansi_no }}</title>
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
<body>
    @php
        $tahun = $procurementPackage->package->fiscalYear->tahun ?? date('Y');
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
            <img src="{{ asset('images/logo-bengkayang.png') }}" style="width:80px;">
        </div>
        <div class="col-10 text-center">
            <div class="kop-pemerintah">PEMERINTAH KABUPATEN BENGKAYANG</div>
            <div class="kop-dinas">{{ strtoupper($skpd->nama ?? 'DINAS ..............................................') }}</div>
            <div class="kop-alamat">{{ $skpd->alamat ?? 'Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat' }}</div>
            <div class="kop-alamat">Situs : bengkayangkab.go.id</div>
        </div>
    </div>
    <hr class="kop-hr">

    <table class="main-table">
        <tr>
            <td style="width: 75%; padding: 15px; border-right: 1px solid #000;">
                <div class="title">K W I T A N S I</div>
                <div class="subtitle">Nomor : {{ $externalRecord->kwitansi_no ?? '..............................' }}</div>

                <table style="width: 100%; margin-top: 10px;">
                    <tr>
                        <td style="width: 20%; vertical-align: top;">Telah terima dari</td>
                        <td style="width: 2%; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">BENDAHARA PENGELUARAN ({{ strtoupper($namaBendahara) }}) {{ strtoupper($skpd->nama ?? '..................................') }} KABUPATEN BENGKAYANG</td>
                    </tr>
                    <tr>
                        <td>Kode Rekening</td>
                        <td>:</td>
                        <td>{{ $procurementPackage->package->account->kode ?? '..............................' }}</td>
                    </tr>
                    <tr>
                        <td>Uang sejumlah</td>
                        <td>:</td>
                        <td><b>Rp {{ number_format($externalRecord->nilai_kontrak, 0, ',', '.') }}</b></td>
                    </tr>
                    <tr>
                        <td>Terbilang</td>
                        <td>:</td>
                        <td style="font-style: italic; font-weight: bold;">{{ \App\Helpers\Terbilang::make($externalRecord->nilai_kontrak) }} Rupiah</td>
                    </tr>
                    <tr>
                        <td>Guna Membayar</td>
                        <td>:</td>
                        <td>{{ $procurementPackage->package->nama_paket ?? '..............................' }} Subkegiatan {{ $procurementPackage->package->subActivity->nama ?? '..............................' }} Tahun {{ $tahun }}</td>
                    </tr>
                </table>

                <table style="width: 100%; margin-top: 30px;">
                    <tr>
                        <td style="width: 50%;"></td>
                        <td style="width: 50%; text-align: center;">
                            Bengkayang, {{ $externalRecord->kwitansi_tgl ? \Carbon\Carbon::parse($externalRecord->kwitansi_tgl)->translatedFormat('d F Y') : '..................... '.date('Y') }}<br>
                            Yang Menerima
                            <br><br><br><br>
                            <b>.................</b>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 25%; padding: 0;">
                <div style="padding: 10px; border-bottom: 1px solid #000;">
                    <div style="text-align: right; text-decoration: underline; margin-bottom: 5px;">Masuk Buku :</div>
                    Tanggal &nbsp;: ..........................<br><br>
                    No. BKU : ..........................
                </div>
                <div style="padding: 10px; border-bottom: 1px solid #000; line-height: 1.8;">
                    Perhitungan Pajak Yang<br>Harus Dibayar :<br>
                    PPN &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ..........................<br>
                    PPh 21 &nbsp;&nbsp;&nbsp;: ..........................<br>
                    PPh 22 &nbsp;&nbsp;&nbsp;: ..........................<br>
                    PPh 23 &nbsp;&nbsp;&nbsp;: ..........................
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

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
