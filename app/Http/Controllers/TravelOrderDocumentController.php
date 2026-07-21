<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use PhpOffice\PhpWord\TemplateProcessor;

class TravelOrderDocumentController extends Controller
{
    /**
     * Export to Word (docx)
     */
    public function exportWord(Package $package, TravelOrder $travelOrder, $type)
    {
        $this->authorizeAccess($package, $travelOrder);
        $travelOrder->load('personnels.employee');
        
        $basePath = storage_path('app/templates/travel-orders');
        $fileName = '';
        $templatePath = '';

        // Determine template
        if ($type === 'permohonan-bupati') {
            $templatePath = $basePath . '/surat_permohonan_bupati.docx';
            $fileName = 'Surat_Permohonan_Bupati_' . str_replace(' ', '_', $travelOrder->tempat_tujuan) . '.docx';
        } elseif ($type === 'surat-tugas-kadis') {
            $templatePath = $basePath . '/surat_tugas_kadis.docx';
            $fileName = 'Surat_Tugas_Kadis_' . str_replace(' ', '_', $travelOrder->tempat_tujuan) . '.docx';
        } elseif ($type === 'surat-tugas-bupati') {
            $templatePath = $basePath . '/surat_tugas_bupati.docx';
            $fileName = 'Surat_Tugas_Bupati_' . str_replace(' ', '_', $travelOrder->tempat_tujuan) . '.docx';
        } else {
            abort(404, 'Tipe dokumen tidak valid');
        }

        if (!file_exists($templatePath)) {
            abort(404, 'File template ' . basename($templatePath) . ' tidak ditemukan. Harap unggah template terlebih dahulu di ' . $templatePath);
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        $employeeNames = $travelOrder->personnels->map(function($p) {
            return $p->employee->nama;
        })->toArray();

        $namaGabungan = '';
        $count = count($employeeNames);
        if ($count === 1) {
            $namaGabungan = $employeeNames[0];
        } elseif ($count === 2) {
            $namaGabungan = $employeeNames[0] . ' dan ' . $employeeNames[1];
        } elseif ($count > 2) {
            $last = array_pop($employeeNames);
            $namaGabungan = implode(', ', $employeeNames) . ', dan ' . $last;
        }

        $tglBerangkatStr = $travelOrder->tanggal_berangkat->translatedFormat('d F Y');
        $tglKembaliStr = $travelOrder->tanggal_kembali->translatedFormat('d F Y');
        
        $tglPelaksanaan = $tglBerangkatStr;
        if ($tglBerangkatStr !== $tglKembaliStr) {
            $tglPelaksanaan = $tglBerangkatStr . ' s.d. ' . $tglKembaliStr;
        }

        $tglSuratCarbon = $travelOrder->tanggal_surat ? $travelOrder->tanggal_surat : Carbon::now();
        $templateProcessor->setValues([
            'dasar_pelaksanaan' => $travelOrder->dasar_pelaksanaan ?? '-',
            'maksud_perjalanan' => $travelOrder->maksud_perjalanan,
            'tempat_tujuan' => $travelOrder->tempat_tujuan,
            'tgl_pelaksanaan' => $tglPelaksanaan,
            'tgl_berangkat' => $tglBerangkatStr,
            'tgl_kembali' => $tglKembaliStr,
            'tgl_surat' => $tglSuratCarbon->translatedFormat('d F Y'),
            'tanggal_naskah' => in_array($type, ['permohonan-bupati', 'surat-tugas-kadis']) ? '${tanggal_naskah}' : '                ' . $tglSuratCarbon->translatedFormat('F Y'),
            'tahun' => $tglSuratCarbon->translatedFormat('Y'),
            'nomor_surat' => '......../........./......./' . date('Y'), // Placeholder
            'nama_kadis' => 'NAMA KEPALA DINAS', // Placeholder
            'nip_kadis' => '19800101 200501 1 001', // Placeholder
            'nama_gabungan' => $namaGabungan,
        ]);

        // Mapping multiple employees using cloneRowAndSetValues
        // This requires the tag ${no} to be inside a table row in the Word document.
        $personnelValues = [];
        foreach ($travelOrder->personnels as $index => $personnel) {
            $employee = $personnel->employee;
            $personnelValues[] = [
                'kpd' => $index === 0 ? 'Kepada' : '',
                't'   => $index === 0 ? ':' : '',
                'n'   => $index + 1,
                'nama' => $employee ? $employee->nama : '-',
                'nip'  => $employee ? $employee->nip : '-',
                'pangkat_gol' => $employee ? $employee->golongan : '-',
                'jabatan' => $employee ? $employee->jabatan : '-',
            ];
        }

        // 1. Try to clone Block if user uses ${block_pegawai} ... ${/block_pegawai}
        try {
            $templateProcessor->cloneBlock('block_pegawai', 0, true, false, $personnelValues);
        } catch (\Exception $e) {
            // Block not found, ignore
        }

        // 2. Try to clone Row if user uses ${n} in a table
        try {
            $templateProcessor->cloneRowAndSetValues('n', $personnelValues);
        } catch (\Exception $e) {
            // Row tag not found, fallback to single assignment
            $first = $personnelValues[0] ?? null;
            if ($first) {
                $templateProcessor->setValues([
                    'nama' => $first['nama'],
                    'nip' => $first['nip'],
                    'pangkat_gol' => $first['pangkat_gol'],
                    'jabatan' => $first['jabatan'],
                ]);
            }
        }

        $outputPath = storage_path('app/temp_' . time() . '.docx');
        $templateProcessor->saveAs($outputPath);

        return response()->download($outputPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Print HTML Preview
     */
    public function printHtml(Package $package, TravelOrder $travelOrder, $type)
    {
        $this->authorizeAccess($package, $travelOrder);
        $travelOrder->load('personnels.employee');
        
        if ($type === 'sppd') {
            return view('travel-orders.print.sppd', compact('package', 'travelOrder'));
        }

        // Surat Tugas Bupati kini diproduksi sebagai dokumen Word (exportWord),
        // bukan cetak HTML.
        abort(404, 'Tipe dokumen cetak tidak valid');
    }

    /**
     * Print Kuitansi for a specific personnel
     */
    public function printKuitansi(Package $package, TravelOrder $travelOrder, \App\Models\TravelPersonnel $personnel)
    {
        $this->authorizeAccess($package, $travelOrder);
        abort_unless($travelOrder->personnels()->whereKey($personnel->getKey())->exists(), 404);
        $travelOrder->load('personnels.employee');
        $personnel->load('employee');

        return view('travel-orders.print.kuitansi', compact('package', 'travelOrder', 'personnel'));
    }

    /**
     * Print Kuitansi for all personnel of a travel order (one page each)
     */
    public function printKuitansiAll(Package $package, TravelOrder $travelOrder)
    {
        $this->authorizeAccess($package, $travelOrder);
        $travelOrder->load('personnels.employee');
        $personnels = $travelOrder->personnels;

        return view('travel-orders.print.kuitansi', compact('package', 'travelOrder', 'personnels'));
    }

    /**
     * Print Daftar Pengeluaran Riil for all personnel of a travel order (one page each)
     */
    public function printPengeluaranRiil(Package $package, TravelOrder $travelOrder)
    {
        $this->authorizeAccess($package, $travelOrder);
        $travelOrder->load('personnels.employee', 'report');
        $personnels = $travelOrder->personnels;

        return view('travel-orders.print.pengeluaran-riil', compact('package', 'travelOrder', 'personnels'));
    }

    /**
     * Print Laporan Perjalanan Dinas (satu laporan per SPPD, ttd ketua pelaksana)
     */
    public function printLaporan(Package $package, TravelOrder $travelOrder)
    {
        $this->authorizeAccess($package, $travelOrder);
        $travelOrder->load('personnels.employee', 'report');

        abort_if(!$travelOrder->report, 404, 'Laporan belum dibuat.');

        return view('travel-orders.print.laporan', [
            'package' => $package,
            'travelOrder' => $travelOrder,
            'report' => $travelOrder->report,
        ]);
    }

    private function authorizeAccess(Package $package, TravelOrder $travelOrder): void
    {
        Gate::authorize('view', $package);
        Gate::authorize('view', $travelOrder);
        abort_if((int) $travelOrder->package_id !== (int) $package->getKey(), 404);
    }
}
