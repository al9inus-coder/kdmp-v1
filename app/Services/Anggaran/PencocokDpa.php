<?php

namespace App\Services\Anggaran;

use App\Models\Account;
use App\Models\BudgetLine;
use App\Models\SubActivity;

/**
 * Mencocokkan hasil bacaan dokumen anggaran dengan isi basis data.
 *
 * Tidak menulis apa pun. Keluarannya bahan untuk ditinjau operator; yang
 * memutuskan tetap manusia, dan yang menulis tetap
 * BudgetLineController::bulkRevision() yang sudah ada.
 */
class PencocokDpa
{
    /** Nilai di sistem sudah sama dengan kolom Sesudah — tidak perlu diapa-apakan. */
    public const COCOK = 'cocok';

    /** Nilai di sistem sama dengan kolom Sebelum — siap diperbarui. */
    public const BERUBAH = 'berubah';

    /** Rekening belum terdaftar sama sekali. */
    public const BARU = 'baru';

    /** Tidak sama dengan Sebelum maupun Sesudah — ada riwayat yang tak tercatat. */
    public const MENYIMPANG = 'menyimpang';

    /** Ada di sistem, tidak disebut dokumen. */
    public const HILANG = 'hilang';

    /**
     * @return array{
     *   baris: array<int, array{
     *     kode: string, nama: string, status: string,
     *     sebelum: ?float, sesudah: ?float, sistem: ?float,
     *     budget_line_id: ?int, account_id: ?int
     *   }>,
     *   ringkasan: array<string, int>
     * }
     */
    public function cocokkan(array $hasil, SubActivity $subActivity, int $fiscalYearId): array
    {
        $lines = BudgetLine::with('account')
            ->where('sub_activity_id', $subActivity->id)
            ->where('fiscal_year_id', $fiscalYearId)
            ->get()
            ->keyBy(fn ($l) => $l->account?->kode);

        $akun = Account::whereIn('kode', array_column($hasil['baris'], 'kode'))
            ->get()
            ->keyBy('kode');

        $baris = [];
        $disebut = [];

        foreach ($hasil['baris'] as $b) {
            $disebut[] = $b['kode'];
            $line = $lines->get($b['kode']);
            $sistem = $line ? (float) $line->pagu_efektif : null;

            $baris[] = [
                'kode' => $b['kode'],
                'nama' => $b['nama'],
                'sebelum' => $b['sebelum'],
                'sesudah' => $b['sesudah'],
                'sistem' => $sistem,
                'budget_line_id' => $line?->id,
                'account_id' => $akun->get($b['kode'])?->id,
                'status' => $this->status($b, $sistem),
            ];
        }

        // Baris yang ada di sistem tetapi tidak disebut dokumen. Tidak diubah —
        // bisa saja posnya memang dihapus, bisa saja dokumennya tidak lengkap,
        // dan keduanya menuntut mata manusia.
        foreach ($lines as $kode => $line) {
            if ($kode === null || in_array($kode, $disebut, true)) {
                continue;
            }

            $baris[] = [
                'kode' => $kode,
                'nama' => $line->account?->nama ?? '—',
                'sebelum' => null,
                'sesudah' => null,
                'sistem' => (float) $line->pagu_efektif,
                'budget_line_id' => $line->id,
                'account_id' => $line->account_id,
                'status' => self::HILANG,
            ];
        }

        usort($baris, fn ($a, $b) => strcmp($a['kode'], $b['kode']));

        $ringkasan = array_count_values(array_column($baris, 'status'));

        return ['baris' => $baris, 'ringkasan' => $ringkasan];
    }

    private function status(array $b, ?float $sistem): string
    {
        if ($sistem === null) {
            return self::BARU;
        }

        if (abs($sistem - $b['sesudah']) < 0.01) {
            return self::COCOK;
        }

        // DPA murni tidak punya kolom Sebelum. Nilainya berbeda dari yang ada
        // di sistem berarti dokumen ini tidak sejalan dengan riwayat yang
        // tercatat — bukan sekadar perubahan biasa.
        if ($b['sebelum'] === null) {
            return self::MENYIMPANG;
        }

        return abs($sistem - $b['sebelum']) < 0.01 ? self::BERUBAH : self::MENYIMPANG;
    }
}
