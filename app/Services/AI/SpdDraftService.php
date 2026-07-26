<?php

namespace App\Services\AI;

use App\Http\Controllers\TravelOrderController;
use App\Models\Employee;
use App\Models\Package;
use App\Models\SbuTransportRate;
use App\Models\TravelOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Resolusi & eksekusi draf SPD dari AI Assistant.
 *
 * Prinsip: setiap slot harus punya sumber — disebut pengguna, diambil dari
 * data KDMP, atau usulan AI yang ditandai. Tidak ada nilai bawaan diam-diam.
 * Eksekusi HANYA membaca payload yang tersimpan di server (ai_jobs),
 * tidak pernah dari kiriman browser.
 *
 * Bentuk satu slot:
 *   ['nilai' => mixed, 'status' => 'ok'|'kosong'|'pilih',
 *    'sumber' => 'user'|'kdmp'|'usulan'|null, 'catatan' => ?string,
 *    'tampil' => ?mixed, 'opsi' => ?array]
 */
class SpdDraftService
{
    public const TIPE_PERJALANAN = ['Dalam Daerah', 'Luar Daerah'];

    /** Seluruh slot draf SPD, urut sesuai tampilan kartu. */
    public const SLOT = [
        'personel', 'paket', 'tujuan', 'tipe_perjalanan',
        'tanggal_berangkat', 'tanggal_kembali', 'maksud', 'dasar_pelaksanaan',
    ];

    public const SLOT_TEKS = ['tujuan', 'maksud', 'dasar_pelaksanaan', 'tanggal_berangkat', 'tanggal_kembali'];

    private const BULAN = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    // ── Pembentukan awal dari entitas mentah ──────────────────────

    public function resolveAwal(array $mentah): array
    {
        $slots = [];

        // Personel — dicocokkan ke master pegawai dinas, bukan hafalan AI.
        $slots['personel'] = $this->resolvePersonel($mentah['personel_mentah'] ?? null);

        // Paket belanja — SELALU dipilih pengguna; AI hanya menyodorkan kandidat.
        $kandidat = $this->kandidatPaket();
        $slots['paket'] = $kandidat === []
            ? $this->slot(null, 'kosong', null, 'tidak ada paket belanja perjalanan dinas yang berstatus disetujui')
            : $this->slot(null, 'pilih', null, null, null, $kandidat);

        $tujuan = $mentah['tujuan_mentah'] ?? null;
        $tujuan = $tujuan ? $this->tanpaAwalanKe($tujuan) : null;
        $slots['tujuan'] = $tujuan
            ? $this->slot($tujuan, 'ok', 'user')
            : $this->slot(null, 'kosong');

        $slots['tipe_perjalanan'] = $this->slot(null, 'pilih', null, null, null, self::TIPE_PERJALANAN);

        $slots['tanggal_berangkat'] = $this->resolveTanggal($mentah['tanggal_mentah'] ?? null);
        $slots['tanggal_kembali'] = $this->slot(null, 'kosong');

        // Maksud — usulan AI dari kalimat pengguna, ditandai & bisa diedit.
        $maksud = $mentah['maksud_mentah'] ?? null;
        if (! $maksud && $tujuan) {
            $maksud = 'Perjalanan dinas ke ' . $tujuan;
        }
        $slots['maksud'] = $maksud
            ? $this->slot($maksud, 'ok', 'usulan')
            : $this->slot(null, 'kosong');

        // Dasar pelaksanaan — tidak pernah dikarang; selalu ditanya.
        $slots['dasar_pelaksanaan'] = $this->slot(null, 'kosong', null, 'nomor surat tugas/disposisi; tulis "belum ada" bila memang belum ada');

        return $this->lengkapiOtomatis($slots);
    }

    // ── Pembaruan satu slot (dari kartu atau jawaban chat) ────────

