<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
    'import_batch_id',
    'fiscal_year_id',
    'program_id',
    'activity_id',
    'sub_activity_id',
    'account_id',

    'id_rup',
    'nama_paket',

    'pagu',

    'jenis_pengadaan',
    'metode_pengadaan',

    'pemilihan_mulai_bulan',
    'pemilihan_selesai_bulan',

    'kontrak_mulai_bulan',
    'kontrak_selesai_bulan',

    'status',

    'submitted_at',
    'submitted_by',
    'approved_at',
    'approved_by',
    ];

    protected $casts = [
    'pagu' => 'decimal:2',

    'pemilihan_mulai_bulan' => 'integer',
    'pemilihan_selesai_bulan' => 'integer',

    'kontrak_mulai_bulan' => 'integer',
    'kontrak_selesai_bulan' => 'integer',

    'submitted_at' => 'datetime',
    'approved_at' => 'datetime',
    ];

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function subActivity(): BelongsTo
    {
        return $this->belongsTo(SubActivity::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function procurementPackage(): HasOne
    {
        return $this->hasOne(ProcurementPackage::class);
    }

    public function travelOrders(): HasMany
    {
        return $this->hasMany(TravelOrder::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    /**
     * Realisasi belanja paket ini, mencakup ketiga jalurnya sekaligus.
     *
     * Aturan kapan sesuatu dianggap terealisasi:
     * - Pengadaan  : lihat ProcurementPackage::getRealisasiAttribute() — untuk
     *                metode Dikecualikan seluruh catatan eksternal dijumlah,
     *                selain itu nilai kontrak baru dihitung setelah tahapannya
     *                selesai, bukan saat masih diproses pembayarannya.
     * - Perjalanan : hanya SPJ (biaya rampung) yang sudah DISETUJUI.
     * - Lembur     : hanya periode yang sudah DIKUNCI.
     *
     * Aturan ini sebelumnya disalin di enam view (monev kabid & admin, cetak
     * monev, dan kartu kendali). Dasbor memanggil metode ini supaya tidak
     * menambah salinan ketujuh; keenam view itu masih menyimpan salinannya
     * sendiri dan layak dipindahkan ke sini juga.
     *
     * @param  \Illuminate\Support\Collection|null  $sbuRates  tarif SBU lembur,
     *         dioper dari luar bila memanggil untuk banyak paket agar tidak
     *         mengambil ulang dari database tiap kali.
     */
    public function realisasi($sbuRates = null): float
    {
        $sbuRates ??= SbuLembur::all();
        $total = 0.0;

        if ($this->procurementPackage) {
            $total += (float) $this->procurementPackage->realisasi;
        }

        foreach ($this->travelOrders as $travelOrder) {
            if ($travelOrder->spjStatus() !== TravelOrder::SPJ_APPROVED) {
                continue;
            }

            foreach ($travelOrder->personnels as $personnel) {
                $total += (float) $personnel->uang_harian
                    + (float) $personnel->biaya_penginapan
                    + (float) $personnel->biaya_representasi
                    + (float) $personnel->biaya_transport
                    + (float) ($personnel->biaya_taksi ?? 0);
            }
        }

        foreach ($this->overtimes as $overtime) {
            if ($overtime->is_locked) {
                $total += (float) $overtime->calculateTotalRealisasi($sbuRates);
            }
        }

        return $total;
    }

    public static function monthNames(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }
    public function isComplete(): bool
    {
        return
            !empty($this->nama_paket) &&
            !empty($this->sub_activity_id) &&
            !empty($this->account_id) &&
            !empty($this->jenis_pengadaan) &&
            !empty($this->metode_pengadaan) &&
            !empty($this->pemilihan_mulai_bulan) &&
            !empty($this->pemilihan_selesai_bulan) &&
            !empty($this->kontrak_mulai_bulan) &&
            !empty($this->kontrak_selesai_bulan);
    }

    public function getRouteKey()
    {
        return $this->id_rup ?? $this->id;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        // Cari berdasarkan id_rup terlebih dahulu (exact match)
        $package = $this->where('id_rup', $value)->first();

        // Jika tidak ditemukan berdasarkan id_rup, cari berdasarkan primary key
        if (!$package) {
            $package = $this->where('id', $value)->firstOrFail();
        }

        return $package ?? abort(404);
    }
}
