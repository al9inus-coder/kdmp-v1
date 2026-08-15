<?php

namespace App\Services\Pajak;

use App\Models\ProcurementPackage;
use App\Models\TarifPajak;

/**
 * SATU-SATUNYA sumber perhitungan pajak pengadaan.
 *
 * Pembagian perannya:
 *
 *   usulan()  — sistem menebak, dari rekening belanja dan jenis pengadaan.
 *               Hanya dipakai selama pembayaran belum pernah disimpan.
 *   hitung()  — menyajikan yang tersimpan. Untuk pembayaran yang sudah punya
 *               baris pajak, angkanya dibaca apa adanya, bukan dihitung ulang.
 *   bekukan() — menulis keputusan user jadi baris pajak beserta rupiahnya.
 *
 * Yang dibekukan bukan hanya persennya, tapi rupiahnya. BAP mencatat apa yang
 * terjadi pada tanggalnya; mengubah tarif lewat /admin/pajak MAUPUN mengubah
 * nilai kontrak tidak boleh menulis ulang dokumen yang sudah ditandatangani.
 */
class PajakPengadaan
{
    public const DASAR_BRUTO = 'bruto';
    public const DASAR_SETELAH_PPN = 'setelah_ppn';
    public const DASAR_SETELAH_PBJT = 'setelah_pbjt';

    /**
     * Pajak yang boleh dipilih user, beserta urutan tampilnya di dokumen.
     */
    public static function pilihanJenis(): array
    {
        return [
            TarifPajak::PPN => 'PPN',
            TarifPajak::PAJAK_RESTORAN => 'PBJT / Pajak Restoran',
            TarifPajak::PPH22_BARANG => 'PPh 22 — Barang',
            TarifPajak::PPH23_JASA => 'PPh 23 — Jasa',
            TarifPajak::PPH4_2_KONSTRUKSI => 'PPh Final Pasal 4(2) — Konstruksi',
        ];
    }

    /**
     * Label sengaja pendek: ia muncul di kolom sempit pada daftar pajak, dan
     * label panjang terpotong jadi "Setelah dikuran…" yang justru mengaburkan
     * pajak mana yang dikurangkan.
     */
    public static function pilihanDasar(): array
    {
        return [
            self::DASAR_BRUTO => 'Bruto (nilai penuh)',
            self::DASAR_SETELAH_PPN => 'Setelah PPN',
            self::DASAR_SETELAH_PBJT => 'Setelah PBJT',
        ];
    }

