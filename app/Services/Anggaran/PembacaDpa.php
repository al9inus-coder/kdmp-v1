<?php

namespace App\Services\Anggaran;

use Illuminate\Support\Str;
use RuntimeException;
use Smalot\PdfParser\Parser;

/**
 * Membaca cetakan anggaran SIPD (DPA, DPPA, RKA) menjadi struktur data.
 *
 * Bukan OCR. Ketiga bentuk cetakan itu PDF digital dengan teks tertanam, jadi
 * angkanya diambil persis apa adanya — tidak ada tebakan dan tidak ada model.
 *
 * Lapisan PDF-nya diserahkan ke smalot/pdfparser, sebab ketiga berkas ternyata
 * berbeda dalamannya walau dari sistem yang sama: RKA memakai string literal
 * tanpa kompresi, kedua DPA memakai aliran terkompresi berisi ID glyph yang
 * perlu tabel ToUnicode fontnya. Kelas ini hanya mengurus bagian domain.
 */
class PembacaDpa
{
    public const DPA = 'dpa';
    public const DPPA = 'dppa';
    public const RKA = 'rka';

    /** Rekening belanja sesungguhnya berkode enam ruas (5.1.02.01.001.00004). */
    private const RUAS_DAUN = 5;

    public function __construct(private readonly Parser $parser = new Parser()) {}

    /**
     * @return array{
     *   jenisDokumen: string, nomor: ?string, tahun: ?int,
     *   subKegiatanKode: ?string, subKegiatanNama: ?string,
     *   programKode: ?string, kegiatanKode: ?string,
     *   punyaSebelum: bool, totalDokumen: float, alokasiKepala: ?float,
     *   baris: array<int, array{kode: string, nama: string, sebelum: ?float, sesudah: float}>
     * }
     */
    public function baca(string $path): array
    {
        $teks = $this->parser->parseFile($path)->getText();
        $baris = explode("\n", $teks);

        $kepala = $this->kepala($baris);
        $rekening = $this->rekening($baris);

        $daun = array_values(array_filter($rekening, fn ($r) => $r['daun']));
        $akar = collect($rekening)->firstWhere('kode', '5');

        return array_merge($kepala, [
            'punyaSebelum' => (bool) collect($daun)->contains(fn ($r) => $r['sebelum'] !== null),
            'totalDokumen' => (float) ($akar['sesudah'] ?? 0),
            'baris' => array_map(fn ($r) => [
                'kode' => $r['kode'],
                'nama' => $r['nama'],
                'sebelum' => $r['sebelum'],
                'sesudah' => $r['sesudah'],
            ], $daun),
        ]);
    }

    /**
     * Uji jumlah: total rekening daun harus sama dengan total belanja pada
     * dokumen, dan dengan alokasi di kepala bila tercantum.
     *
     * Tiga angka dari tiga tempat berbeda saling mengunci. Ini yang akan
     * menangkap salah urai bila SIPD mengubah tata letak cetakannya — dan
     * salah urai anggaran tidak menimbulkan galat apa pun bila lolos.
     *
     * @return array<int, string> daftar keberatan; kosong berarti lolos
     */
    public function periksa(array $hasil): array
    {
        $keberatan = [];
        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

        if ($hasil['baris'] === []) {
            return ['Tidak ada satu pun baris rekening yang terbaca dari berkas ini.'];
        }

        $jumlahDaun = array_sum(array_column($hasil['baris'], 'sesudah'));

        if (abs($jumlahDaun - $hasil['totalDokumen']) >= 0.01) {
            $keberatan[] = 'Jumlah rekening (' . $rp($jumlahDaun) . ') tidak sama dengan total belanja'
                . ' pada dokumen (' . $rp($hasil['totalDokumen']) . ').';
        }

        if ($hasil['alokasiKepala'] !== null
            && abs($hasil['totalDokumen'] - $hasil['alokasiKepala']) >= 0.01) {
            $keberatan[] = 'Total belanja (' . $rp($hasil['totalDokumen']) . ') tidak sama dengan'
                . ' alokasi di kepala dokumen (' . $rp($hasil['alokasiKepala']) . ').';
        }

        if (!$hasil['subKegiatanKode']) {
            $keberatan[] = 'Kode sub kegiatan tidak ditemukan di berkas ini.';
        }

        return $keberatan;
    }

