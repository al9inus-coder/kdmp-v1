<div class="document-section" style="page-break-after: always; padding: 0;">
    <div style="text-align: center; margin-bottom: 10px;">
        <h4 style="margin-bottom: 5px; font-weight: bold;">RINGKASAN KONTRAK</h4>
    </div>

    <style>
        .rk-table {
            width: 100%; 
            line-height: 1.6; 
            border-collapse: collapse;
        }
        .rk-table, .rk-table th, .rk-table td {
            border: 1px solid black;
        }
    </style>
    <table class="rk-table">
        <tr>
            <td style="width: 30px; text-align: center; vertical-align: top; padding: 5px;">1.</td>
            <td style="width: 200px; vertical-align: top; padding: 5px;">Nomor dan Tanggal DPA</td>
            <td style="width: 10px; vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">DPA/A.1/1.04.2.11.2.10.04.0000/001/2026 tgl. 7 April 2026</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">2.</td>
            <td style="vertical-align: top; padding: 5px;">Kegiatan</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $procurementPackage->package->activity->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">3.</td>
            <td style="vertical-align: top; padding: 5px;">Sub Kegiatan</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $procurementPackage->package->subActivity->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">4.</td>
            <td style="vertical-align: top; padding: 5px;">Pekerjaan</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $procurementPackage->package->nama_paket }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">5.</td>
            <td style="vertical-align: top; padding: 5px;">Nomor SPK/ Kontrak</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $process->nomor_surat_pesanan }} tanggal {{ \Carbon\Carbon::parse($process->tanggal_surat_pesanan)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">6.</td>
            <td style="vertical-align: top; padding: 5px;">Nama Penyedia/ Pelaksana</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $process->nama_penyedia }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">7.</td>
            <td style="vertical-align: top; padding: 5px;">Alamat Penyedia/ Pelaksana</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $process->alamat_penyedia }}</td>
        </tr>
        <tr>
            <td rowspan="2" style="text-align: center; vertical-align: top; padding: 5px;">8.</td>
            <td style="vertical-align: top; padding: 5px; border-bottom: none;">Nilai SPK/ Kontrak</td>
            <td style="vertical-align: top; padding: 5px; border-right: none; border-bottom: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none; font-weight: bold; border-bottom: none;">Rp {{ number_format($process->nilai_kontrak, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 5px; border-top: none;"><em>(terbilang)</em></td>
            <td style="vertical-align: top; padding: 5px; border-right: none; border-top: none;"></td>
            <td style="vertical-align: top; padding: 5px; border-left: none; border-top: none; font-style: italic;">{{ \App\Helpers\Terbilang::make($process->nilai_kontrak) }} Rupiah</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">9.</td>
            <td style="vertical-align: top; padding: 5px;">Uraian dan Volume Kegiatan</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">100%</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">10.</td>
            <td style="vertical-align: top; padding: 5px;">Cara Pembayaran</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">Pembayaran Non Termijn</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">11.</td>
            <td style="vertical-align: top; padding: 5px;">Jangka Waktu Pelaksanaan</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            @php
                $start = \Carbon\Carbon::parse($process->tanggal_surat_pesanan);
                $end = \Carbon\Carbon::parse($process->tanggal_barang_diterima);
                $durasiHari = $start->diffInDays($end) ?: 1;
            @endphp
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $durasiHari }} hari kalender ({{ $start->translatedFormat('d F Y') }} s.d {{ $end->translatedFormat('d F Y') }})</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">12.</td>
            <td style="vertical-align: top; padding: 5px;">Tanggal Serah Terima Pekerjaan</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $payment->nomor_bast }} tanggal {{ \Carbon\Carbon::parse($payment->tanggal_bast)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">13.</td>
            <td style="vertical-align: top; padding: 5px;">Nomor Rekening Bank</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $process->nomor_rekening }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">14.</td>
            <td style="vertical-align: top; padding: 5px;">Atas Nama</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ strtoupper($process->nama_penyedia) }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">15.</td>
            <td style="vertical-align: top; padding: 5px;">Nama Bank</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $process->nama_bank }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">16.</td>
            <td style="vertical-align: top; padding: 5px;">NPWP Pelaksana</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">{{ $process->npwp_penyedia }}</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">17.</td>
            <td style="vertical-align: top; padding: 5px;">Jangka Waktu Pemeliharaan</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none;">-</td>
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: top; padding: 5px;">18.</td>
            <td style="vertical-align: top; padding: 5px;">Ketentuan Sanksi</td>
            <td style="vertical-align: top; padding: 5px; border-right: none;">:</td>
            <td style="vertical-align: top; padding: 5px; border-left: none; text-align: justify;">Apabila tidak dapat melaksanakan pekerjaan tersebut yang dibiayai dari sumber dana DAU sebagaimana mestinya, maka secara sepihak diputuskan hubungan kerja sama dan kontrak kerjasama dinyatakan batal demi hukum serta pelaksana diwajibkan mempertanggungjawabkan penggunaan dana yang telah digunakan.</td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 20px; text-align: center;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                Bengkayang, {{ \Carbon\Carbon::parse($payment->tanggal_ringkasan_kontrak)->translatedFormat('d F Y') }}<br>
                Pejabat Pembuat Komitmen<br>
                DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP KABUPATEN BENGKAYANG<br>
                <br><br><br>
                <strong><u>{{ $procurementPackage->nama_ppk }}</u></strong><br>
                NIP. {{ $procurementPackage->nip_ppk ?? '.......................' }}
            </td>
        </tr>
    </table>
</div>