    /**
     * @throws InvalidArgumentException bila nilainya tidak sah
     */
    public function updateSlot(array $slots, string $slot, mixed $nilai): array
    {
        $slots[$slot] = match ($slot) {
            'personel' => $this->resolvePersonelIds((array) $nilai),
            'paket' => $this->resolvePaketId((int) $nilai),
            'tujuan' => $this->slotTeks($this->tanpaAwalanKe((string) $nilai), 'tempat tujuan'),
            'maksud' => $this->slotTeks($nilai, 'maksud perjalanan'),
            'dasar_pelaksanaan' => $this->slotTeks($nilai, 'dasar pelaksanaan'),
            'tipe_perjalanan' => $this->resolveTipe((string) $nilai),
            'tanggal_berangkat', 'tanggal_kembali' => $this->resolveTanggal((string) $nilai),
            default => throw new InvalidArgumentException("Slot tidak dikenal: {$slot}"),
        };

        return $this->periksaSilang($this->lengkapiOtomatis($slots));
    }

    /** Jawaban bebas dari chat untuk slot teks yang sedang ditunggu. */
    public function jawabSlot(array $slots, string $slot, string $teks): array
    {
        if (! in_array($slot, self::SLOT_TEKS, true)) {
            return $slots;
        }

        return $this->updateSlot($slots, $slot, trim($teks));
    }

    /**
     * Terapkan hasil ekstraksi LLM: banyak field sekaligus, TANPA melempar
     * exception — field yang tak sah menjadi catatan pada slotnya, sisanya
     * tetap terisi. Slot yang sudah terisi tidak ditimpa nilai kosong.
     */
    public function terapkanEkstraksi(array $slots, array $fields): array
    {
        if ($slots === []) {
            $slots = $this->resolveAwal([]);
        }

        // Personel: union dengan yang sudah terpilih — "tambah Willeam"
        // tidak menghapus pelaksana sebelumnya.
        foreach ((array) ($fields['personel_disebut'] ?? []) as $nama) {
            $hasil = $this->resolvePersonel($nama);

            if ($hasil['status'] === 'ok') {
                $ids = array_values(array_unique(array_merge(
                    array_map('intval', (array) ($slots['personel']['nilai'] ?? [])),
                    $hasil['nilai']
                )));
                $slots['personel'] = $this->resolvePersonelIds($ids);
            } else {
                // Ambigu / tak ketemu → catatan, jangan hapus yang sudah ada.
                $slots['personel']['catatan'] = $hasil['catatan'];
                if (($slots['personel']['nilai'] ?? []) === []) {
                    $slots['personel']['status'] = $hasil['status'];
                }
            }
        }

        foreach (['tujuan', 'maksud', 'dasar_pelaksanaan'] as $field) {
            $teks = trim((string) ($fields[$field] ?? ''));
            if ($teks !== '') {
                if ($field === 'tujuan') {
                    $teks = $this->tanpaAwalanKe($teks);
                }
                $slots[$field] = $this->slot($teks, 'ok', 'user');
            }
        }

        foreach (['tanggal_berangkat', 'tanggal_kembali'] as $field) {
            $teks = trim((string) ($fields[$field] ?? ''));
            if ($teks !== '') {
                $slots[$field] = $this->resolveTanggal($teks);
            }
        }

        $tipe = $fields['tipe_perjalanan'] ?? null;
        if (in_array($tipe, self::TIPE_PERJALANAN, true)) {
            $slots['tipe_perjalanan'] = $this->slot($tipe, 'ok', 'user');
        }

        $paketId = $fields['paket_pilihan_id'] ?? null;
        if ($paketId) {
            try {
                $slots['paket'] = $this->resolvePaketId((int) $paketId);
            } catch (InvalidArgumentException $e) {
                $slots['paket']['catatan'] = $e->getMessage();
            }
        }

        // Usulan maksud bila tujuan sudah ada tapi maksud belum disebut.
        if (($slots['maksud']['status'] ?? 'kosong') !== 'ok' && ($slots['tujuan']['status'] ?? '') === 'ok') {
            $slots['maksud'] = $this->slot('Perjalanan dinas ke ' . $slots['tujuan']['nilai'], 'ok', 'usulan');
        }

        return $this->periksaSilang($this->lengkapiOtomatis($slots));
    }

