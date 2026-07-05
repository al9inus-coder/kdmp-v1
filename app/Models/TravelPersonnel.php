<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelPersonnel extends Model
{
    protected $fillable = [
        'travel_order_id',
        'employee_id',
        'urutan',
        'nomor_sppd',
        'uang_harian',
        'biaya_transport',
        'biaya_taksi',
        'biaya_penginapan',
        'biaya_representasi',
        'jenis_kendaraan',
    ];

    public function travelOrder()
    {
        return $this->belongsTo(TravelOrder::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getEstimatedCosts()
    {
        $employee = $this->employee;
        $travelOrder = $this->travelOrder;
        $jenisKendaraan = $this->jenis_kendaraan ?? 'mobil';
        $days = 0;
        if ($travelOrder->tanggal_berangkat && $travelOrder->tanggal_kembali) {
            $days = \Carbon\Carbon::parse($travelOrder->tanggal_berangkat)->diffInDays(\Carbon\Carbon::parse($travelOrder->tanggal_kembali)) + 1;
        }

        $estimates = [
            'uang_harian' => 0,
            'biaya_penginapan' => 0,
            'biaya_representasi' => 0,
            'biaya_transport' => 0,
            'base_uang_harian' => 0,
            'base_penginapan' => 0,
            'base_representasi' => 0,
            'nights' => max(0, $days - 1),
            'days' => max(0, $days)
        ];

        if ($days <= 0) return $estimates;

        $baseUangHarian = 0;
        if (strtolower($travelOrder->tipe_perjalanan) === 'dalam daerah' || strtolower($travelOrder->tipe_perjalanan) === 'dalam_daerah') {
            $baseUangHarian = 150000;
        } else {
            $transportLuarDaerah = \App\Models\SbuTransportRate::where('kategori', 'luar_daerah')
                ->where('tempat_tujuan', $travelOrder->tempat_tujuan)->first();
                
            if ($transportLuarDaerah) {
                $sbuUangHarian = \App\Models\SbuUangHarian::where('provinsi', 'like', '%Kalimantan Barat%')->first();
            } else {
                $sbuUangHarian = \App\Models\SbuUangHarian::where('provinsi', 'like', '%' . $travelOrder->tempat_tujuan . '%')->first();
            }
            if ($sbuUangHarian) $baseUangHarian = $sbuUangHarian->luar_kota;
        }

        if (!empty($employee->kategori_biaya)) {
            $category = $employee->kategori_biaya;
        } else {
            $category = 'Eselon IV, Gol. III kebawah, P3K, Jafung, Non ASN';
            $jabatan = strtolower($employee->jabatan ?? '');
            $golongan = strtolower($employee->golongan ?? '');
            if (str_contains($jabatan, 'eselon ii') || str_contains($jabatan, 'kepala dinas')) {
                $category = 'Eselon II';
            } elseif (str_contains($jabatan, 'kepala bidang') || str_contains($jabatan, 'sekretaris') || str_contains($jabatan, 'eselon iii') || str_contains($golongan, 'iv') || str_contains($jabatan, 'jafung madya')) {
                $category = 'Eselon III, Gol. IV dan Jafung Madya';
            }
        }

        $estimates['uang_harian'] = $baseUangHarian * $days;
        $estimates['base_uang_harian'] = $baseUangHarian;

        $nights = max(0, $days - 1);

        if ($category === 'Eselon II') {
            if (strtolower($travelOrder->tipe_perjalanan) === 'dalam daerah' || strtolower($travelOrder->tipe_perjalanan) === 'dalam_daerah') {
                $estimates['base_representasi'] = 75000;
                $estimates['biaya_representasi'] = 75000 * $days;
            } else {
                $estimates['base_representasi'] = 150000;
                $estimates['biaya_representasi'] = 150000 * $days;
            }
        } else {
            $estimates['base_representasi'] = 0;
            $estimates['biaya_representasi'] = 0;
        }

        if (strtolower($travelOrder->tipe_perjalanan) === 'dalam daerah' || strtolower($travelOrder->tipe_perjalanan) === 'dalam_daerah') {
            if ($category === 'Eselon II') {
                    $estimates['base_penginapan'] = 500000;
                    $estimates['biaya_penginapan'] = 500000 * $nights;
                } elseif ($category === 'Eselon III, Gol. IV dan Jafung Madya') {
                    $estimates['base_penginapan'] = 450000;
                    $estimates['biaya_penginapan'] = 450000 * $nights;
                } else {
                    $jab = strtolower($employee->jabatan ?? '');
                    $gol = strtolower($employee->golongan ?? '');
                    // Eselon IV / Gol III gets 400.000, others 350.000
                    $isEselon4 = str_contains($jab, 'eselon iv') || str_contains($jab, 'kasi') || str_contains($jab, 'kasubbag') || str_contains($gol, 'iii');
                    
                    if ($isEselon4) {
                        $estimates['base_penginapan'] = 400000;
                        $estimates['biaya_penginapan'] = 400000 * $nights;
                    } else {
                        $estimates['base_penginapan'] = 350000;
                        $estimates['biaya_penginapan'] = 350000 * $nights;
                    }
                }
        } else {
            $transportLuarDaerah = \App\Models\SbuTransportRate::where('kategori', 'luar_daerah')
                ->where('tempat_tujuan', $travelOrder->tempat_tujuan)->first();

            if ($transportLuarDaerah) {
                $sbuPenginapan = \App\Models\SbuPenginapan::where('provinsi', 'like', '%Kalimantan Barat%')->first();
            } else {
                $sbuPenginapan = \App\Models\SbuPenginapan::where('provinsi', 'like', '%' . $travelOrder->tempat_tujuan . '%')->first();
            }
            
            if ($sbuPenginapan) {
                if ($category === 'Eselon II') {
                    $estimates['base_penginapan'] = $sbuPenginapan->eselon_ii;
                    $estimates['biaya_penginapan'] = $sbuPenginapan->eselon_ii * $nights;
                } elseif ($category === 'Eselon III, Gol. IV dan Jafung Madya') {
                    $estimates['base_penginapan'] = $sbuPenginapan->eselon_iii;
                    $estimates['biaya_penginapan'] = $sbuPenginapan->eselon_iii * $nights;
                } else {
                    $estimates['base_penginapan'] = $sbuPenginapan->eselon_iv;
                    $estimates['biaya_penginapan'] = $sbuPenginapan->eselon_iv * $nights;
                }
            }
        }

        if ($jenisKendaraan === 'pengikut') {
            $estimates['biaya_transport'] = 0;
        } elseif ($jenisKendaraan === 'pesawat') {
            $tiket = \App\Models\SbuTiketPesawat::where('tujuan', 'like', '%' . $travelOrder->tempat_tujuan . '%')->first();
            if ($tiket) {
                if ($category === 'Eselon II') $estimates['biaya_transport'] = $tiket->bisnis;
                else $estimates['biaya_transport'] = $tiket->ekonomi;
                $estimates['biaya_taksi'] = 0;
                // Add Transport Bandara (Luar Kota)
                $transportBandara = \App\Models\SbuTransportBandara::where('provinsi', 'like', '%' . $travelOrder->tempat_tujuan . '%')->first();
                $isTransportLuarDaerah = \App\Models\SbuTransportRate::where('kategori', 'luar_daerah')
                    ->where('tempat_tujuan', $travelOrder->tempat_tujuan)->exists();
                
                if (!$transportBandara && $isTransportLuarDaerah) {
                    $transportBandara = \App\Models\SbuTransportBandara::where('provinsi', 'like', '%Kalimantan Barat%')->first();
                }
                if ($transportBandara) {
                    $estimates['biaya_taksi'] = $transportBandara->di_tempat_kedudukan + $transportBandara->di_tempat_tujuan;
                }
            }
        } else {
            $transport = \App\Models\SbuTransportRate::where('tempat_tujuan', 'like', '%' . $travelOrder->tempat_tujuan . '%')->first();
            if ($transport) {
                if ($jenisKendaraan === 'motor') $estimates['biaya_transport'] = $transport->biaya_motor;
                else $estimates['biaya_transport'] = $transport->biaya_mobil;
            }
        }

        return $estimates;
    }
}