    /**
     * Aturan validasi daftar pajak — dipakai kedua controller pembayaran dan
     * endpoint pratinjau, supaya ketiganya tidak bisa berselisih.
     */
    public static function aturanValidasi(): array
    {
        return [
            'pajak' => 'nullable|array|max:5',
            'pajak.*.jenis' => 'required|string|in:' . implode(',', array_keys(self::pilihanJenis())),
            'pajak.*.persen' => 'required|numeric|min:0|max:100',
            'pajak.*.dasar' => 'required|string|in:' . implode(',', array_keys(self::pilihanDasar())),
            'pajak.*.kunci' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array{
     *   nilaiKontrak: float, beku: bool, baris: array, totalPotongan: float,
     *   jumlahBayar: float, dpp: float, konsumsi: float, pph: float,
     *   labelKonsumsi: string, labelPph: string, persenPph: float, skema: string
     * }
     */
    public static function hitung(ProcurementPackage $pp, $tarif = null): array
    {
        $bayar = $pp->payment;
        $tersimpan = $bayar?->pajaks;

        // Penandanya nilai_kontrak_fix, BUKAN "ada barisnya" — sebab daftar
        // pajak yang kosong adalah keputusan yang sah: user memang tidak
        // memungut apa pun. Memakai jumlah baris sebagai penanda membuat
        // pembayaran yang pajaknya dihapus kembali memungut usulan sistem.
        $beku = !is_null($bayar?->nilai_kontrak_fix);

        $nilai = $beku
            ? (float) $bayar->nilai_kontrak_fix
            : (float) ($pp->procurementProcess->nilai_kontrak ?? 0);

        $baris = $beku
            ? collect($tersimpan ?? [])->map(fn ($p) => [
                'jenis' => $p->jenis,
                'kunci' => $p->kunci,
                'persen' => (float) $p->persen,
                'dasar' => $p->dasar,
                'nilaiDasar' => (float) $p->nilai_dasar,
                'nominal' => (float) $p->nominal,
            ])->all()
            : self::nilaiBaris(self::usulan($pp, $tarif), $nilai);

        foreach ($baris as $i => $b) {
            $baris[$i]['label'] = self::label($b['jenis'], $b['persen'], $b['kunci']);
        }

        $totalPotongan = array_sum(array_column($baris, 'nominal'));

        return array_merge([
            'skema' => $bayar?->skema_pajak ?: self::skema($pp),
            'nilaiKontrak' => $nilai,
            'beku' => (bool) $beku,
            'baris' => $baris,
            'totalPotongan' => $totalPotongan,
            'jumlahBayar' => $nilai - $totalPotongan,
        ], self::turunanLama($baris, $nilai));
    }

    /**
     * Kunci lama dipertahankan supaya cetakan dan layar yang belum diubah tetap
     * jalan. "Konsumsi" = PPN atau PBJT, "pph" = sisanya.
     */
    private static function turunanLama(array $baris, float $nilai): array
    {
        $konsumsi = array_values(array_filter($baris, fn ($b) => in_array(
            $b['jenis'], [TarifPajak::PPN, TarifPajak::PAJAK_RESTORAN], true)));
        $pph = array_values(array_filter($baris, fn ($b) => !in_array(
            $b['jenis'], [TarifPajak::PPN, TarifPajak::PAJAK_RESTORAN], true)));

        return [
            'dpp' => $konsumsi[0]['nilaiDasar'] ?? ($pph[0]['nilaiDasar'] ?? $nilai),
            'konsumsi' => array_sum(array_column($konsumsi, 'nominal')),
            'persenKonsumsi' => (float) ($konsumsi[0]['persen'] ?? 0),
            'labelKonsumsi' => $konsumsi[0]['label'] ?? 'Tanpa pajak konsumsi',
            'pph' => array_sum(array_column($pph, 'nominal')),
            'persenPph' => (float) ($pph[0]['persen'] ?? 0),
            'labelPph' => $pph[0]['label'] ?? 'Tanpa PPh',
        ];
    }

    /**
     * Usulan sistem: dari skema (rekening belanja) dan jenis pengadaan, dengan
     * tarif dan dasar bawaan dari Master Pajak. User bebas menimpanya.
     */
    public static function usulan(ProcurementPackage $pp, $tarif = null): array
    {
        $tarif ??= TarifPajak::aktif()->get();
        $bayar = $pp->payment;
        $skema = $bayar?->skema_pajak ?: self::skema($pp);

        $usul = [];

        $jenisKonsumsi = SkemaPajak::jenisPajakKonsumsi($skema);
        if ($b = TarifPajak::untukJenis($tarif, $jenisKonsumsi)) {
            $usul[] = self::dariTarif($b, null);
        }

        // Dasar bawaan di Master Pajak ditulis "setelah_ppn" karena skema
        // standar yang paling umum. Pada skema restoran tidak ada baris PPN,
        // sehingga dasar itu menggantung dan diam-diam jatuh ke nilai kontrak
        // penuh — PPh 23 jadi dihitung dari bruto tanpa ada yang meminta.
        $samakanDasar = fn (string $dasar) => $jenisKonsumsi === TarifPajak::PAJAK_RESTORAN
            && $dasar === self::DASAR_SETELAH_PPN
                ? self::DASAR_SETELAH_PBJT
                : $dasar;

        $jenisPph = self::jenisPphTersimpan($bayar?->jenis_pph)
            ?? self::jenisPphTurunan($pp, $skema);

        $barisPph = $jenisPph === TarifPajak::PPH4_2_KONSTRUKSI
            ? TarifPajak::untukKunci($tarif, $jenisPph, $bayar?->kualifikasi_pajak)
            : TarifPajak::untukJenis($tarif, $jenisPph);

        if ($barisPph) {
            $b = self::dariTarif($barisPph, $barisPph->kunci);
            $b['dasar'] = $samakanDasar($b['dasar']);
            $usul[] = $b;
        }

        return $usul;
    }

    private static function dariTarif(TarifPajak $t, ?string $kunci): array
    {
        return [
            'jenis' => $t->jenis,
            'kunci' => $kunci,
            'persen' => (float) $t->persen,
            'dasar' => self::dasarValid($t->dasar) ?? self::DASAR_BRUTO,
        ];
    }

    /**
     * Rupiah tiap baris. Dasar sebuah baris menyebut pajak mana yang
     * dikeluarkan lebih dulu; bila baris itu tidak ada, dasarnya jatuh ke nilai
     * kontrak — menghapus PPN tidak membuat baris lain menggantung.
     */
    public static function nilaiBaris(array $baris, float $nilai): array
    {
        $persenDari = function (string $jenis) use ($baris) {
            foreach ($baris as $b) {
                if ($b['jenis'] === $jenis) {
                    return (float) $b['persen'];
                }
            }
            return 0.0;
        };

        foreach ($baris as $i => $b) {
            $pembagi = match ($b['dasar']) {
                self::DASAR_SETELAH_PPN => $persenDari(TarifPajak::PPN),
                self::DASAR_SETELAH_PBJT => $persenDari(TarifPajak::PAJAK_RESTORAN),
                default => 0.0,
            };

            $dasar = $pembagi > 0 ? $nilai / (1 + $pembagi / 100) : $nilai;

            $baris[$i]['nilaiDasar'] = $dasar;
            $baris[$i]['nominal'] = $dasar * (float) $b['persen'] / 100;
        }

        return $baris;
    }

    /**
     * Kunci keputusan user jadi baris pajak beserta rupiahnya.
     *
     * @param  array|null  $pilihan  larik {jenis, kunci, persen, dasar}; null =
     *                               pakai usulan sistem
     */
    public static function bekukan(ProcurementPackage $pp, ?array $pilihan = null): void
    {
        $bayar = $pp->payment;
        if (!$bayar) {
            return;
        }

        $nilai = (float) ($pp->procurementProcess->nilai_kontrak ?? 0);
        $baris = self::nilaiBaris(self::rapikan($pilihan ?? self::usulan($pp)), $nilai);

        $bayar->nilai_kontrak_fix = $nilai;
        $bayar->skema_pajak = $bayar->skema_pajak ?: self::skema($pp);

        // jenis_pph tinggal catatan prasetel; yang menghitung adalah baris.
        $pphPertama = collect($baris)->first(fn ($b) => !in_array(
            $b['jenis'], [TarifPajak::PPN, TarifPajak::PAJAK_RESTORAN], true));
        $bayar->jenis_pph = $pphPertama['jenis'] ?? null;
        $bayar->kualifikasi_pajak = $pphPertama['kunci'] ?? null;
        $bayar->save();

        $bayar->pajaks()->delete();
        foreach ($baris as $urutan => $b) {
            $bayar->pajaks()->create([
                'jenis' => $b['jenis'],
                'kunci' => $b['kunci'],
                'persen' => $b['persen'],
                'dasar' => $b['dasar'],
                'nilai_dasar' => $b['nilaiDasar'],
                'nominal' => $b['nominal'],
                'urutan' => $urutan,
            ]);
        }

        $bayar->load('pajaks');
    }

    /**
     * Buang baris yang jenisnya tidak dikenali dan jenis yang muncul dua kali —
     * satu pajak hanya boleh sekali per pembayaran.
     */
    public static function labelBaris(array $baris): string
    {
        return self::label($baris['jenis'], (float) $baris['persen'], $baris['kunci'] ?? null);
    }

    public static function rapikan(array $pilihan): array
    {
        $dikenali = array_keys(self::pilihanJenis());
        $bersih = [];
        $sudah = [];

        foreach ($pilihan as $b) {
            $jenis = $b['jenis'] ?? null;
            if (!in_array($jenis, $dikenali, true) || in_array($jenis, $sudah, true)) {
                continue;
            }
            $sudah[] = $jenis;

            $bersih[] = [
                'jenis' => $jenis,
                'kunci' => ($b['kunci'] ?? null) ?: null,
                'persen' => max(0, min(100, (float) ($b['persen'] ?? 0))),
                'dasar' => self::dasarValid($b['dasar'] ?? null) ?? self::DASAR_BRUTO,
            ];
        }

        // Urutan dokumen mengikuti urutan pilihanJenis(), bukan urutan kirim.
        usort($bersih, fn ($a, $b) => array_search($a['jenis'], $dikenali, true)
            <=> array_search($b['jenis'], $dikenali, true));

        return $bersih;
    }

    private static function dasarValid(?string $dasar): ?string
    {
        return array_key_exists((string) $dasar, self::pilihanDasar()) ? $dasar : null;
    }

    /**
     * Jenis PPh yang seharusnya berlaku bila tidak ditentukan manual.
     */
    public static function jenisPphTurunan(ProcurementPackage $pp, ?string $skema = null): string
    {
        return SkemaPajak::jenisPajakPenghasilan(
            $skema ?? self::skema($pp),
            $pp->package?->jenis_pengadaan
        );
    }

    public static function pilihanJenisPph(): array
    {
        return [
            TarifPajak::PPH22_BARANG => 'PPh 22 — Barang',
            TarifPajak::PPH23_JASA => 'PPh 23 — Jasa',
        ];
    }

    private static function jenisPphTersimpan(?string $jenis): ?string
    {
        $dikenali = array_merge(
            array_keys(self::pilihanJenisPph()),
            [TarifPajak::PPH4_2_KONSTRUKSI]
        );

        return in_array($jenis, $dikenali, true) ? $jenis : null;
    }

    /**
     * Skema berlaku tiga lapis: pilihan manual pada pembayaran mengalahkan
     * bawaan rekening belanja, yang mengalahkan skema standar. Sesudah
     * pembayaran disimpan skema hanya jadi catatan prasetel — yang menghitung
     * adalah baris pajaknya.
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