    /** Ringkasan status slot untuk konteks LLM — tanpa data pribadi. */
    public function statusRingkas(array $slots): array
    {
        return collect($slots)
            ->map(fn ($s) => ($s['status'] ?? 'kosong') === 'ok' ? 'terisi' : 'kosong')
            ->all();
    }

    /** Kandidat paket {id, label} untuk konteks LLM. */
    public function kandidatUntukLlm(): array
    {
        return array_map(
            fn ($p) => ['id' => $p['id'], 'label' => $p['label'] . ' — ' . $p['sub_kegiatan']],
            $this->kandidatPaket()
        );
    }

    // ── Eksekusi (dipanggil HANYA dengan payload dari server) ─────

    /**
     * Validasi ulang seluruh slot terhadap database lalu tulis TravelOrder.
     *
     * @return array{travel_order: TravelOrder, package: Package}
     * @throws InvalidArgumentException dengan pesan yang layak tampil ke pengguna
     */
    public function eksekusi(array $slots, int $createdBy): array
    {
        $ids = array_map('intval', (array) ($slots['personel']['nilai'] ?? []));
        $employees = Employee::whereIn('id', $ids)->where('tipe', 'dinas')->get();
        if ($ids === [] || $employees->count() !== count($ids)) {
            throw new InvalidArgumentException('Pelaksana perjalanan tidak sah — pilih ulang pegawainya.');
        }

        $package = Package::query()
            ->whereKey((int) ($slots['paket']['nilai'] ?? 0))
            ->where('status', 'approved')
            ->whereHas('account', fn ($q) => $q->where('nama', 'like', '%perjalanan dinas%'))
            ->first();
        if (! $package) {
            throw new InvalidArgumentException('Paket belanja tidak sah atau bukan paket perjalanan dinas.');
        }

        $tipe = (string) ($slots['tipe_perjalanan']['nilai'] ?? '');
        if (! in_array($tipe, self::TIPE_PERJALANAN, true)) {
            throw new InvalidArgumentException('Tipe perjalanan belum dipilih.');
        }

        $berangkat = $this->tanggalSah($slots['tanggal_berangkat']['nilai'] ?? null, 'tanggal berangkat');
        $kembali = $this->tanggalSah($slots['tanggal_kembali']['nilai'] ?? null, 'tanggal kembali');
        if ($kembali->lt($berangkat)) {
            throw new InvalidArgumentException('Tanggal kembali mendahului tanggal berangkat.');
        }

        foreach (['tujuan' => 'tempat tujuan', 'maksud' => 'maksud perjalanan', 'dasar_pelaksanaan' => 'dasar pelaksanaan'] as $kunci => $label) {
            if (trim((string) ($slots[$kunci]['nilai'] ?? '')) === '') {
                throw new InvalidArgumentException("Kolom {$label} masih kosong.");
            }
        }

        // Satu pegawai tidak boleh punya dua perjalanan yang beririsan.
        $bentrok = TravelOrder::bentrokJadwal($ids, $berangkat->toDateString(), $kembali->toDateString());
        if ($bentrok) {
            throw new InvalidArgumentException(TravelOrder::pesanBentrok($bentrok));
        }

        $kalkulator = app(TravelOrderController::class);

        $travelOrder = DB::transaction(function () use ($package, $slots, $tipe, $berangkat, $kembali, $employees, $createdBy, $kalkulator) {
            $travelOrder = $package->travelOrders()->create([
                'tipe_perjalanan' => $tipe,
                'dasar_pelaksanaan' => trim((string) $slots['dasar_pelaksanaan']['nilai']),
                'maksud_perjalanan' => trim((string) $slots['maksud']['nilai']),
                'tempat_tujuan' => trim((string) $slots['tujuan']['nilai']),
                'tanggal_berangkat' => $berangkat->toDateString(),
                'tanggal_kembali' => $kembali->toDateString(),
                'tanggal_surat' => now()->toDateString(),
                'status' => TravelOrder::STATUS_DRAFT,
                'created_by' => $createdBy,
            ]);

            $days = $berangkat->diffInDays($kembali) + 1;

            // Biaya dihitung dari tarif SBU lewat kalkulator jalur manual —
            // tidak ditampilkan di percakapan, tapi tersimpan benar.
            foreach ($employees->values() as $index => $employee) {
                $estimasi = $kalkulator->calculateEstimatedCost($employee, $travelOrder, $days, 'mobil');

                $travelOrder->personnels()->create([
                    'employee_id' => $employee->id,
                    'urutan' => $index,
                    'jenis_kendaraan' => 'mobil',
                    'uang_harian' => $estimasi['uang_harian'] ?? 0,
                    'biaya_penginapan' => $estimasi['biaya_penginapan'] ?? 0,
                    'biaya_representasi' => $estimasi['biaya_representasi'] ?? 0,
                    'biaya_transport' => $estimasi['biaya_transport'] ?? 0,
                    'biaya_taksi' => $estimasi['biaya_taksi'] ?? 0,
                ]);
            }

            return $travelOrder;
        });

        return ['travel_order' => $travelOrder, 'package' => $package];
    }

