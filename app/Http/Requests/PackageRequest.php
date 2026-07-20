<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'sub_activity_id' => ['nullable', 'exists:sub_activities,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'id_rup' => [
                'required', 'string', 'max:100',
                Rule::unique('packages', 'id_rup')->ignore($this->route('package')),
            ],
            'nama_paket' => ['required', 'string', 'max:255'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'jenis_pengadaan' => ['nullable', 'string', 'max:100'],
            'metode_pengadaan' => ['nullable', 'string', 'max:100'],
            'pemilihan_mulai_bulan' => ['nullable', 'integer', 'between:1,12'],
            'pemilihan_selesai_bulan' => ['nullable', 'integer', 'between:1,12'],
            'kontrak_mulai_bulan' => ['nullable', 'integer', 'between:1,12'],
            'kontrak_selesai_bulan' => ['nullable', 'integer', 'between:1,12'],
            'status' => [
                'nullable',
                Rule::in(['needs_review', 'draft','submitted', 'approved']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'id_rup.unique' => 'ID RUP ini sudah terdaftar pada paket lain.',
        ];
    }

    public function attributes(): array
    {
        return [
            'fiscal_year_id' => 'tahun anggaran',
            'sub_activity_id' => 'sub kegiatan',
            'account_id' => 'rekening belanja',
            'id_rup' => 'ID RUP',
            'nama_paket' => 'nama paket',
            'pagu' => 'pagu',
            'jenis_pengadaan' => 'jenis pengadaan',
            'metode_pengadaan' => 'metode pengadaan',
            'pemilihan_mulai_bulan' => 'bulan mulai pemilihan',
            'pemilihan_selesai_bulan' => 'bulan selesai pemilihan',
            'kontrak_mulai_bulan' => 'bulan mulai kontrak',
            'kontrak_selesai_bulan' => 'bulan selesai kontrak',
            'status' => 'status',
        ];
    }
}
