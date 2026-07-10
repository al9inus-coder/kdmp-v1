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

    public function calculateTotalRealisasi($sbuRates = null)
    {
        if (!$sbuRates) {
            $sbuRates = \App\Models\SbuLembur::all();
        }

        $totalBulanIni = 0;
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->bulan, $this->tahun);

        foreach ($this->details as $detail) {
            $emp = $detail->employee;
            if (!$emp) continue;

            $golongan = $detail->golongan_fix ?? $emp->golongan ?? 'P3K Paruh Waktu';
            $totalJam = 0;
            $daysWithOvertime = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $val = isset($detail->daily_hours[$d]) ? (int)$detail->daily_hours[$d] : 0;
                if ($val >= 2) {
                    $totalJam += $val;
                    $daysWithOvertime++;
                }
            }
            if ($totalJam == 0) continue;

            // Rate Lembur (pemetaan golongan toleran format, tanpa fallback tarif lain)
            if (!is_null($detail->rate_lembur_fix)) {
                $valLembur = $detail->rate_lembur_fix;
            } else {
                $valLembur = SbuLembur::pickRate($sbuRates, 'Uang Lembur', $golongan)?->besaran ?? 0;
            }

            // Rate Makan
            if (!is_null($detail->rate_makan_fix)) {
                $valMakan = $detail->rate_makan_fix;
            } else {
                $valMakan = SbuLembur::pickRate($sbuRates, 'Uang Makan Lembur', $golongan)?->besaran ?? 0;
            }

            // Values computed above

            $uangLembur = $totalJam * $valLembur;
            $uangMakan = $detail->use_uang_makan ? ($daysWithOvertime * $valMakan) : 0;

            // PPh 21 Final for Lembur
            $pphRate = 0;
            if (str_contains(strtoupper($golongan), 'III')) $pphRate = 0.05;
            elseif (str_contains(strtoupper($golongan), 'IV')) $pphRate = 0.15;

            $totalDiterima = ($uangLembur - ($uangLembur * $pphRate)) + $uangMakan;
            $totalBulanIni += $totalDiterima;
        }

        return $totalBulanIni;
    }
}
