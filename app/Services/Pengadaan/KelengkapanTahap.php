<?php

namespace App\Services\Pengadaan;

use App\Models\ProcurementPayment;
use App\Models\ProcurementProcess;

/**
 * Satu-satunya penentu "tahap ini sudah lengkap atau belum".
 *
 * Sebelumnya aturan yang sama dihitung ulang di empat tempat — pemblokir di
 * controller, navigasi tahap, panel Selesaikan, dan daftar periksa dokumen —
 * sehingga pasti melenceng begitu salah satunya diubah.
 *
 * Setiap metode mengembalikan daftar LABEL yang masih kosong, bukan sekadar
 * true/false, supaya tampilan bisa menyebut persis apa yang kurang.
 */
class KelengkapanTahap
{
    /**
     * Syarat menutup tahap Pemilihan Penyedia (lanjut ke Pelaksanaan).
     *
     * Rekening, NPWP, dan nama bank sengaja TIDAK di sini: ketiganya hanya
     * dipakai dokumen tahap Pembayaran (BAP, Non-PKP, Ringkasan Kontrak),
     * jadi menuntutnya sejak awal hanya menahan pekerjaan tanpa manfaat.
     *
     * @return array<int, string>
     */
    public function pemilihan(?ProcurementProcess $process): array
    {
        if (! $process) {
            return ['Data surat pesanan'];
        }

        $kurang = $this->kosong($process, [
            'nomor_surat_pesanan' => 'Nomor surat pesanan',
            'tanggal_surat_pesanan' => 'Tanggal surat pesanan',
            'tanggal_barang_diterima' => 'Tanggal barang diterima',
            'nama_penyedia' => 'Nama penyedia',
            'alamat_penyedia' => 'Alamat penyedia',
            'nama_pic' => 'Nama PIC',
            'jabatan_pic' => 'Jabatan PIC',
        ]);

        if ((float) $process->nilai_kontrak <= 0) {
            $kurang[] = 'Nilai kontrak';
        }

        return $kurang;
    }

    /**
     * Syarat menutup tahap Pelaksanaan (lanjut ke Pembayaran).
     * Cukup bukti serah terima — dokumen tagihan belum tentu sudah terbit.
     *
     * @return array<int, string>
     */
    public function penyelesaian(?ProcurementPayment $payment): array
    {
        if (! $payment) {
            return ['Nomor BAST', 'Tanggal BAST'];
        }

        return $this->kosong($payment, [
            'nomor_bast' => 'Nomor BAST',
            'tanggal_bast' => 'Tanggal BAST',
        ]);
    }

    /**
     * Syarat menutup tahap Pembayaran (pengadaan selesai) — sekaligus syarat
     * dokumen pembayaran boleh dicetak, supaya berkas resmi tidak keluar
     * dengan bagian kosong.
     *
     * @return array<int, string>
     */
    public function pembayaran(?ProcurementProcess $process, ?ProcurementPayment $payment): array
    {
        $kurang = [];

        if (! $payment) {
            return ['Seluruh data penagihan'];
        }

        $kurang = $this->kosong($payment, [
            'nomor_invoice' => 'Nomor invoice',
            'tanggal_invoice' => 'Tanggal invoice',
            'nomor_bap' => 'Nomor BAP',
            'tanggal_bap' => 'Tanggal BAP',
            'nomor_kwitansi' => 'Nomor kwitansi',
            'tanggal_kwitansi' => 'Tanggal kwitansi',
            'tanggal_ringkasan_kontrak' => 'Tanggal ringkasan kontrak',
            'nama_pptk' => 'Nama PPTK',
            'nip_pptk' => 'NIP PPTK',
            'pangkat_golongan_pptk' => 'Pangkat/golongan PPTK',
        ]);

        // Tanggal surat Non-PKP hanya wajib bila memang dilampirkan.
        if ($payment->is_non_pkp && blank($payment->tanggal_non_pkp)) {
            $kurang[] = 'Tanggal surat Non-PKP';
        }

        // Data setoran penyedia — pindahan dari tahap Pemilihan Penyedia.
        $kurang = array_merge($kurang, $this->kosong($process, [
            'npwp_penyedia' => 'NPWP penyedia',
            'nama_bank' => 'Nama bank',
            'nomor_rekening' => 'Nomor rekening',
        ]));

        return $kurang;
    }

    /**
     * Apakah data pembayaran sudah pernah disentuh sama sekali.
     *
     * Dipakai halaman Pembayaran untuk memutuskan tampil form dulu atau
     * langsung tampilan biasa. Sengaja dibedakan dari "lengkap": begitu
     * pengguna menyimpan, ia berhak melihat tampilan biasa walau masih ada
     * yang kurang — kekurangannya tetap diberitahukan lewat peringatan.
     */
    public function pembayaranPernahDiisi(?ProcurementProcess $process, ?ProcurementPayment $payment): bool
    {
        if (! $payment) {
            return false;
        }

        $jejak = [
            $payment->nomor_invoice,
            $payment->nomor_bap,
            $payment->nomor_kwitansi,
            $payment->tanggal_ringkasan_kontrak,
            $payment->nama_pptk,
            $process?->npwp_penyedia,
            $process?->nama_bank,
            $process?->nomor_rekening,
        ];

        foreach ($jejak as $nilai) {
            if (filled($nilai)) {
                return true;
            }
        }

        return false;
    }

    public function pemilihanLengkap(?ProcurementProcess $process): bool
    {
        return $this->pemilihan($process) === [];
    }

    public function penyelesaianLengkap(?ProcurementPayment $payment): bool
    {
        return $this->penyelesaian($payment) === [];
    }

    public function pembayaranLengkap(?ProcurementProcess $process, ?ProcurementPayment $payment): bool
    {
        return $this->pembayaran($process, $payment) === [];
    }

    /** Kalimat siap tampil, mis. "Nomor invoice, Tanggal BAP, dan NPWP penyedia". */
    public function kalimat(array $kurang): string
    {
        if ($kurang === []) {
            return '';
        }

        if (count($kurang) === 1) {
            return $kurang[0];
        }

        $akhir = array_pop($kurang);

        return implode(', ', $kurang) . ', dan ' . $akhir;
    }

    /**
     * @param  array<string, string>  $peta  kolom => label
     * @return array<int, string>
     */
    private function kosong(mixed $model, array $peta): array
    {
        if (! $model) {
            return array_values($peta);
        }

        $kurang = [];

        foreach ($peta as $kolom => $label) {
            if (blank($model->{$kolom})) {
                $kurang[] = $label;
            }
        }

        return $kurang;
    }
}
