<?php

namespace App\Services\AI;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Menyiapkan lampiran sebelum dikirim ke AI Service.
 *
 * Berkasnya TIDAK disimpan di mana pun: dibaca di memori, dikirim, lalu
 * dibuang. Lembar disposisi memuat nama, NIP, dan tanda tangan — makin
 * sedikit yang mengendap, makin baik.
 *
 * Tiga bentuk keluaran:
 *   ['jenis' => 'gambar',     'data_url' => 'data:image/jpeg;base64,…']
 *   ['jenis' => 'pdf_teks',   'teks' => '…']            ← PDF digital, gratis
 *   ['jenis' => 'pdf_berkas', 'data_url' => 'data:application/pdf;base64,…']
 */
class PenyiapDokumen
{
    /** Sisi terpanjang gambar setelah diperkecil. Cukup untuk tulisan
     *  tangan, tanpa membuang biaya pada piksel yang tidak menambah
     *  keterbacaan. */
    private const SISI_MAKSIMAL = 1600;

    private const MUTU_JPEG = 85;

    /** 8 MB — foto ponsel biasanya 2–5 MB. */
    public const UKURAN_MAKSIMAL = 8 * 1024 * 1024;

    /** Batas halaman PDF. Tiap halaman pindaian dihitung seperti satu
     *  gambar, jadi dokumen tebal bisa mahal tanpa disadari. */
    private const HALAMAN_MAKSIMAL = 10;

    /** Di bawah ambang ini PDF dianggap hasil pindai (teksnya tidak ada,
     *  yang terbaca cuma sisa metadata), sehingga perlu dibaca penglihatan. */
    private const AMBANG_TEKS = 120;

    public const JENIS_GAMBAR = ['image/jpeg', 'image/png', 'image/webp'];

    public const JENIS_PDF = ['application/pdf'];

    /**
     * @throws InvalidArgumentException dengan pesan yang layak tampil ke pengguna
     */
    public function siapkan(UploadedFile $berkas): array
    {
        $jenis = (string) $berkas->getMimeType();

        if ($berkas->getSize() > self::UKURAN_MAKSIMAL) {
            throw new InvalidArgumentException('Ukuran berkas melebihi 8 MB.');
        }

        if (in_array($jenis, self::JENIS_GAMBAR, true)) {
            return $this->siapkanGambar($berkas, $jenis);
        }

        if (in_array($jenis, self::JENIS_PDF, true)) {
            return $this->siapkanPdf($berkas);
        }

        throw new InvalidArgumentException(
            'Jenis berkas belum didukung. Kirim foto (JPG, PNG, WEBP) atau berkas PDF. '
            . 'Foto iPhone berformat HEIC perlu diubah dulu — pilih "Paling Kompatibel" di pengaturan kamera.'
        );
    }

    // ── PDF ───────────────────────────────────────────────────────

    private function siapkanPdf(UploadedFile $berkas): array
    {
        $jalur = $berkas->getRealPath();

        // PDF digital: teksnya sudah ada di dalam berkas. Membacanya sendiri
        // gratis dan hasilnya persis — tidak perlu melibatkan model sama sekali.
        $teks = $this->tarikTeks($jalur);
        $halaman = $this->hitungHalaman($jalur, $teks);

        if ($halaman !== null && $halaman > self::HALAMAN_MAKSIMAL) {
            throw new InvalidArgumentException(
                "PDF ini {$halaman} halaman, melebihi batas " . self::HALAMAN_MAKSIMAL . ' halaman. '
                . 'Kirim halaman yang relevan saja.'
            );
        }

        if ($teks !== null && mb_strlen(preg_replace('/\s+/u', '', $teks)) >= self::AMBANG_TEKS) {
            return [
                'jenis' => 'pdf_teks',
                'teks' => $teks,
                'halaman' => $halaman,
                'nama' => $berkas->getClientOriginalName(),
            ];
        }

        // Hasil pindai: isinya gambar, jadi harus dibaca penglihatan.
        return [
            'jenis' => 'pdf_berkas',
            'data_url' => 'data:application/pdf;base64,' . base64_encode((string) file_get_contents($jalur)),
            'halaman' => $halaman,
            'nama' => $berkas->getClientOriginalName(),
        ];
    }

