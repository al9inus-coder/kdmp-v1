<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis PPh ikut disimpan pada pembayaran, bukan dihitung ulang tiap cetak.
 *
 * Sebelumnya persennya dibekukan tetapi jenisnya diturunkan hidup-hidup dari
 * packages.jenis_pengadaan. Begitu jenis pengadaan diubah setelah pembayaran
 * disimpan, keduanya berpisah dan BAP mencetak kombinasi yang tidak ada —
 * "PPh 23 1,5%" pada paket 66638513.
 *
 * SENGAJA TIDAK MEMANGGIL KODE APLIKASI. Migrasi harus tetap berarti sama
 * bertahun-tahun kemudian, sedangkan model dan service terus berubah. Versi
 * pertama migrasi ini memakai PajakPengadaan, lalu gagal di server bersih
 * begitu service itu mulai membaca tabel yang baru dibuat migrasi sesudahnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Idempoten: migrasi ini pernah gagal setelah kolomnya terlanjur
        // ditambah, jadi ia harus bisa dijalankan ulang di atas keadaan itu.
        if (!Schema::hasColumn('procurement_payments', 'jenis_pph')) {
            Schema::table('procurement_payments', function (Blueprint $table) {
                $table->string('jenis_pph')->nullable()->after('skema_pajak');
            });
        }

        $this->backfill();
    }

    /**
     * Isi keputusan pajak yang selama ini tersirat, mengikuti jenis pengadaan
     * paket — keputusan yang diambil bersama pengguna. Persen PPh ikut
     * disesuaikan bila tarif bekunya milik jenis yang lain.
     */
    private function backfill(): void
    {
        // Angka yang dulu tertanam di cetakan BAP sebelum Master Pajak ada.
        // Wajib ada: seeder tarif dijalankan terpisah dari migrasi, dan di
        // produksi ia baru dijalankan SESUDAHNYA — tanpa cadangan ini seluruh
        // persen PPh dibekukan sebagai nol, dan BAP berhenti memotong PPh.
        $bawaan = [
            'ppn' => 11.0,
            'pajak_restoran' => 10.0,
            'pph22_barang' => 1.5,
            'pph23_jasa' => 2.0,
        ];

        $tarif = DB::table('tarif_pajaks')->where('aktif', true)->get();

        $persenTarif = function (string $jenis, ?string $kunci = null) use ($tarif, $bawaan) {
            foreach ($tarif as $t) {
                if ($t->jenis !== $jenis) {
                    continue;
                }
                if ($jenis === 'pph4_2_konstruksi' && $t->kunci !== $kunci) {
                    continue;
                }

                return (float) $t->persen;
            }

            return $bawaan[$jenis] ?? 0.0;
        };

        $bayar = DB::table('procurement_payments as b')
            ->join('procurement_packages as pp', 'pp.id', '=', 'b.procurement_package_id')
            ->join('packages as p', 'p.id', '=', 'pp.package_id')
            ->leftJoin('accounts as a', 'a.id', '=', 'p.account_id')
            ->leftJoin('procurement_processes as pr', 'pr.procurement_package_id', '=', 'pp.id')
            ->select(
                'b.id', 'b.skema_pajak', 'b.kualifikasi_pajak',
                'b.persen_ppn_fix', 'b.persen_restoran_fix', 'b.persen_pph_fix',
                'p.id_rup', 'p.jenis_pengadaan',
                'a.skema_pajak as skema_rekening',
                'pr.nilai_kontrak'
            )
            ->get();

        $berubah = [];

        foreach ($bayar as $r) {
            // Skema tiga lapis, sama seperti yang berlaku saat itu.
            $skema = in_array($r->skema_pajak, ['standar', 'restoran', 'konstruksi'], true)
                ? $r->skema_pajak
                : (in_array($r->skema_rekening, ['standar', 'restoran', 'konstruksi'], true)
                    ? $r->skema_rekening
                    : 'standar');

            $jenis = $skema === 'konstruksi'
                ? 'pph4_2_konstruksi'
                : (str_contains(strtolower((string) $r->jenis_pengadaan), 'barang')
                    ? 'pph22_barang'
                    : 'pph23_jasa');

            $persenBaru = $persenTarif($jenis, $r->kualifikasi_pajak);

            // Potongan menurut aturan yang berlaku SEBELUM migrasi ini: DPP
            // diturunkan dari pajak konsumsi, PPh dihitung dari DPP itu.
            //
            // Kolom _fix yang kosong berarti belum pernah dibekukan, bukan
            // "nol" — saat mencetak, angkanya diambil dari tarif yang berlaku.
            // Memperlakukannya nol membuat laporan ini menyebut seluruh baris
            // bergeser padahal potongannya tetap.
            $nilai = (float) ($r->nilai_kontrak ?? 0);
            $restoran = $skema === 'restoran';
            $persenKonsumsi = $restoran ? $r->persen_restoran_fix : $r->persen_ppn_fix;
            $persenKonsumsi = is_null($persenKonsumsi)
                ? $persenTarif($restoran ? 'pajak_restoran' : 'ppn')
                : (float) $persenKonsumsi;
            $dpp = $persenKonsumsi > 0 ? $nilai / (1 + $persenKonsumsi / 100) : $nilai;

            $persenPphLama = is_null($r->persen_pph_fix)
                ? $persenTarif($jenis, $r->kualifikasi_pajak)
                : (float) $r->persen_pph_fix;
            $pphLama = $dpp * $persenPphLama / 100;
            $pphBaru = $dpp * $persenBaru / 100;

            if (abs($pphBaru - $pphLama) >= 0.005) {
                $berubah[] = sprintf('%s: PPh Rp %s -> Rp %s',
                    $r->id_rup ?? $r->id,
                    number_format($pphLama, 0, ',', '.'),
                    number_format($pphBaru, 0, ',', '.'));
            }

            DB::table('procurement_payments')->where('id', $r->id)->update([
                'skema_pajak' => $skema,
                'jenis_pph' => $jenis,
                'persen_pph_fix' => $persenBaru,
            ]);
        }

        foreach ($berubah as $baris) {
            echo "  potongan bergeser -> {$baris}\n";
        }
        echo '  ' . $bayar->count() . ' pembayaran ditinjau, ' . count($berubah) . " bergeser\n";
    }

    public function down(): void
    {
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->dropColumn('jenis_pph');
        });
    }
};
