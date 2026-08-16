<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FiscalYear;
use App\Models\ImportBatch;
use App\Models\SubActivity;
use App\Services\Anggaran\PembacaDpa;
use App\Services\Anggaran\PencocokDpa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Impor DPA/DPPA/RKA cetakan SIPD untuk satu sub kegiatan.
 *
 * Tidak menulis anggaran sama sekali. Tugasnya membaca berkas, mencocokkannya
 * dengan isi basis data, lalu mengembalikan operator ke halaman sub kegiatan
 * dengan form revisi massal yang sudah terisi. Yang menyimpan tetap
 * BudgetLineController::bulkRevision() — jalur yang sudah terbukti.
 */
class ImporDpaController extends Controller
{
    public function __construct(
        private readonly PembacaDpa $pembaca,
        private readonly PencocokDpa $pencocok,
    ) {}

    public function store(Request $request, SubActivity $subActivity): RedirectResponse
    {
        $data = $request->validate([
            'berkas' => ['required', 'file', 'mimetypes:application/pdf', 'max:10240'],
            'tahun' => ['nullable', 'integer', 'exists:fiscal_years,id'],
        ], [], ['berkas' => 'berkas DPA']);

        $tahunId = $data['tahun'] ?? (FiscalYear::where('is_active', true)->value('id')
            ?? FiscalYear::latest('tahun')->value('id'));
        $tahun = FiscalYear::find($tahunId);

        $kembali = fn () => redirect()->route('admin.anggaran.sub-kegiatan', [$subActivity, 'tahun' => $tahunId]);

        $berkas = $data['berkas'];
        $namaAsli = $berkas->getClientOriginalName();

        // Berkas disimpan lebih dulu supaya tiap angka pagu bisa ditelusuri
        // ke dokumen sumbernya saat pemeriksaan.
        $namaSimpan = uniqid() . '_' . $namaAsli;
        $berkas->move(storage_path('app/private/imports/dpa'), $namaSimpan);
        $jalur = 'imports/dpa/' . $namaSimpan;

        try {
            $hasil = $this->pembaca->baca(storage_path('app/private/' . $jalur));
        } catch (Throwable $e) {
            return $kembali()->with('error', 'Berkas tidak dapat dibaca: ' . $e->getMessage());
        }

        // ── Penjaga: menahan sebelum apa pun ditampilkan ──────────────
        $keberatan = $this->pembaca->periksa($hasil);

        if ($hasil['tahun'] && $tahun && (int) $hasil['tahun'] !== (int) $tahun->tahun) {
            $keberatan[] = "Dokumen ini tahun anggaran {$hasil['tahun']}, sedangkan yang dibuka tahun {$tahun->tahun}.";
        }

        if ($hasil['subKegiatanKode'] && $hasil['subKegiatanKode'] !== $subActivity->kode) {
            $keberatan[] = "Dokumen ini untuk sub kegiatan {$hasil['subKegiatanKode']}, "
                . "sedangkan halaman ini {$subActivity->kode}.";
        }

        if ($keberatan) {
            return $kembali()->with('error', 'Impor dibatalkan. ' . implode(' ', $keberatan));
        }

        $cocok = $this->pencocok->cocokkan($hasil, $subActivity, $tahunId);

        $batch = ImportBatch::create([
            'fiscal_year_id' => $tahunId,
            'created_by' => Auth::id(),
            'file_name' => $namaAsli,
            'file_path' => $jalur,
            'status' => 'menunggu_tinjauan',
            'total_rows' => count($cocok['baris']),
            'success_rows' => 0,
            'failed_rows' => 0,
            'notes' => trim(($hasil['jenisDokumen'] ?? '') . ' ' . ($hasil['nomor'] ?? '')),
        ]);

        // Hasilnya dititipkan ke session, bukan disimpan sebagai keadaan
        // tersendiri: ia hanya berumur satu lompatan sampai form terisi, dan
        // menyimpannya justru menciptakan data turunan yang bisa basi.
        return $kembali()->with('imporDpa', [
            'batch_id' => $batch->id,
            'dokumen' => $hasil,
            'baris' => $cocok['baris'],
            'ringkasan' => $cocok['ringkasan'],
            'nama_berkas' => $namaAsli,
        ]);
    }
}