    /**
     * Jumlah halaman, dari yang paling dapat dipercaya ke yang paling kasar:
     *
     * 1. pdftotext memisahkan halaman dengan pemisah halaman (\f) — ini
     *    akurat dan tidak butuh perkakas tambahan selain yang sudah dipakai.
     * 2. pdfinfo, bila kebetulan terpasang.
     * 3. Menghitung objek /Type /Page di berkas mentah — meleset pada PDF
     *    yang objeknya dimampatkan, jadi hanya dipakai sebagai upaya terakhir.
     *
     * null berarti benar-benar tidak diketahui; batas halaman lalu dilewati
     * daripada menolak berkas yang sebenarnya sah.
     */
    private function hitungHalaman(string $jalur, ?string $teks): ?int
    {
        if ($teks !== null && str_contains($teks, "\f")) {
            return substr_count($teks, "\f");
        }

        $keluaran = $this->jalankan(['pdfinfo', $jalur]);

        if ($keluaran !== null && preg_match('/^Pages:\s*(\d+)/mi', $keluaran, $m)) {
            return (int) $m[1];
        }

        $mentah = (string) file_get_contents($jalur);
        $jumlah = preg_match_all('#/Type\s*/Page[^s]#', $mentah);

        return $jumlah > 0 ? $jumlah : null;
    }

    /**
     * Teks tertanam lewat pdftotext. Mengembalikan null bila perkakasnya
     * tidak terpasang — pemanggilnya lalu jatuh ke jalur penglihatan, jadi
     * fitur ini tetap jalan di server yang tidak punya poppler.
     */
    private function tarikTeks(string $jalur): ?string
    {
        $tujuan = tempnam(sys_get_temp_dir(), 'pdfteks');

        try {
            $keluaran = $this->jalankan(['pdftotext', '-layout', $jalur, $tujuan]);

            if ($keluaran === null) {
                return null;
            }

            $teks = is_file($tujuan) ? (string) file_get_contents($tujuan) : '';

            return trim($teks) !== '' ? trim($teks) : null;
        } finally {
            @unlink($tujuan);
        }
    }

    /** @param  array<int, string>  $perintah */
    private function jalankan(array $perintah): ?string
    {
        try {
            $proses = new Process($perintah);
            $proses->setTimeout(20);
            $proses->run();

            if (! $proses->isSuccessful()) {
                Log::warning('PenyiapDokumen: ' . $perintah[0] . ' gagal: ' . $proses->getErrorOutput());

                return null;
            }

            return $proses->getOutput();
        } catch (ProcessFailedException|\Throwable $e) {
            // Perkakas tidak terpasang di server ini — bukan galat fatal.
            Log::info('PenyiapDokumen: ' . $perintah[0] . ' tidak tersedia (' . $e->getMessage() . ')');

            return null;
        }
    }

    // ── Gambar ────────────────────────────────────────────────────

    private function siapkanGambar(UploadedFile $berkas, string $jenis): array
    {
        $jalur = $berkas->getRealPath();

        $gambar = match ($jenis) {
            'image/jpeg' => @imagecreatefromjpeg($jalur),
            'image/png' => @imagecreatefrompng($jalur),
            'image/webp' => @imagecreatefromwebp($jalur),
        };

        if (! $gambar) {
            throw new InvalidArgumentException('Berkas tidak dapat dibaca sebagai gambar.');
        }

        try {
            $gambar = $this->tegakkan($gambar, $jalur, $jenis);
            $gambar = $this->perkecil($gambar);

            ob_start();
            imagejpeg($gambar, null, self::MUTU_JPEG);
            $biner = (string) ob_get_clean();

            return [
                'jenis' => 'gambar',
                'data_url' => 'data:image/jpeg;base64,' . base64_encode($biner),
                'lebar' => imagesx($gambar),
                'tinggi' => imagesy($gambar),
                'ukuran' => strlen($biner),
                'nama' => $berkas->getClientOriginalName(),
            ];
        } finally {
            imagedestroy($gambar);
        }
    }

    /**
     * Foto ponsel sering tersimpan miring dengan penanda orientasi di EXIF.
     * Model penglihatan membaca pikselnya apa adanya, jadi kalau tidak
     * ditegakkan dulu hasil bacaannya jauh lebih buruk.
     *
     * @param  \GdImage  $gambar
     * @return \GdImage
     */
    private function tegakkan($gambar, string $jalur, string $jenis)
    {
        if ($jenis !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $gambar;
        }

        $exif = @exif_read_data($jalur);

        $derajat = match ((int) ($exif['Orientation'] ?? 0)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($derajat === 0) {
            return $gambar;
        }

        $diputar = imagerotate($gambar, $derajat, 0);

        if (! $diputar) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $diputar;
    }

    /**
     * @param  \GdImage  $gambar
     * @return \GdImage
     */
    private function perkecil($gambar)
    {
        $lebar = imagesx($gambar);
        $tinggi = imagesy($gambar);
        $terpanjang = max($lebar, $tinggi);

        if ($terpanjang <= self::SISI_MAKSIMAL) {
            return $gambar;
        }

        $rasio = self::SISI_MAKSIMAL / $terpanjang;
        $kecil = imagescale($gambar, (int) round($lebar * $rasio), (int) round($tinggi * $rasio));

        if (! $kecil) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $kecil;
    }
}
