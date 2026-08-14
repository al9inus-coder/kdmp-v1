<?php

namespace App\Services\Pajak;

use App\Models\ProcurementPackage;
use App\Models\TarifPajak;

/**
 * SATU-SATUNYA sumber perhitungan pajak pengadaan.
 *
 * Sebelumnya rumusnya tertanam di cetakan BAP sebagai angka lepas: pembagi
 * 1.11 dan pengali 0.11 ditulis terpisah. Begitu tarif bisa diubah operator,
 * keduanya bisa berselisih — ubah PPN jadi 12% sementara pembaginya tetap
 * 1,11, dan seluruh angka BAP salah tanpa satu pun galat muncul.
 *
 * Di sini pembagi DITURUNKAN dari tarif yang sama dengan yang memotong:
 * DPP = nilai / (1 + persen/100). Satu angka, dua kegunaan.
 */
class PajakPengadaan
{
    /**
     * @return array{
     *   skema: string, dpp: float, konsumsi: float, labelKonsumsi: string,
     *   persenKonsumsi: float, pph: float, labelPph: string, persenPph: float,
     *   totalPotongan: float, jumlahBayar: float, nilaiKontrak: float
     * }
     */
    public static function hitung(ProcurementPackage $pp, $tarif = null): array
    {
        $tarif ??= TarifPajak::aktif()->get();

        $nilai = (float) ($pp->procurementProcess->nilai_kontrak ?? 0);
        $bayar = $pp->payment;
        $skema = self::skema($pp);

        // ── Pajak konsumsi (PPN atau pajak restoran) ──────────────────
        $jenisKonsumsi = SkemaPajak::jenisPajakKonsumsi($skema);
        $bekuKonsumsi = $skema === SkemaPajak::RESTORAN
            ? $bayar?->persen_restoran_fix
            : $bayar?->persen_ppn_fix;

        $persenKonsumsi = !is_null($bekuKonsumsi)
            ? (float) $bekuKonsumsi
            : (float) (TarifPajak::untukJenis($tarif, $jenisKonsumsi)?->persen ?? 0);

        // Nilai kontrak sudah termasuk pajak konsumsinya, jadi DPP diperoleh
        // dengan membaginya — memakai persen yang sama, bukan angka terpisah.
        $dpp = $persenKonsumsi > 0 ? $nilai / (1 + $persenKonsumsi / 100) : $nilai;
        $konsumsi = $dpp * $persenKonsumsi / 100;

        // ── Pajak penghasilan (PPh 22/23 atau PPh Final 4(2)) ─────────
        $jenisPph = SkemaPajak::jenisPajakPenghasilan($skema, $pp->package?->jenis_pengadaan);

        $barisPph = $jenisPph === TarifPajak::PPH4_2_KONSTRUKSI
            ? TarifPajak::untukKunci($tarif, $jenisPph, $bayar?->kualifikasi_pajak)
            : TarifPajak::untukJenis($tarif, $jenisPph);

        $persenPph = !is_null($bayar?->persen_pph_fix)
            ? (float) $bayar->persen_pph_fix
            : (float) ($barisPph?->persen ?? 0);

        $pph = $dpp * $persenPph / 100;

        $totalPotongan = $konsumsi + $pph;

        return [
            'skema' => $skema,
            'nilaiKontrak' => $nilai,
            'dpp' => $dpp,
            'konsumsi' => $konsumsi,
            'persenKonsumsi' => $persenKonsumsi,
            'labelKonsumsi' => self::label($jenisKonsumsi, $persenKonsumsi),
            'pph' => $pph,
            'persenPph' => $persenPph,
            'labelPph' => self::label($jenisPph, $persenPph, $barisPph?->kunci),
            'totalPotongan' => $totalPotongan,
            'jumlahBayar' => $nilai - $totalPotongan,
        ];
    }

    /**
     * Skema berlaku tiga lapis: pilihan manual pada pembayaran mengalahkan
     * bawaan rekening belanja, yang mengalahkan skema standar.
     */
    public static function skema(ProcurementPackage $pp): string
    {
        $pilihan = $pp->payment?->skema_pajak;
        if (SkemaPajak::valid($pilihan)) {
            return $pilihan;
        }

        $rekening = $pp->package?->account?->skema_pajak;
        if (SkemaPajak::valid($rekening)) {
            return $rekening;
        }

        return SkemaPajak::STANDAR;
    }

    private static function label(string $jenis, float $persen, ?string $kunci = null): string
    {
        $angka = rtrim(rtrim(number_format($persen, 2, ',', '.'), '0'), ',');

        $nama = match ($jenis) {
            TarifPajak::PPN => 'PPN',
            TarifPajak::PAJAK_RESTORAN => 'Pajak Restoran',
            TarifPajak::PPH22_BARANG => 'PPh 22',
            TarifPajak::PPH23_JASA => 'PPh 23',
            TarifPajak::PPH4_2_KONSTRUKSI => 'PPh Final Pasal 4(2)' . ($kunci ? ' — ' . $kunci : ''),
            default => $jenis,
        };

        return $nama . ' ' . $angka . '%';
    }
}