    // ── Bentuk draf untuk widget ──────────────────────────────────

    public function draftUntukWidget(string $jobId, array $slots, bool $lengkap, ?string $pesan): array
    {
        // Kartu membaca setiap slot tanpa pengaman, jadi bentuknya harus utuh
        // walau payload lama/rusak hanya berisi sebagian slot.
        foreach (self::SLOT as $kunci) {
            $slots[$kunci] = array_merge(
                $this->slot($kunci === 'personel' ? [] : null, 'kosong'),
                $slots[$kunci] ?? []
            );
        }

        // Daftar pilihan TIDAK ikut dikirim. Percakapan hanya butuh nilai yang
        // sudah terisi; mengirim seluruh master pegawai (berikut NIP) ke
        // browser pada tiap balasan adalah paparan data yang tidak perlu.
        foreach (self::SLOT as $kunci) {
            unset($slots[$kunci]['opsi']);
        }

        return [
            'job_id' => $jobId,
            'intent' => 'SPD',
            'lengkap' => $lengkap,
            'pesan' => $pesan,
            'slots' => $slots,
        ];
    }

    // ── Resolusi per jenis slot ───────────────────────────────────

    private function resolvePersonel(?string $mentah): array
    {
        if (blank($mentah)) {
            return $this->slot([], 'kosong');
        }

        $cocok = Employee::where('tipe', 'dinas')
            ->where('nama', 'like', '%' . trim($mentah) . '%')
            ->get();

        if ($cocok->count() === 1) {
            $e = $cocok->first();

            return $this->slot([$e->id], 'ok', 'kdmp', null, [$this->ringkasPegawai($e)]);
        }

        if ($cocok->count() > 1) {
            return $this->slot([], 'pilih', null,
                "beberapa pegawai cocok dengan '{$mentah}': " . $cocok->pluck('nama')->implode(', '));
        }

        return $this->slot([], 'kosong', null, "'{$mentah}' tidak ditemukan di master pegawai");
    }

    private function resolvePersonelIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if ($ids === []) {
            return $this->slot([], 'kosong');
        }

        $pegawai = Employee::whereIn('id', $ids)->where('tipe', 'dinas')->get();

        if ($pegawai->count() !== count($ids)) {
            throw new InvalidArgumentException('Ada pegawai yang tidak dikenal atau bukan pegawai dinas.');
        }

