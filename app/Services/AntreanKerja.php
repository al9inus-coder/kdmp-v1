<?php

namespace App\Services;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Pekerjaan yang sedang menunggu tindakan pengguna.
 *
 * Diturunkan langsung dari data — tidak ada tabel notifikasi maupun status
 * "sudah dibaca". Akibatnya angkanya tidak pernah basi dan tidak bisa
 * melenceng dari keadaan sebenarnya.
 *
 * Satu-satunya sumber untuk badge sidebar maupun pil asisten; jangan
 * menghitung ulang di tempat lain.
 */
class AntreanKerja
{
    /**
     * @return Collection<int, array{kunci: string, jumlah: int, teks: string, url: string}>
     */
    public function untuk(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return match (true) {
            $user->hasRole('Kabid') => $this->kabid(),
            $user->hasRole('Staff') => $this->staf($user),
            // Admin belum punya definisi "menunggu" yang disepakati — lebih
            // baik kosong daripada menampilkan angka yang mengada-ada.
            default => collect(),
        };
    }

    public function jumlah(?User $user): int
    {
        return (int) $this->untuk($user)->sum('jumlah');
    }

    /** Jumlah khusus SPD — dipakai badge menu SPPD di sidebar. */
    public function jumlahSpd(?User $user): int
    {
        return (int) $this->untuk($user)
            ->filter(fn (array $a) => str_starts_with($a['kunci'], 'spd_'))
            ->sum('jumlah');
    }

    // ── Per peran ─────────────────────────────────────────────────

    private function kabid(): Collection
    {
        $pengajuan = TravelOrder::where('status', TravelOrder::STATUS_SUBMITTED)->count();

        $spj = TravelOrder::where('status', TravelOrder::STATUS_APPROVED)
            ->where('spj_status', TravelOrder::SPJ_SUBMITTED)
            ->count();

        return $this->rakit([
            ['spd_persetujuan', $pengajuan, 'pengajuan SPD menunggu persetujuan Anda', 'kabid.sppd.index'],
            ['spd_spj', $spj, 'SPJ perjalanan dinas menunggu diperiksa', 'kabid.sppd.index'],
        ]);
    }

    private function staf(User $user): Collection
    {
        $dikembalikan = TravelOrder::where('created_by', $user->id)
            ->where('status', TravelOrder::STATUS_REVISION)
            ->count();

        $spjRevisi = TravelOrder::where('created_by', $user->id)
            ->where('spj_status', TravelOrder::SPJ_REVISION)
            ->count();

        return $this->rakit([
            ['spd_revisi', $dikembalikan, 'SPD Anda dikembalikan untuk diperbaiki', 'staf.sppd.index'],
            ['spd_spj_revisi', $spjRevisi, 'SPJ Anda diminta diperbaiki', 'staf.sppd.index'],
        ]);
    }

    /** @param array<int, array{0: string, 1: int, 2: string, 3: string}> $baris */
    private function rakit(array $baris): Collection
    {
        return collect($baris)
            ->filter(fn (array $b) => $b[1] > 0)
            ->map(fn (array $b) => [
                'kunci' => $b[0],
                'jumlah' => $b[1],
                'teks' => $b[2],
                'url' => \Route::has($b[3]) ? route($b[3]) : url('/'),
            ])
            ->values();
    }
}
