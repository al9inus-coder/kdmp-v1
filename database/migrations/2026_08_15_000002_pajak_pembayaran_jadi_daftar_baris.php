<?php

use App\Models\TarifPajak;
use App\Services\Pajak\PajakPengadaan;
use App\Services\Pajak\SkemaPajak;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pajak pembayaran jadi daftar baris, dan rupiahnya ikut dibekukan.
 *
 * Sebelumnya jenis pajak konsumsi tersimpan tersirat lewat kolom mana yang
 * terisi (persen_ppn_fix vs persen_restoran_fix), tidak ada cara menyatakan
 * "tidak dipungut", dan dasar pengenaan tidak bisa dipilih sama sekali.
 *
 * Nominalnya juga tidak pernah disimpan — dihitung ulang dari nilai_kontrak
 * tiap dicetak — sehingga adendum atau koreksi ketik menulis ulang BAP yang
 * sudah ditandatangani.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_payment_pajaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_payment_id')->constrained()->cascadeOnDelete();
            $table->string('jenis');
            $table->string('kunci')->nullable();
            $table->decimal('persen', 5, 2);
            $table->string('dasar')->default(PajakPengadaan::DASAR_BRUTO);
            $table->decimal('nilai_dasar', 18, 2)->default(0);
            $table->decimal('nominal', 18, 2)->default(0);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            // Satu pajak hanya sekali per pembayaran.
            $table->unique(['procurement_payment_id', 'jenis']);
        });

        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->decimal('nilai_kontrak_fix', 18, 2)->nullable()->after('kualifikasi_pajak');
        });

        $this->seragamkanDasarTarif();
        $this->pindahkan();

        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->dropColumn(['persen_ppn_fix', 'persen_pph_fix', 'persen_restoran_fix']);
        });
    }

    /**
     * Kolom dasar pada tarif_pajaks selama ini tidak menggerakkan apa pun, dan
     * isinya tidak cocok dengan kenyataan: pajak_restoran tertulis "bruto"
     * padahal selalu dihitung dari DPP. Sekarang kolom itu benar-benar dipakai
     * sebagai dasar bawaan pada usulan, jadi isinya diseragamkan ke kosakata
     * baru mengikuti perilaku yang selama ini berjalan.
     */
    private function seragamkanDasarTarif(): void
    {
        $peta = [
            TarifPajak::PPN => PajakPengadaan::DASAR_SETELAH_PPN,
            TarifPajak::PAJAK_RESTORAN => PajakPengadaan::DASAR_SETELAH_PBJT,
            TarifPajak::PPH22_BARANG => PajakPengadaan::DASAR_SETELAH_PPN,
            TarifPajak::PPH23_JASA => PajakPengadaan::DASAR_SETELAH_PPN,
            TarifPajak::PPH4_2_KONSTRUKSI => PajakPengadaan::DASAR_SETELAH_PPN,
            // PPh 21 lembur memang dihitung dari bruto, dan tetap begitu.
            TarifPajak::PPH21 => PajakPengadaan::DASAR_BRUTO,
        ];

        foreach ($peta as $jenis => $dasar) {
            DB::table('tarif_pajaks')->where('jenis', $jenis)->update(['dasar' => $dasar]);
        }
    }

    /**
     * Tiga kolom persen jadi baris, beserta rupiah yang selama ini dihitung
     * hidup. Angkanya wajib tidak bergeser sesen pun.
     */
    private function pindahkan(): void
    {
        $bayar = DB::table('procurement_payments as b')
            ->join('procurement_packages as pp', 'pp.id', '=', 'b.procurement_package_id')
            ->leftJoin('procurement_processes as pr', 'pr.procurement_package_id', '=', 'pp.id')
            ->select('b.*', 'pr.nilai_kontrak')
            ->get();

        $dipindah = 0;

        foreach ($bayar as $r) {
            $nilai = (float) ($r->nilai_kontrak ?? 0);
            $restoran = $r->skema_pajak === SkemaPajak::RESTORAN;

            $pilihan = [];

            // Baris pajak konsumsi. Dasarnya "setelah dirinya sendiri" — itulah
            // arti nilai kontrak yang sudah termasuk pajak: DPP = nilai / (1+p).
            $persenKonsumsi = $restoran ? $r->persen_restoran_fix : $r->persen_ppn_fix;
            if (!is_null($persenKonsumsi)) {
                $pilihan[] = [
                    'jenis' => $restoran ? TarifPajak::PAJAK_RESTORAN : TarifPajak::PPN,
                    'kunci' => null,
                    'persen' => (float) $persenKonsumsi,
                    'dasar' => $restoran
                        ? PajakPengadaan::DASAR_SETELAH_PBJT
                        : PajakPengadaan::DASAR_SETELAH_PPN,
                ];
            }

            if (!is_null($r->persen_pph_fix) && $r->jenis_pph) {
                $pilihan[] = [
                    'jenis' => $r->jenis_pph,
                    'kunci' => $r->kualifikasi_pajak,
                    'persen' => (float) $r->persen_pph_fix,
                    'dasar' => $restoran
                        ? PajakPengadaan::DASAR_SETELAH_PBJT
                        : PajakPengadaan::DASAR_SETELAH_PPN,
                ];
            }

            if (!$pilihan) {
                continue;
            }

            $baris = PajakPengadaan::nilaiBaris($pilihan, $nilai);

            DB::table('procurement_payments')->where('id', $r->id)
                ->update(['nilai_kontrak_fix' => $nilai]);

            foreach ($baris as $urutan => $b) {
                DB::table('procurement_payment_pajaks')->insert([
                    'procurement_payment_id' => $r->id,
                    'jenis' => $b['jenis'],
                    'kunci' => $b['kunci'],
                    'persen' => $b['persen'],
                    'dasar' => $b['dasar'],
                    'nilai_dasar' => round($b['nilaiDasar'], 2),
                    'nominal' => round($b['nominal'], 2),
                    'urutan' => $urutan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $dipindah++;
        }

        echo "  {$dipindah} pembayaran dipindahkan ke daftar baris pajak\n";
    }

    public function down(): void
    {
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->decimal('persen_ppn_fix', 5, 2)->nullable();
            $table->decimal('persen_pph_fix', 5, 2)->nullable();
            $table->decimal('persen_restoran_fix', 5, 2)->nullable();
            $table->dropColumn('nilai_kontrak_fix');
        });

        Schema::dropIfExists('procurement_payment_pajaks');
    }
};