        return $this->slot($ids, 'ok', 'user', null, $pegawai->map(fn ($e) => $this->ringkasPegawai($e))->all());
    }

    private function resolvePaketId(int $id): array
    {
        $paket = collect($this->kandidatPaket())->firstWhere('id', $id);

        if (! $paket) {
            throw new InvalidArgumentException('Paket yang dipilih bukan kandidat paket perjalanan dinas.');
        }

        return $this->slot($id, 'ok', 'user', null, $paket);
    }

    private function resolveTipe(string $nilai): array
    {
        if (! in_array($nilai, self::TIPE_PERJALANAN, true)) {
            throw new InvalidArgumentException('Tipe perjalanan harus Dalam Daerah atau Luar Daerah.');
        }

        return $this->slot($nilai, 'ok', 'user');
    }

    private function slotTeks(mixed $nilai, string $label): array
    {
        $teks = trim((string) $nilai);

        if ($teks === '') {
            throw new InvalidArgumentException("Kolom {$label} tidak boleh kosong.");
        }

        return $this->slot($teks, 'ok', 'user');
    }

    /**
     * Tanggal harus utuh (ada tahunnya) dan tidak lampau — AI dilarang
     * menebak tahun sendiri.
     */
    private function resolveTanggal(?string $mentah): array
    {
        if (blank($mentah)) {
            return $this->slot(null, 'kosong');
        }

        $teks = strtolower(trim($mentah));

        // "17 Agustus 2026" / "17 agustus"
        if (preg_match('/^(\d{1,2})[\s\-\/]+([a-z]+)(?:[\s\-\/]+(\d{4}))?$/u', $teks, $m)) {
            $bulan = self::BULAN[$m[2]] ?? null;

            if ($bulan === null) {
                return $this->slot(null, 'kosong', null, "'{$mentah}' bukan tanggal yang saya kenali");
            }

            // Tahun tidak disebut → tahun berjalan. Tidak ada gunanya bertanya:
            // SPD selalu dibebankan pada tahun anggaran yang sedang berjalan.
            $tanggal = Carbon::createSafe((int) (($m[3] ?? '') ?: now()->year), $bulan, (int) $m[1]);
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $teks, $m)) {
            $tanggal = Carbon::createSafe((int) $m[1], (int) $m[2], (int) $m[3]);
        } elseif (preg_match('/^(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})$/', $teks, $m)) {
            $tanggal = Carbon::createSafe((int) $m[3], (int) $m[2], (int) $m[1]);
        } else {
            return $this->slot(null, 'kosong', null, "'{$mentah}' bukan tanggal yang saya kenali — tulis mis. 17 Agustus " . now()->year);
        }

        if (! $tanggal) {
            return $this->slot(null, 'kosong', null, "'{$mentah}' bukan tanggal yang sah");
        }

        // Tanggal lampau tidak diblokir (SPD susulan itu wajar), tapi ditandai
        // supaya salah ketik tetap kelihatan sebelum disetujui.
        $catatan = $tanggal->isPast() && ! $tanggal->isToday()
            ? $tanggal->translatedFormat('j F Y') . ' sudah lewat — pastikan sudah benar'
            : null;

        return $this->slot($tanggal->toDateString(), 'ok', 'user', $catatan, $tanggal->translatedFormat('j F Y'));
    }

    /**
     * Kesimpulan yang wajar diambil sendiri — pengguna tidak perlu ditanya
     * hal yang sudah jelas dari data. Semuanya ditandai sumbernya dan tetap
     * bisa diubah di kartu, jadi tidak ada yang tersembunyi.
     */
    private function lengkapiOtomatis(array $slots): array
    {
        // Tipe perjalanan disimpulkan dari master tujuan SBU: Lumar itu
        // kecamatan di Bengkayang, jadi pasti Dalam Daerah.
        if (($slots['tipe_perjalanan']['status'] ?? '') !== 'ok'
            && ($slots['tujuan']['status'] ?? '') === 'ok') {
            $tipe = $this->tipeDariTujuan((string) $slots['tujuan']['nilai']);

            if ($tipe) {
                $slots['tipe_perjalanan'] = $this->slot($tipe, 'ok', 'kdmp');
            }
        }

        // Satu tanggal saja berarti berangkat dan pulang di hari yang sama.
        // Bila berangkat berubah, usulan ini ikut menyesuaikan; begitu
        // pengguna mengisinya sendiri (sumber 'user'), tidak diutak-atik lagi.
        $berangkat = $slots['tanggal_berangkat'] ?? [];
        $kembali = $slots['tanggal_kembali'] ?? [];

        if (($berangkat['status'] ?? '') === 'ok'
            && (($kembali['status'] ?? '') !== 'ok' || ($kembali['sumber'] ?? '') === 'usulan')) {
            $slots['tanggal_kembali'] = $this->slot(
                $berangkat['nilai'], 'ok', 'usulan', null, $berangkat['tampil'] ?? null
            );
        }

        return $slots;
    }

    /**
     * Kategori tujuan menurut master tarif transport KDMP. Pencocokan
     * terpanjang menang supaya "Sungai Raya Kepulauan" tidak tertangkap
     * sebagai "Sungai Raya".
     */
    private function tipeDariTujuan(string $tujuan): ?string
    {
        $teks = mb_strtolower(trim($tujuan));

        if ($teks === '') {
            return null;
        }

        $kategori = null;
        $terpanjang = 0;

        foreach (SbuTransportRate::query()->select('tempat_tujuan', 'kategori')->get() as $tarif) {
            $nama = mb_strtolower(trim((string) $tarif->tempat_tujuan));

            if ($nama === '' || ! str_contains($teks, $nama)) {
                continue;
            }

            if (mb_strlen($nama) > $terpanjang) {
                $terpanjang = mb_strlen($nama);
                $kategori = $tarif->kategori;
            }
        }

        return match ($kategori) {
            'dalam_daerah' => 'Dalam Daerah',
            'luar_daerah' => 'Luar Daerah',
            default => null,
        };
    }

    /** Konsistensi antar slot: kembali tidak boleh mendahului berangkat. */
    private function periksaSilang(array $slots): array
    {
        $b = $slots['tanggal_berangkat']['nilai'] ?? null;
        $k = $slots['tanggal_kembali']['nilai'] ?? null;

        if ($b && $k && Carbon::parse($k)->lt(Carbon::parse($b))) {
            $slots['tanggal_kembali'] = $this->slot(null, 'kosong', null, 'tanggal kembali mendahului tanggal berangkat');
        }

        return $slots;
    }

    // ── Data pendukung ────────────────────────────────────────────

    private function kandidatPaket(): array
    {
        return Package::query()
            ->where('status', 'approved')
            ->whereHas('account', fn ($q) => $q->where('nama', 'like', '%perjalanan dinas%'))
            ->with('subActivity:id,kode,nama')
            ->orderBy('sub_activity_id')
            ->get()
            ->map(fn (Package $p) => [
                'id' => $p->id,
                'label' => $p->nama_paket,
                'sub_kegiatan' => trim(($p->subActivity->kode ?? '') . ' ' . ($p->subActivity->nama ?? '')),
                'pagu' => (float) $p->pagu,
            ])
            ->all();
    }


    private function ringkasPegawai(Employee $e): array
    {
        return ['id' => $e->id, 'nama' => $e->nama, 'nip' => $e->nip, 'jabatan' => $e->jabatan];
    }

    /**
     * "ke Lumar" → "Lumar" — mencegah usulan maksud menjadi
     * "Perjalanan dinas ke Ke Lumar".
     */
    private function tanpaAwalanKe(string $teks): string
    {
        return trim(preg_replace('/^\s*ke\s+/i', '', $teks));
    }

    private function tanggalSah(?string $nilai, string $label): Carbon
    {
        if (blank($nilai)) {
            throw new InvalidArgumentException("Kolom {$label} masih kosong.");
        }

        return Carbon::parse($nilai);
    }

    private function slot(mixed $nilai, string $status, ?string $sumber = null, ?string $catatan = null, mixed $tampil = null, ?array $opsi = null): array
    {
        return [
            'nilai' => $nilai,
            'status' => $status,
            'sumber' => $sumber,
            'catatan' => $catatan,
            'tampil' => $tampil,
            'opsi' => $opsi,
        ];
    }
}
