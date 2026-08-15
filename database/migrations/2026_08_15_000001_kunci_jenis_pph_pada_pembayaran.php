<?php

use App\Models\ProcurementPackage;
use App\Models\TarifPajak;
use App\Services\Pajak\PajakPengadaan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis PPh ikut disimpan pada pembayaran, bukan dihitung ulang tiap cetak.
 *
 * Sebelumnya persennya dibekukan tetapi jenisnya diturunkan hidup-hidup dari
 * packages.jenis_pengadaan. Begitu jenis pengadaan diubah setelah pembayaran
 * disimpan, keduanya berpisah dan BAP mencetak kombinasi yang tidak ada —
 * "PPh 23 1,5%" pada paket 66638513.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->string('jenis_pph')->nullable()->after('skema_pajak');
        });

        $this->backfill();
    }

    /**
     * Isi keputusan pajak yang selama ini tersirat, mengikuti jenis pengadaan
     * paket — keputusan yang diambil bersama pengguna. Persen PPh ikut
     * disesuaikan bila tarif bekunya milik jenis yang lain.
     */
    private function backfill(): void
    {
        $tarif = TarifPajak::aktif()->get();
        $berubah = [];

        $semua = ProcurementPackage::with(['package.account', 'procurementProcess', 'payment'])->get();

        foreach ($semua as $pp) {
            if (!$pp->payment) {
                continue;
            }

            // Yang dibandingkan potongan yang benar-benar dihitung, bukan isi
            // kolom bekunya. Kolom beku yang kosong berarti "ikut tarif hidup",
            // bukan "dipotong nol" — memperlakukannya sebagai nol akan
            // melaporkan seluruh baris bergeser padahal angkanya tetap.
            $sebelum = PajakPengadaan::hitung($pp, $tarif);

            // Lewat bekukan() supaya backfill mengunci hal yang persis sama
            // dengan yang dikunci saat operator menyimpan — termasuk pajak
            // konsumsinya. Menuliskannya sendiri di sini sempat hanya mengunci
            // PPh, sehingga pembayaran lama masih ikut bergeser saat PPN
            // disesuaikan lewat /admin/pajak.
            PajakPengadaan::bekukan($pp);

            $sesudah = PajakPengadaan::hitung(
                $pp->fresh(['package.account', 'procurementProcess', 'payment']), $tarif);

            if (abs($sesudah['totalPotongan'] - $sebelum['totalPotongan']) >= 0.005) {
                $berubah[] = sprintf('%s: %s -> %s, potongan Rp %s -> Rp %s',
                    $pp->package?->id_rup ?? $pp->id,
                    $sebelum['labelPph'], $sesudah['labelPph'],
                    number_format($sebelum['totalPotongan'], 0, ',', '.'),
                    number_format($sesudah['totalPotongan'], 0, ',', '.'));
            }
        }

        foreach ($berubah as $baris) {
            echo "  potongan bergeser -> {$baris}\n";
        }
        echo '  ' . $semua->count() . " paket ditinjau, " . count($berubah) . " pembayaran bergeser\n";
    }

    public function down(): void
    {
        Schema::table('procurement_payments', function (Blueprint $table) {
            $table->dropColumn('jenis_pph');
        });
    }
};
