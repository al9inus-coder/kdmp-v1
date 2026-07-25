<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Package;
use App\Models\TravelOrder;
use App\Models\TravelPersonnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiSppdBridgeController extends Controller
{
    /**
     * Store AI Generated SPD directly into KDMP travel_orders & travel_personnels database.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $personelSearch = $request->input('personel', '');
            $tujuan = $request->input('tujuan', 'Bengkayang');
            $maksud = $request->input('maksud', "Perjalanan Dinas Ke {$tujuan}");
            $tanggalRaw = $request->input('tanggal', '');

            // 1. Resolve Employee from Database
            $employee = null;
            if (!empty($personelSearch)) {
                $employee = Employee::where('nama', 'LIKE', '%' . trim($personelSearch) . '%')->first();
            }

            if (!$employee) {
                // Fallback to first employee or default logged-in user employee
                $employee = Employee::first();
            }

            // 2. Parse Dates
            $tglBerangkat = now()->toDateString();
            $tglKembali = now()->toDateString();

            if (preg_match('/([0-9]{1,2})\s+([a-zA-Z]+)\s+([0-9]{4})/', $tanggalRaw, $matches)) {
                try {
                    $parsed = Carbon::parse("{$matches[1]} {$matches[2]} {$matches[3]}");
                    $tglBerangkat = $parsed->toDateString();
                    $tglKembali = $parsed->toDateString();
                } catch (\Throwable $e) {
                    // Fallback date
                }
            }

            // 3. Find active package or default package
            $package = Package::first();
            $packageId = $package ? $package->id : 1;

            DB::beginTransaction();

            // 4. Create TravelOrder record
            $travelOrder = TravelOrder::create([
                'package_id' => $packageId,
                'tipe_perjalanan' => 'dalam_daerah',
                'dasar_pelaksanaan' => 'Surat Tugas Disposisi Pimpinan / AI Generated',
                'maksud_perjalanan' => $maksud,
                'tempat_tujuan' => $tujuan,
                'tanggal_berangkat' => $tglBerangkat,
                'tanggal_kembali' => $tglKembali,
                'tanggal_surat' => now()->toDateString(),
                'status' => TravelOrder::STATUS_DRAFT,
                'created_by' => auth()->id() ?? 1,
            ]);

            // 5. Create TravelPersonnel record
            if ($employee) {
                TravelPersonnel::create([
                    'travel_order_id' => $travelOrder->id,
                    'employee_id' => $employee->id,
                    'nomor_sppd' => '000.1.2.3/SPD/KDMP/' . date('Y'),
                    'uang_harian' => 430000,
                    'biaya_transport' => 150000,
                    'biaya_penginapan' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Draf SPD atas nama [{$employee->nama}] berhasil disimpan ke Database KDMP!",
                'data' => [
                    'travel_order_id' => $travelOrder->id,
                    'nama_pelaksana' => $employee->nama,
                    'tujuan' => $tujuan,
                    'status' => 'draft',
                ],
                'redirect_url' => auth()->user()?->hasRole('Kabid') ? route('kabid.sppd.index') : route('staf.sppd.index'),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan draf SPD dari AI: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan draf SPD ke database: ' . $e->getMessage(),
            ], 500);
        }
    }
}
