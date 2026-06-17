<div class="document-section" style="page-break-after: always; padding: 20px;">
    <div style="text-align: center; margin-bottom: 40px; margin-top: 30px;">
        <h3 style="text-decoration: underline; margin-bottom: 5px; font-weight: bold;">SURAT PERNYATAAN NON PKP</h3>
    </div>

    <p style="text-align: justify; line-height: 1.6;">
        Yang bertanda tangan di bawah ini:
    </p>

    <table style="width: 100%; margin-bottom: 30px; line-height: 1.6; margin-left: 20px;">
        <tr>
            <td style="width: 180px; vertical-align: top;">Nama</td>
            <td style="width: 10px; vertical-align: top;">:</td>
            <td style="vertical-align: top;"><strong>{{ strtoupper($process->nama_pic) }}</strong></td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Jabatan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $process->jabatan_pic ?? 'Pimpinan' }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Nama Perusahaan / Usaha</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $process->nama_penyedia }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Alamat</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $process->alamat_penyedia }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">NPWP</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $process->npwp_penyedia }}</td>
        </tr>
    </table>

    <p style="text-align: justify; line-height: 1.6;">
        Dengan ini menyatakan dengan sesungguhnya bahwa Perusahaan/Usaha kami <strong>BUKAN merupakan Pengusaha Kena Pajak (Non PKP)</strong> sebagaimana diatur dalam Undang-Undang Pajak Pertambahan Nilai dan ketentuan perundang-undangan perpajakan yang berlaku.
    </p>
    
    <p style="text-align: justify; line-height: 1.6;">
        Oleh karena itu, kami tidak dapat menerbitkan Faktur Pajak atas transaksi penjualan Barang/Jasa kepada Dinas Perumahan Rakyat dan Kawasan Permukiman, Pertanahan dan Lingkungan Hidup Kabupaten Bengkayang terkait pekerjaan <strong>{{ $procurementPackage->package->nama_paket }}</strong>.
    </p>

    <p style="text-align: justify; line-height: 1.6;">
        Apabila di kemudian hari ternyata pernyataan ini tidak benar, maka kami bersedia mempertanggungjawabkannya sesuai dengan ketentuan hukum dan peraturan perundang-undangan yang berlaku.
    </p>

    <p style="text-align: justify; line-height: 1.6; margin-top: 30px;">
        Demikian Surat Pernyataan ini kami buat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    <table style="width: 100%; margin-top: 50px; text-align: right;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%; text-align: center;">
                Bengkayang, {{ \Carbon\Carbon::parse($payment->tanggal_non_pkp)->translatedFormat('d F Y') }}<br>
                <strong>Yang Membuat Pernyataan,</strong><br>
                <div style="margin: 15px 0;">
                    <span style="border: 1px solid #000; padding: 15px 10px; display: inline-block; font-size: 0.8em; color: #666;">
                        Meterai<br>Rp 10.000,-
                    </span>
                </div>
                <strong><u>{{ strtoupper($process->nama_pic) }}</u></strong><br>
                {{ $process->jabatan_pic ?? 'Pimpinan' }}
            </td>
        </tr>
    </table>
</div>
