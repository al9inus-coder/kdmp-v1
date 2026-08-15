<?php

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
 *
 * Seperti migrasi sebelumnya: tidak memanggil kode aplikasi. Nilai kosakata
 * ditulis harfiah supaya arti migrasi ini tidak ikut berubah bila konstanta
 * di service kelak diganti.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('procurement_payment_pajaks')) {
            Schema::create('procurement_payment_pajaks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('procurement_payment_id')->constrained()->cascadeOnDelete();
                $table->string('jenis');
                $table->string('kunci')->nullable();
                $table->decimal('persen', 5, 2);
                $table->string('dasar')->default('bruto');
                $table->decimal('nilai_dasar', 18, 2)->default(0);
                $table->decimal('nominal', 18, 2)->default(0);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();

                // Satu pajak hanya sekali per pembayaran.
                $table->unique(['procurement_payment_id', 'jenis']);
            });
        }

        if (!Schema::hasColumn('procurement_payments', 'nilai_kontrak_fix')) {
            Schema::table('procurement_payments', function (Blueprint $table) {
                $table->decimal('nilai_kontrak_fix', 18, 2)->nullable()->after('kualifikasi_pajak');
            });
        }

        $this->seragamkanDasarTarif();
        $this->pindahkan();

        foreach (['persen_ppn_fix', 'persen_pph_fix', 'persen_restoran_fix'] as $kolom) {
            if (Schema::hasColumn('procurement_payments', $kolom)) {
                Schema::table('procurement_payments', fn (Blueprint $t) => $t->dropColumn($kolom));
            }
        }
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
            'ppn' => 'setelah_ppn',
            'pajak_restoran' => 'setelah_pbjt',
            'pph22_barang' => 'setelah_ppn',
            'pph23_jasa' => 'setelah_ppn',
            'pph4_2_konstruksi' => 'setelah_ppn',
            // PPh 21 lembur memang dihitung dari bruto, dan tetap begitu.
            'pph21' => 'bruto',
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
        // Sudah pernah dipindahkan pada percobaan sebelumnya? Jangan digandakan.
        if (DB::table('procurement_payment_pajaks')->exists()) {
            echo "  baris pajak sudah ada, pemindahan dilewati\n";

            return;
        }

        // Tarif yang berlaku, dipakai untuk pembayaran yang persennya belum
        // pernah dibekukan. Pada pemasangan baru SELURUH kolom _fix masih NULL
        // — belum ada yang disimpan lewat form pajak — dan memperlakukannya
        // sebagai "tidak dipungut" akan menghapus baris PPN dari BAP yang
        // selama ini tercetak. Yang benar: bekukan tarif yang berlaku, sebab
        // itulah angka yang selama ini dihitung hidup saat mencetak.
        // Cadangan terakhir: angka yang dulu tertanam di cetakan BAP sebelum
        // Master Pajak ada. Diperlukan karena tabel tarif bisa saja masih
        // kosong saat migrasi ini jalan — seeder dijalankan terpisah, dan di
        // produksi ia memang baru dijalankan sesudahnya. Tanpa cadangan ini
        // baris PPN tidak pernah dibuat, dan BAP kehilangan potongannya.
        $bawaan = [
            'ppn' => 11.0,
            'pajak_restoran' => 10.0,
            'pph22_barang' => 1.5,
            'pph23_jasa' => 2.0,
        ];

        $tarifAktif = DB::table('tarif_pajaks')->where('aktif', true)->get();
        $persenTarif = function (string $jenis) use ($tarifAktif, $bawaan) {
            foreach ($tarifAktif as $t) {
                if ($t->jenis === $jenis) {
                    return (float) $t->persen;
                }
            }

            return $bawaan[$jenis] ?? null;
        };

        $bayar = DB::table('procurement_payments as b')
            ->join('procurement_packages as pp', 'pp.id', '=', 'b.procurement_package_id')
            ->leftJoin('procurement_processes as pr', 'pr.procurement_package_id', '=', 'pp.id')
            ->select('b.*', 'pr.nilai_kontrak')
            ->get();

        $dipindah = 0;

        foreach ($bayar as $r) {
            $nilai = (float) ($r->nilai_kontrak ?? 0);
            $restoran = $r->skema_pajak === 'restoran';

            // Dasarnya "setelah dirinya sendiri" — itulah arti nilai kontrak
            // yang sudah termasuk pajak: DPP = nilai / (1 + p/100).
            $dasar = $restoran ? 'setelah_pbjt' : 'setelah_ppn';
            $jenisKonsumsi = $restoran ? 'pajak_restoran' : 'ppn';

            $persenKonsumsi = $restoran ? $r->persen_restoran_fix : $r->persen_ppn_fix;
            $persenKonsumsi = is_null($persenKonsumsi)
                ? $persenTarif($jenisKonsumsi)
                : (float) $persenKonsumsi;

            $persenPph = is_null($r->persen_pph_fix)
                ? ($r->jenis_pph ? $persenTarif($r->jenis_pph) : null)
                : (float) $r->persen_pph_fix;

            $baris = [];

            if (!is_null($persenKonsumsi)) {
                $baris[] = [
                    'jenis' => $jenisKonsumsi,
                    'kunci' => null,
                    'persen' => $persenKonsumsi,
                    'dasar' => $dasar,
                ];
            }

            if (!is_null($persenPph) && $r->jenis_pph) {
                $baris[] = [
                    'jenis' => $r->jenis_pph,
                    'kunci' => $r->kualifikasi_pajak,
                    'persen' => $persenPph,
                    'dasar' => $dasar,
                ];
            }

            if (!$baris) {
                continue;
            }

            // Pembagi diturunkan dari persen pajak konsumsi yang sama —
            // rumus yang sama dengan yang dipakai sebelum migrasi ini.
            $pembagi = (float) ($persenKonsumsi ?? 0);
            $nilaiDasar = $pembagi > 0 ? $nilai / (1 + $pembagi / 100) : $nilai;

            DB::table('procurement_payments')->where('id', $r->id)
                ->update(['nilai_kontrak_fix' => $nilai]);

            foreach ($baris as $urutan => $b) {
                DB::table('procurement_payment_pajaks')->insert([
                    'procurement_payment_id' => $r->id,
                    'jenis' => $b['jenis'],
                    'kunci' => $b['kunci'],
                    'persen' => $b['persen'],
                    'dasar' => $b['dasar'],
                    'nilai_dasar' => round($nilaiDasar, 2),
                    'nominal' => round($nilaiDasar * $b['persen'] / 100, 2),
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
