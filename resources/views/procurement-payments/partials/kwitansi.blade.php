<div class="document-section" style="page-break-after: always; padding: 20px;">
    <div style="border: 3px double #000; padding: 0;">
        <div style="border-bottom: 2px solid #000; padding: 10px;">
            <div style="font-weight: bold; line-height: 1.0;">
                PEMERINTAH DAERAH KABUPATEN BENGKAYANG<br>
                DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN<br>
                PERTANAHAN DAN LINGKUNGAN HIDUP<br>
                KABUPATEN BENGKAYANG
            </div>
        </div>
        
        <div style="text-align: center; border-bottom: 2px solid #000; padding: 15px 0;">
            <h2 style="margin: 0; font-weight: bold; letter-spacing: 2px; font-size: 24pt;">KWITANSI</h2>
        </div>
        
        <div style="padding: 20px;">
            <table style="width: 100%; line-height: 1.2; margin-bottom: 20px;">
                <tr>
                    <td style="width: 150px; vertical-align: top;">Sudah Terima Dari</td>
                    <td style="width: 10px; vertical-align: top;">:</td>
                    <td style="vertical-align: top;">BENDAHARA UMUM DAERAH</td>
                </tr>
                <tr>
                    <td style="vertical-align: middle;">Banyaknya Uang</td>
                    <td style="vertical-align: middle;">:</td>
                    <td style="vertical-align: middle;">
                        <div style="border: 2px solid #000; padding: 5px 15px; display: inline-block; font-weight: bold; font-size: 14pt; margin: 10px 0;">
                            <span style="display: inline-block; width: 40px;">Rp.</span> 
                            <span style="display: inline-block; text-align: right; width: 120px;">{{ number_format($process->nilai_kontrak, 0, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Terbilang</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top; font-weight: bold; font-style: italic;">
                        {{ \App\Helpers\Terbilang::make($process->nilai_kontrak) }} Rupiah
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Untuk Keperluan</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top; text-align: justify;">
                        Pembayaran untuk Pekerjaan {{ $procurementPackage->package->nama_paket }} berdasarkan Surat Pesanan No. {{ $process->nomor_surat_pesanan }} tanggal {{ \Carbon\Carbon::parse($process->tanggal_surat_pesanan)->translatedFormat('d F Y') }} kegiatan {{ $procurementPackage->package->activity->nama ?? '-' }} pada Dinas Perumahan Rakyat dan Kawasan Permukiman, Pertanahan dan Lingkungan Hidup Kabupaten Bengkayang Tahun Anggaran {{ $procurementPackage->package->fiscalYear->tahun ?? '2026' }}.
                    </td>
                </tr>
            </table>

            <table style="width: 100%; text-align: center; line-height: 1.2; margin-top: 20px;">
                <tr>
                    <td style="width: 50%;"></td>
                    <td style="width: 50%;">
                        Bengkayang, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ bulanIndonesia(\Carbon\Carbon::parse($payment->tanggal_kwitansi)->month) }} {{ \Carbon\Carbon::parse($payment->tanggal_kwitansi)->year }}
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top; padding-top: 20px;">
                        Dibuat/ Diajukan Oleh :<br>
                        <strong>PEJABAT PELAKSANA TEKNIS KEGIATAN</strong><br>
                        <br><br><br><br>
                        <u style="font-weight: bold;">{{ strtoupper($payment->nama_pptk) }}</u><br>
                        NIP. {{ $payment->nip_pptk }}
                    </td>
                    <td style="vertical-align: top; padding-top: 20px;">
                        Penyedia<br>
                        @if(strtolower(trim($process->jabatan_pic)) !== 'penyedia')
                            <strong>{{ strtoupper($process->nama_penyedia) }}</strong><br>
                        @else
                            <br>
                        @endif
                        <br><br><br><br><br>
                        <u style="font-weight: bold;">{{ strtoupper($process->nama_pic) }}</u><br>
                        @if(strtolower(trim($process->jabatan_pic)) !== 'penyedia')
                            {{ $process->jabatan_pic ?? 'Pemilik Toko' }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top: 40px;">
                        Mengetahui/ Menyetujui<br>
                        <strong>PENGGUNA ANGGARAN</strong><br>
                        DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN, PERTANAHAN DAN LINGKUNGAN HIDUP<br>
                        KABUPATEN BENGKAYANG<br>
                        <br><br><br><br>
                        <u style="font-weight: bold;">{{ strtoupper($procurementPackage->nama_ppk) }}</u><br>
                        NIP. {{ $procurementPackage->nip_ppk }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
