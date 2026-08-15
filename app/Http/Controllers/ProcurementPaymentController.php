<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ProcurementPackage;
use App\Models\ProcurementAddendum;
use App\Models\ProcurementPayment;
use Illuminate\Http\Request;

class ProcurementPaymentController extends Controller
{
    public function storeAddendum(Request $request, Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        
        $request->validate([
            'nomor' => 'required|string',
            'tanggal_akhir_baru' => 'required|date',
            'alasan' => 'required|string',
        ]);

        $procurementPackage->addendums()->create([
            'nomor' => $request->nomor,
            'tanggal_akhir_baru' => $request->tanggal_akhir_baru,
            'alasan' => $request->alasan,
        ]);

        // Update tanggal akhir di procurement_processes
        if ($procurementPackage->procurementProcess) {
            $procurementPackage->procurementProcess->update([
                'tanggal_barang_diterima' => $request->tanggal_akhir_baru,
            ]);
        }

        return redirect()->back()->with('success', 'Adendum Kontrak berhasil disimpan, batas waktu pelaksanaan telah diperbarui.');
    }

    public function storePayment(Request $request, Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        
        $request->validate([
            'nomor_bast' => 'required|string',
            'tanggal_bast' => 'required|date',
            'nomor_invoice' => 'required|string',
            'tanggal_invoice' => 'required|date',
            'nomor_bap' => 'required|string',
            'tanggal_bap' => 'required|date',
            'nomor_kwitansi' => 'required|string',
            'tanggal_kwitansi' => 'required|date',
            'tanggal_ringkasan_kontrak' => 'required|date',
            'tanggal_non_pkp' => 'required_if:is_non_pkp,1|date|nullable',
            'nama_pptk' => 'required|string',
            'nip_pptk' => 'required|string',
            'pangkat_golongan_pptk' => 'required|string',
            'skema_pajak' => 'nullable|string|in:' . implode(',', array_keys(\App\Services\Pajak\SkemaPajak::pilihan())),
            'jenis_pph' => 'nullable|string|in:' . implode(',', array_keys(\App\Services\Pajak\PajakPengadaan::pilihanJenisPph())),
            ...\App\Services\Pajak\PajakPengadaan::aturanValidasi(),
            'kualifikasi_pajak' => 'nullable|string|max:255',
        ]);

        $procurementPackage->payment()->updateOrCreate(
            ['procurement_package_id' => $procurementPackage->id],
            [
                'nomor_bast' => $request->nomor_bast,
                'tanggal_bast' => $request->tanggal_bast,
                'nomor_invoice' => $request->nomor_invoice,
                'tanggal_invoice' => $request->tanggal_invoice,
                'nomor_bap' => $request->nomor_bap,
                'tanggal_bap' => $request->tanggal_bap,
                'nomor_kwitansi' => $request->nomor_kwitansi,
                'tanggal_kwitansi' => $request->tanggal_kwitansi,
                'is_non_pkp' => $request->boolean('is_non_pkp'),
                'tanggal_non_pkp' => $request->tanggal_non_pkp,
                'tanggal_ringkasan_kontrak' => $request->tanggal_ringkasan_kontrak,
                'nama_pptk' => $request->nama_pptk,
                'nip_pptk' => $request->nip_pptk,
                'pangkat_golongan_pptk' => $request->pangkat_golongan_pptk,
                'skema_pajak' => $request->skema_pajak,
                'jenis_pph' => $request->jenis_pph,
                'kualifikasi_pajak' => $request->kualifikasi_pajak,
            ]
        );

        // Kunci seluruh keputusan pajaknya — alasannya di PajakPengadaan::bekukan().
        // Daftar kosong berarti user memang tidak menerapkan pajak apa pun, jadi
        // dibedakan dari "tidak dikirim sama sekali" yang jatuh ke usulan sistem.
        $procurementPackage->refresh()->load('payment.pajaks', 'package.account', 'procurementProcess');
        \App\Services\Pajak\PajakPengadaan::bekukan(
            $procurementPackage,
            $request->boolean('pajak_diisi') ? $request->input('pajak', []) : null
        );

        // Update workflow status
        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_PAYMENT_PROCESS
        ]);

        // Nama route halaman pembayaran berbeda antar role:
        // admin.procurement-packages.payment vs kabid.procurement-packages.payment.show
        $tujuan = auth()->user()->hasAnyRole(['Admin', 'Super Admin'])
            ? 'admin.procurement-packages.payment'
            : 'kabid.procurement-packages.payment.show';

        return redirect()->route($tujuan, $package)->with('success', 'Pekerjaan dinyatakan Selesai. Selamat datang di tahap Pembayaran!');
    }

    /**
     * Pratinjau potongan pajak untuk pilihan yang sedang dilihat operator,
     * sebelum disimpan.
     *
     * Perhitungannya tetap lewat PajakPengadaan::hitung() — bukan salinan
     * rumus di JavaScript. Rumus DPP terikat pada tarif yang sama dengan yang
     * memotong, dan menyalinnya ke sisi klien akan menghidupkan kembali
     * jebakan dua angka lepas yang dulu bisa membuat BAP salah diam-diam.
     */
    public function pratinjauPajak(Request $request, Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        abort_if(!$procurementPackage, 404);

        $data = $request->validate([
            'skema_pajak' => 'nullable|string|in:' . implode(',', array_keys(\App\Services\Pajak\SkemaPajak::pilihan())),
            'jenis_pph' => 'nullable|string|in:' . implode(',', array_keys(\App\Services\Pajak\PajakPengadaan::pilihanJenisPph())),
            ...\App\Services\Pajak\PajakPengadaan::aturanValidasi(),
            'kualifikasi_pajak' => 'nullable|string|max:255',
        ]);

        // Salinan di memori: pilihan dicoba tanpa menyentuh baris tersimpan.
        // Baris pajaknya dikosongkan supaya yang dipakai pilihan yang sedang
        // dilihat, bukan yang sudah beku, dan nilai kontraknya yang berlaku
        // sekarang — pratinjau memang menjawab "kalau disimpan sekarang".
        $bayar = ($procurementPackage->payment?->replicate() ?? new ProcurementPayment())->forceFill([
            'skema_pajak' => $data['skema_pajak'] ?? null,
            'jenis_pph' => $data['jenis_pph'] ?? null,
            'kualifikasi_pajak' => $data['kualifikasi_pajak'] ?? null,
            'nilai_kontrak_fix' => null,
        ]);
        $bayar->setRelation('pajaks', collect());

        $nilai = (float) ($procurementPackage->procurementProcess->nilai_kontrak ?? 0);
        $salinan = (clone $procurementPackage)->setRelation('payment', $bayar);

        $baris = \App\Services\Pajak\PajakPengadaan::nilaiBaris(
            $request->has('pajak')
                ? \App\Services\Pajak\PajakPengadaan::rapikan($request->input('pajak', []))
                : \App\Services\Pajak\PajakPengadaan::usulan($salinan),
            $nilai
        );

        $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
        $total = array_sum(array_column($baris, 'nominal'));

        return response()->json([
            // Dipakai tombol "Pakai usulan sistem" untuk mengisi ulang form.
            'usulan' => array_map(fn ($b) => [
                'jenis' => $b['jenis'],
                'kunci' => $b['kunci'] ?? '',
                'persen' => rtrim(rtrim(number_format((float) $b['persen'], 2, '.', ''), '0'), '.'),
                'dasar' => $b['dasar'],
            ], $baris),
            'nilaiKontrak' => $rp($nilai),
            'baris' => array_map(fn ($b) => [
                'jenis' => $b['jenis'],
                'label' => \App\Services\Pajak\PajakPengadaan::labelBaris($b),
                'dasar' => \App\Services\Pajak\PajakPengadaan::pilihanDasar()[$b['dasar']] ?? $b['dasar'],
                'nilaiDasar' => $rp($b['nilaiDasar']),
                'nominal' => $rp($b['nominal']),
            ], $baris),
            'totalPotongan' => $rp($total),
            'jumlahBayar' => $rp($nilai - $total),
            'adaNilaiKontrak' => $nilai > 0,
        ]);
    }

    public function previewDocument(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        $process = $procurementPackage->procurementProcess;
        $payment = $procurementPackage->payment;

        return view('procurement-payments.preview-document', compact('procurementPackage', 'process', 'payment'));
    }

    public function printDocument(Package $package, \Illuminate\Http\Request $request)
    {
        $procurementPackage = $package->procurementPackage;
        $process = $procurementPackage->procurementProcess;
        $payment = $procurementPackage->payment;
        $type = $request->get('type', 'all');

        return view('procurement-payments.print-document', compact('procurementPackage', 'process', 'payment', 'type'));
    }

    public function complete(Package $package)
    {
        $procurementPackage = $package->procurementPackage;
        
        $procurementPackage->update([
            'workflow_status' => ProcurementPackage::WORKFLOW_COMPLETED
        ]);

        return redirect()->route((auth()->user()->hasRole('Kabid') ? 'kabid.' : 'admin.') . 'procurement-packages.index')->with('success', 'Seluruh Proses Pengadaan telah selesai!');
    }
}
