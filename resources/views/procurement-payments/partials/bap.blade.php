<div class="document-section" style="page-break-after: always; padding: 10px;">
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

    <div style="text-align: center; margin-bottom: 10px;">
        <div style="font-size: 12pt; text-decoration: underline; font-weight: bold;">BERITA ACARA PEMBAYARAN</div>
        <div style="margin-top: 5px;">{{ $payment->nomor_bap }}/BAP/{{ $procurementPackage->package->program->kode ?? '2.11.04' }}/PERKIMPLH-C</div>
    </div>

    <table style="width: 100%; margin-bottom: 20px; line-height: 1.2;">
        <tr>
            <td style="width: 120px; vertical-align: top;">Kegiatan</td>
            <td style="width: 10px; vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $procurementPackage->package->activity->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Sub Kegiatan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $procurementPackage->package->subActivity->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Pekerjaan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $procurementPackage->package->nama_paket }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Lokasi</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">Kabupaten Bengkayang</td>
        </tr>
    </table>

    @php
        $tglBap = \Carbon\Carbon::parse($payment->tanggal_bap);
        $days = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        $hariEn = $tglBap->format('l');
        $hari = $days[$hariEn] ?? $hariEn;

        $tanggalTerbilang = \App\Helpers\Terbilang::make($tglBap->format('d'));
        $bulan = bulanIndonesia($tglBap->month);
        $tahunTerbilang = \App\Helpers\Terbilang::make($tglBap->format('Y'));
    @endphp

    <p style="text-align: justify; line-height: 1.2;">
        Pada hari ini {{ $hari }} tanggal {{ $tanggalTerbilang }} bulan {{ $bulan }} tahun {{ $tahunTerbilang }}, kami yang bertanda tangan di bawah ini:
    </p>

    <table style="width: 100%; margin-bottom: 10px; line-height: 1.2;">
        <tr>
            <td style="width: 30px; vertical-align: top;">1.</td>
            <td style="width: 90px; vertical-align: top;">Nama</td>
            <td style="width: 10px; vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $payment->nama_pptk }}</td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">Jabatan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">Pejabat Pelaksana Teknis Kegiatan (PPTK) {{ $procurementPackage->package->activity->nama ?? '-' }} pada {{ $skpd->nama }} Kabupaten Bengkayang Tahun Anggaran {{ $procurementPackage->package->fiscalYear->tahun ?? '2026' }}</td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">Alamat</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">Jalan Guna Baru Trans Rangkang, Bengkayang, Kalimantan Barat, Kode Pos : 79211</td>
        </tr>
        <tr>
            <td colspan="4" style="padding-top: 10px;">yang selanjutnya di sebut sabagai PIHAK PERTAMA.</td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 10px; line-height: 1.2;">
        <tr>
            <td style="width: 30px; vertical-align: top;">2.</td>
            <td style="width: 90px; vertical-align: top;">Nama</td>
            <td style="width: 10px; vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $process->nama_pic }}</td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">Jabatan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ strtoupper($process->jabatan_pic ?? 'Pimpinan') }} pada {{ $process->nama_penyedia }}</td>
        </tr>
        <tr>
            <td></td>
            <td style="vertical-align: top;">Alamat</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $process->alamat_penyedia }}</td>
        </tr>
        <tr>
            <td colspan="4" style="padding-top: 10px;">yang selanjutnya di sebut sabagai PIHAK KEDUA.</td>
        </tr>
    </table>

    <p style="text-align: justify; line-height: 1.2;">
       A. Berdasarkan :
    </p>
    
    <table style="width: 100%; margin-bottom: 10px; line-height: 1.2; margin-left: 15px;">
        <tr>
            <td style="width: 20px; vertical-align: top;">1.</td>
            <td style="width: 150px; vertical-align: top;">Nomor DPA SKPD</td>
            <td style="width: 10px; vertical-align: top;">:</td>
            <td style="vertical-align: top;">DPA/A.1/1.04.2.11.2.10.04.0000/001/2026<br>tanggal 7 April 2026</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">2.</td>
            <td style="vertical-align: top;">Surat Pesanan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $process->nomor_surat_pesanan }}<br>tanggal {{ \Carbon\Carbon::parse($process->tanggal_surat_pesanan)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">3.</td>
            <td style="vertical-align: top;">Surat Tagihan/Invoice</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $payment->nomor_invoice }}<br>tanggal {{ optional($payment->tanggal_invoice)->translatedFormat('d F Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">4.</td>
            <td style="vertical-align: top;">Nilai Tagihan Pesanan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">
                Rp {{ number_format($process->nilai_kontrak, 2, ',', '.') }}<br>
                <em>(Terbilang: {{ \App\Helpers\Terbilang::make($process->nilai_kontrak) }} Rupiah)</em>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">5.</td>
            <td style="vertical-align: top;">BA. Serah Terima Barang</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $payment->nomor_bast }}<br>tanggal {{ \Carbon\Carbon::parse($payment->tanggal_bast)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">6.</td>
            <td style="vertical-align: top;">Sumber Dana</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">DAU</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">7.</td>
            <td style="vertical-align: top;">Lama Pekerjaan</td>
            <td style="vertical-align: top;">:</td>
            @php
                $start = \Carbon\Carbon::parse($process->tanggal_surat_pesanan);
                $end = \Carbon\Carbon::parse($process->tanggal_barang_diterima);
                $durasiHari = $start->diffInDays($end) ?: 1;
            @endphp
            <td style="vertical-align: top;">{{ $durasiHari }} Hari</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">8.</td>
            <td style="vertical-align: top;">Tanggal mulai s/d tanggal selesai</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $start->translatedFormat('d F') }} sd. {{ $end->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <p style="text-align: justify; line-height: 1.2;">
        B. Sesuai dengan Surat Tagihan Pesanan tersebut diatas, maka PIHAK KEDUA berhak menerima pembayaran pekerjaan selesai dari PIHAK PERTAMA dengan perincian sebagai berikut :
    </p>

    <style>
        .bap-table th, .bap-table td {
            border: 1px solid black !important;
            padding: 4px;
        }
    </style>
    <table class="bap-table" style="width: 100%; border-collapse: collapse; margin-bottom: 10px; line-height: 1.2;">
        <tr>
            <th rowspan="2" style="width: 30px; text-align: center; font-weight: normal;">No</th>
            <th rowspan="2" style="text-align: center; font-weight: normal;">Uraian</th>
            <th colspan="2" style="text-align: center; font-weight: normal;">Sumber Dana</th>
            <th rowspan="2" style="text-align: center; font-weight: normal;">Jumlah</th>
        </tr>
        <tr>
            <th style="text-align: center; width: 15%; font-weight: normal;">DAK</th>
            <th style="text-align: center; width: 25%; font-weight: normal;">DAU</th>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top;">1.</td>
            <td>Perhitungan Pembayaran :<br>a. Nilai Tagihan</td>
            <td></td>
            <td>Rp. <span style="float: right;">{{ number_format($process->nilai_kontrak, 0, ',', '.') }}</span></td>
            <td>Rp. <span style="float: right;">{{ number_format($process->nilai_kontrak, 0, ',', '.') }}</span></td>
        </tr>
        @php
            // Hitung DPP
            $dpp = $process->nilai_kontrak / 1.11;
            // PPN
            $ppn = $dpp * 0.11;
            // PPh (Barang = 1.5%, selain Barang = 2%)
            $jenisPengadaan = strtolower($procurementPackage->package->jenis_pengadaan);
            $isBarang = str_contains($jenisPengadaan, 'barang');
            $tarifPph = $isBarang ? 0.015 : 0.02;
            $teksPph = $isBarang ? 'PPh 22 1,5%' : 'PPh 23 2%';
            $pph = $dpp * $tarifPph;
            
            $totalPotongan = $pph + $ppn;
            $jumlahBayar = $process->nilai_kontrak - $totalPotongan;
        @endphp
        <tr>
            <td style="text-align: center; vertical-align: top;">2.</td>
            <td>Potongan-potongan<br>a. Pajak-Pajak<br>&nbsp;&nbsp;&nbsp;{{ $teksPph }}<br>&nbsp;&nbsp;&nbsp;PPN 11%<br>&nbsp;&nbsp;&nbsp;Retensi</td>
            <td></td>
            <td>
                <br>
                Rp. <span style="float: right;">{{ number_format($pph, 0, ',', '.') }}</span><br>
                Rp. <span style="float: right;">{{ number_format($ppn, 0, ',', '.') }}</span><br>
            </td>
            <td>
                <br>
                Rp. <span style="float: right;">{{ number_format($pph, 0, ',', '.') }}</span><br>
                Rp. <span style="float: right;">{{ number_format($ppn, 0, ',', '.') }}</span><br>
            </td>
        </tr>
        <tr>
            <td style="text-align: center;">3.</td>
            <td>Jumlah Pembayaran</td>
            <td></td>
            <td></td>
            <td>Rp. <span style="float: right;">{{ number_format($jumlahBayar, 0, ',', '.') }}</span></td>
        </tr>
    </table>

    <p style="text-align: justify; line-height: 1.2; margin-bottom: 20px;">
        Terbilang : <strong><em>{{ \App\Helpers\Terbilang::make($jumlahBayar) }} Rupiah</em></strong>
    </p>

    <p style="text-align: justify; line-height: 1.2;">
        Kedua Belah Pihak sepakat atas jumlah pembayaran tersebut di atas di bayarkan ke Rekening Giro PIHAK KEDUA bertindak atas nama {{ strtoupper($process->nama_penyedia) }} Nomor : {{ $process->nomor_rekening }} pada {{ $process->nama_bank }}
    </p>

    <p style="text-align: justify; line-height: 1.2; margin-top: 20px;">
        Demikian Berita Acara Pembayaran ini dibuat dalam rangkap secukupnya untuk dipergunakan sebagaimana mestinya.
    </p>

    <table style="width: 100%; margin-top: 30px; text-align: center; line-height: 1.2;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <strong>PIHAK KEDUA</strong><br>
                {{ $process->nama_penyedia }}<br>
                <br><br><br><br>
                <strong><u>{{ $process->nama_pic }}</u></strong><br>
                {{ $process->jabatan_pic ?? 'Pimpinan' }}
            </td>
            <td style="width: 50%; vertical-align: top;">
                <strong>PIHAK PERTAMA</strong><br>
                PEJABAT PELAKSANA TEKNIS KEGIATAN<br>
                <br><br><br><br>
                <strong><u>{{ $payment->nama_pptk }}</u></strong><br>
                NIP. {{ $payment->nip_pptk }}
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 30px; text-align: center; line-height: 1.2;">
        <tr>
            <td style="width: 100%;">
                MENGETAHUI/ MENYETUJUI :<br>
                PENGGUNA ANGGARAN<br>
                {{ $skpd->singkatan ?? $skpd->nama }}<br>
                <br><br><br><br>
                <u style="font-weight: bold;">{{ $procurementPackage->nama_ppk }}</u><br>
                NIP. {{ $procurementPackage->nip_ppk }}
            </td>
        </tr>
    </table>
</div>