    private function kepala(array $baris): array
    {
        $satu = fn (string $pola) => collect($baris)
            ->map(fn ($b) => trim(preg_replace('/\s+/', ' ', $b)))
            ->map(fn ($b) => preg_match($pola, $b, $m) ? $m : null)
            ->filter()
            ->first();

        $nomor = $satu('/Nomor\s+(DPPA|DPA)\s*:\s*(\S+)/i');
        // Kode sub kegiatan berruas enam (2.11.04.2.01.0004). Dengan lima ruas,
        // potongan terakhirnya ikut terbawa ke nama.
        $sub = $satu('/Sub Kegiatan\s*:\s*(\d(?:\.\d+){5})\s*-?\s*(.*)$/i');
        $prog = $satu('/^Program\s*:\s*(\d(?:\.\d+){1,2})/i');
        $keg = $satu('/^Kegiatan\s*:\s*(\d(?:\.\d+){3})/i');
        $tahun = $satu('/Tahun Anggaran\s+(\d{4})/i');
        $alokasi = $satu('/Jumlah Anggaran Sub Kegiatan\s*Rp\s*([\d.]+,\d{2})/i');

        // Cetakan RKA tidak memakai baris itu; alokasinya tertulis per tahun
        // ("Alokasi 2026 : Rp. 153.395.031,00") berdampingan dengan tahun lain,
        // jadi yang diambil harus yang tahunnya cocok.
        if (!$alokasi && isset($tahun[1])) {
            $alokasi = $satu('/Alokasi\s+' . preg_quote($tahun[1], '/') . '\s*:\s*Rp\.?\s*([\d.]+,\d{2})/i');
        }

        // Nomornya menyebut jenisnya: DPA untuk murni, DPPA untuk perubahan.
        // Cetakan RKA tidak bernomor.
        $jenis = match (strtoupper($nomor[1] ?? '')) {
            'DPPA' => self::DPPA,
            'DPA' => self::DPA,
            default => self::RKA,
        };

        return [
            'jenisDokumen' => $jenis,
            'nomor' => $nomor[2] ?? null,
            'tahun' => isset($tahun[1]) ? (int) $tahun[1] : null,
            'subKegiatanKode' => $sub[1] ?? null,
            'subKegiatanNama' => isset($sub[2]) ? trim($sub[2]) : null,
            'programKode' => $prog[1] ?? null,
            'kegiatanKode' => $keg[1] ?? null,
            'alokasiKepala' => isset($alokasi[1]) ? $this->rupiah($alokasi[1]) : null,
        ];
    }

    /**
     * Baris rekening berbentuk: <kode><nama> lalu satu atau tiga angka.
     *
     * Satu angka pada DPA murni; tiga pada DPPA dan RKA (sebelum, sesudah,
     * selisih). Awalan "Rp" ada pada cetakan DPA, tidak pada RKA — jadi
     * angkanya dicari dengan pola yang sama-sama menerima keduanya.
     */
    private function rekening(array $baris): array
    {
        $keluar = [];
        $rapi = array_map(fn ($b) => trim(preg_replace('/\s+/', ' ', $b)), $baris);
        $jumlah = count($rapi);
        $adaAngka = fn (string $s) => (bool) preg_match('/-?[\d.]+,\d{2}/', $s);

        for ($i = 0; $i < $jumlah; $i++) {
            if (!preg_match('/^(5(?:\.\d+)*)\s*(.*)$/', $rapi[$i], $m)) {
                continue;
            }

            $kode = $m[1];
            $sisa = $m[2];

            // Nama rekening yang panjang bisa terpecah beberapa baris, dan
            // angkanya baru muncul di baris terakhir. Kumpulkan sampai ketemu,
            // tapi berhenti bila sudah menyentuh rekening lain atau butir
            // rinciannya — supaya tidak menyerap angka milik baris lain.
            $lompat = 0;
            while (!$adaAngka($sisa) && $lompat < 4 && $i + $lompat + 1 < $jumlah) {
                $lanjut = $rapi[$i + $lompat + 1];
                if ($lanjut === '' || preg_match('/^5(?:\.\d+)*\D/', $lanjut) || str_starts_with($lanjut, '[')) {
                    break;
                }
                $sisa = trim($sisa . ' ' . $lanjut);
                $lompat++;
            }

            preg_match_all('/(?:Rp\s*)?(-?[\d.]+,\d{2})/', $sisa, $angka);
            if ($angka[1] === []) {
                continue;
            }

            $i += $lompat;

            $nilai = array_map(fn ($a) => $this->rupiah($a), $angka[1]);

            // Nama = bagian sebelum angka pertama.
            $potong = mb_strpos($sisa, $angka[0][0]);
            $nama = trim(mb_substr($sisa, 0, $potong === false ? null : $potong));

            if ($nama === '') {
                continue;
            }

            $keluar[] = [
                'kode' => $kode,
                'nama' => $this->rapikanNama($nama),
                'sebelum' => count($nilai) >= 3 ? $nilai[0] : null,
                'sesudah' => count($nilai) >= 3 ? $nilai[1] : $nilai[0],
                'daun' => substr_count($kode, '.') >= self::RUAS_DAUN,
            ];
        }

        return $keluar;
    }

    /**
     * Cetakan SIPD kadang menyisipkan spasi di tengah kata akibat kerning
     * ("PER TANAHAN"). Yang dirapikan hanya spasi ganda dan spasi sebelum
     * tanda baca — memperbaiki pemenggalan kata butuh menebak, dan menebak
     * nama rekening lebih berbahaya daripada membiarkannya apa adanya.
     */
    private function rapikanNama(string $nama): string
    {
        return Str::of($nama)->replaceMatches('/\s+/', ' ')
            ->replaceMatches('/\s+([,.)])/', '$1')
            ->trim()
            ->value();
    }

    /**
     * "47.049.000,00" -> 47049000.00
     *
     * Pemisah ribuan dibuang DULU, baru koma desimal jadi titik. Dibalik
     * urutannya, hasilnya seratus kali lipat tanpa galat apa pun.
     */
    private function rupiah(string $s): float
    {
        $bersih = preg_replace('/[^\d.,-]/', '', $s);

        return (float) str_replace(',', '.', str_replace('.', '', $bersih));
    }
}
