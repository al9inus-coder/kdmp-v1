<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $fillable = [
        'package_id',
        'tahun',
        'bulan',
        'jenis_lembur',
        'dasar_pelaksanaan',
        'is_locked',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function details()
    {
        return $this->hasMany(OvertimeDetail::class);
    }

    /**
     * SATU-SATUNYA sumber perhitungan lembur (jam, tarif, uang makan, PPh 21).
     * Dipakai monev (via calculateTotalRealisasi), tabel rekap di halaman lembur,
     * dan dokumen cetak — supaya angkanya tidak mungkin berbeda antar halaman.
     *
     * @return array{rows: array<int, array<string, mixed>>, totalJam: int, totalUpah: float, totalUangMakan: float, totalPajak: float, totalDiterima: float}
     */
    public function rekap($sbuRates = null): array
    {
        if (!$sbuRates) {
            $sbuRates = SbuLembur::all();
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->bulan, $this->tahun);

        $rows = [];
        $totalJam = 0;
        $totalUpah = 0;
        $totalUangMakan = 0;
        $totalPajak = 0;
        $totalDiterima = 0;

        foreach ($this->details as $detail) {
            $emp = $detail->employee;
            if (!$emp) continue;

            $golongan = $detail->golongan_fix ?? $emp->golongan ?? 'P3K Paruh Waktu';
            $jam = 0;
            $hari = 0;
            $jamPerHari = [];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $val = isset($detail->daily_hours[$d]) ? (int) $detail->daily_hours[$d] : 0;
                if ($val >= 2) {
                    $jam += $val;
                    $hari++;
                    $jamPerHari[$d] = $val;
                }
            }
            if ($jam == 0) continue;

            // Tarif: snapshot per-detail menang; selain itu pemetaan golongan SBU
            // yang toleran format (tanpa fallback tarif lain).
            $valLembur = !is_null($detail->rate_lembur_fix)
                ? $detail->rate_lembur_fix
                : (SbuLembur::pickRate($sbuRates, 'Uang Lembur', $golongan)?->besaran ?? 0);
            $valMakan = !is_null($detail->rate_makan_fix)
                ? $detail->rate_makan_fix
                : (SbuLembur::pickRate($sbuRates, 'Uang Makan Lembur', $golongan)?->besaran ?? 0);

            $uangLembur = $jam * $valLembur;
            $uangMakan = $detail->use_uang_makan ? ($hari * $valMakan) : 0;

            // PPh 21 Final lembur: gol III 5%, gol IV 15%, selainnya 0.
            $pphRate = 0;
            if (str_contains(strtoupper($golongan), 'III')) $pphRate = 0.05;
            elseif (str_contains(strtoupper($golongan), 'IV')) $pphRate = 0.15;

            $pajak = $uangLembur * $pphRate;
            $diterima = ($uangLembur - $pajak) + $uangMakan;

            $rows[] = [
                'detail' => $detail,
                'employee' => $emp,
                'golongan' => $golongan,
                'jamPerHari' => $jamPerHari,
                'totalJam' => $jam,
                'hari' => $hari,
                'valLembur' => $valLembur,
                'valMakan' => $valMakan,
                'uangLembur' => $uangLembur,
                'uangMakan' => $uangMakan,
                'pajak' => $pajak,
                'diterima' => $diterima,
            ];

            $totalJam += $jam;
            $totalUpah += $uangLembur;
            $totalUangMakan += $uangMakan;
            $totalPajak += $pajak;
            $totalDiterima += $diterima;
        }

        return [
            'rows' => $rows,
            'totalJam' => $totalJam,
            'totalUpah' => $totalUpah,
            'totalUangMakan' => $totalUangMakan,
            'totalPajak' => $totalPajak,
            'totalDiterima' => $totalDiterima,
        ];
    }

    public function calculateTotalRealisasi($sbuRates = null)
    {
        return $this->rekap($sbuRates)['totalDiterima'];
    }
}
