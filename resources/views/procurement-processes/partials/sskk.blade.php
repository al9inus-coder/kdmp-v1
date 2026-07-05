<table class="sskk-table">
    <tr>
        <td class="col-letter">A.</td>
        <td class="col-title">KORESPONDENSI</td>
        <td class="col-value">
            Alamat Para Pihak sebagai berikut
            <table class="inner-table mt-2">
                <tr><td class="inner-label">Satuan Kerja PPK</td><td class="col-colon">:</td><td>{{ $skpd->nama }} Kabupaten Bengkayang</td></tr>
                <tr><td class="inner-label">Nama</td><td class="col-colon">:</td><td>{{ $procurementPackage->nama_ppk }}</td></tr>
                <tr><td class="inner-label">Alamat</td><td class="col-colon">:</td><td>Jl. Guna Baru Trans Rangkang, Kel. Sebalo Kec. Bengkayang</td></tr>
                <tr><td class="inner-label">Telepon</td><td class="col-colon">:</td><td>{{ $procurementPackage->no_telp_ppk ?? '-' }}</td></tr>
                <tr><td class="inner-label">Website</td><td class="col-colon">:</td><td>bengkayangkab.go.id</td></tr>
                <tr><td class="inner-label">Faksimili</td><td class="col-colon">:</td><td>-</td></tr>
                <tr><td class="inner-label">e-mail</td><td class="col-colon">:</td><td>{{ $procurementPackage->email_ppk ?? '-' }}</td></tr>
            </table>

            <table class="inner-table mt-3">
                <tr><td class="inner-label">Penyedia</td><td class="col-colon">:</td><td>{{ strtoupper($process->nama_penyedia) }}</td></tr>
                <tr><td class="inner-label">Alamat</td><td class="col-colon">:</td><td>{{ $process->alamat_penyedia ?? '-' }}</td></tr>
                <tr><td class="inner-label">Telepon</td><td class="col-colon">:</td><td>-</td></tr>
                <tr><td class="inner-label">Website</td><td class="col-colon">:</td><td>-</td></tr>
                <tr><td class="inner-label">Faksimili</td><td class="col-colon">:</td><td>-</td></tr>
                <tr><td class="inner-label">e-mail</td><td class="col-colon">:</td><td>-</td></tr>
            </table>
        </td>
    </tr>

    <tr>
        <td class="col-letter">B.</td>
        <td class="col-title">WAKIL SAH PARA PIHAK</td>
        <td class="col-value">
            Wakil sah para pihak sebagai berikut :<br>
            <table class="inner-table mt-2">
                <tr><td class="inner-label" style="width: 150px;">Untuk PPK</td><td class="col-colon">:</td><td>{{ strtoupper($procurementPackage->nama_ppk) }}</td></tr>
                <tr><td class="inner-label">Untuk Penyedia</td><td class="col-colon">:</td><td>{{ strtoupper($process->nama_pic) }}</td></tr>
            </table>
        </td>
    </tr>

    <tr>
        <td class="col-letter">C.</td>
        <td class="col-title">JENIS KONTRAK</td>
        <td class="col-value">Kontrak {{ $procurementPackage->jenis_kontrak }}</td>
    </tr>

    <tr>
        <td class="col-letter">D.</td>
        <td class="col-title">TANGGAL BERLAKU KONTRAK</td>
        <td class="col-value">Kontrak mulai berlaku sejak : {{ optional($process->tanggal_surat_pesanan)->translatedFormat('d F Y') }} sd. {{ optional($process->tanggal_barang_diterima)->translatedFormat('d F Y') }}</td>
    </tr>

    <tr>
        <td class="col-letter">E.</td>
        <td class="col-title">WAKTU PELAKSANAAN</td>
        @php
            $durasiHari = 0;
            if ($process->tanggal_surat_pesanan && $process->tanggal_barang_diterima) {
                $durasiHari = $process->tanggal_surat_pesanan->diffInDays($process->tanggal_barang_diterima);
            }
        @endphp
        <td class="col-value">{{ $durasiHari }} ( {{ \App\Helpers\Terbilang::make($durasiHari) }} ) Hari Kalender</td>
    </tr>

    <tr>
        <td class="col-letter">F.</td>
        <td class="col-title">LOKASI PEKERJAAN</td>
        <td class="col-value">Kecamatan Bengkayang, Kabupaten Bengkayang</td>
    </tr>

    <tr>
        <td class="col-letter">G.</td>
        <td class="col-title">MASA PEMELIHARAAN</td>
        <td class="col-value">
            @if($procurementPackage->ada_garansi)
                {{ $procurementPackage->garansi_nilai }} {{ $procurementPackage->garansi_satuan }}
            @else
                -
            @endif
        </td>
    </tr>

    <tr>
        <td class="col-letter">H.</td>
        <td class="col-title">PEMBAYARAN TAGIHAN</td>
        <td class="col-value text-justify">Batas akhir waktu yang disepakati untuk penerbitan SPP oleh PPK untuk pembayaran tagihan angsuran adalah 14 hari kalender terhitung sejak tagihan dan kelengkapan dokumen penunjang yang tidak diperselisihkan diterima oleh PPK.</td>
    </tr>

    <tr>
        <td class="col-letter">I.</td>
        <td class="col-title">PENCAIRAN JAMINAN</td>
        <td class="col-value">-</td>
    </tr>

    <tr>
        <td class="col-letter">J.</td>
        <td class="col-title">TINDAKAN PENYEDIA YANG MENSYARATKAN PERSETUJUAN PPK</td>
        <td class="col-value text-justify">
            Tindakan lain oleh Penyedia yang memerlukan persetujuan PPK adalah apabila terjadi perubahan kontrak yang disebabkan adanya perbedaan antara kondisi dilapangan pada saat pelaksanaan dengan spesifikasi yang ditentukan dalam dokumen kontrak.<br><br>
            Tindakan lain oleh Penyedia yang memerlukan persetujuan Pengawas Pekerjaan adalah : Pemeriksaan pekerjaan yakni Pemeriksaaan Pekerjaan dan Pekerjaan Tambah Kurang.
        </td>
    </tr>

    <tr>
        <td class="col-letter">K.</td>
        <td class="col-title">KEPEMILIKAN DOKUMEN</td>
        <td class="col-value text-justify">Penyedia diperbolehkan menggunakan salinan dokumen dan piranti lunak yang dihasilkan dari pekerjaan ini dan Penyedia wajib menjaga kerahasiaan dokumen dan data yang digunakan dan dihasilkan dari pekerjaan ini.</td>
    </tr>

    <tr>
        <td class="col-letter">L.</td>
        <td class="col-title">FASILITAS</td>
        <td class="col-value">-</td>
    </tr>

    <tr>
        <td class="col-letter">M.</td>
        <td class="col-title">SUMBER PEMBIAYAAN</td>
        <td class="col-value">Kontrak pengadaan barang ini dibiayai oleh APBD Kabupaten Bengkayang Tahun {{ $procurementPackage->package->fiscalYear->tahun }}</td>
    </tr>

    <tr>
        <td class="col-letter">N.</td>
        <td class="col-title">PEMBAYARAN UANG MUKA</td>
        <td class="col-value">Pekerjaan ini tidak diberikan uang muka</td>
    </tr>

    <tr>
        <td class="col-letter">O.</td>
        <td class="col-title">PEMBAYARAN PRESTASI PEKERJAAN</td>
        <td class="col-value text-justify">
            Pembayaran prestasi pekerjaan dilakukan dengan cara : Sekaligus<br><br>
            Pembayaran berdasarkan cara tersebut diatas dilakukan dengan ketentuan sebagai berikut : Berdasarkan progres/kemajuan pelaksanaan pekerjaan.
        </td>
    </tr>

    <tr>
        <td class="col-letter">P.</td>
        <td class="col-title">PENYESUAIAN HARGA</td>
        <td class="col-value">Untuk Penyesuaian Harga digunakan indeks yang dikeluarkan oleh Pemerintah Kabupaten Bengkayang</td>
    </tr>

    <tr>
        <td class="col-letter">Q.</td>
        <td class="col-title">DENDA</td>
        <td class="col-value text-justify">Untuk Pekerjaan ini besar denda keterlambatan untuk setiap hari keterlambatan adalah 1/1000 (satu perseribu) dari harga Surat Pesanan (SP)</td>
    </tr>

    <tr>
        <td class="col-letter">R.</td>
        <td class="col-title">PENYELESAIAN PERSELISIHAN</td>
        <td class="col-value text-justify">Jika perselisihan Para Pihak mengenai pelaksanaan Kontrak tidak dapat diselesaikan secara damai maka Para Pihak menetapkan lembaga penyelesaian perselisihan tersebut di bawah sebagai Pemutus Sengketa Pengadilan Negeri Bengkayang</td>
    </tr>
</table>
