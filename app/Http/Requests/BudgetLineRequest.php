<?php

namespace App\Http\Requests;

use App\Models\BudgetRevision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BudgetLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dijaga middleware role:Admin pada grup route
    }

    public function rules(): array
    {
        $lineId = $this->route('anggaran')?->id;

        return [
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'sub_activity_id' => [
                'required',
                'exists:sub_activities,id',
                // Satu rekening hanya boleh sekali dalam satu sub kegiatan per tahun.
                Rule::unique('budget_lines')
                    ->where(fn ($q) => $q
                        ->where('fiscal_year_id', $this->fiscal_year_id)
                        ->where('account_id', $this->account_id))
                    ->ignore($lineId),
            ],
            'account_id' => ['required', 'exists:accounts,id'],
            'keterangan' => ['nullable', 'string', 'max:1000'],

            // Revisi awal (saat membuat baris baru)
            'pagu' => ['required', 'numeric', 'min:0'],
            'jenis' => ['required', Rule::in(array_keys(BudgetRevision::jenisOptions()))],
            'tanggal' => ['nullable', 'date'],
            'nomor_dasar' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fiscal_year_id' => 'tahun anggaran',
            'sub_activity_id' => 'sub kegiatan',
            'account_id' => 'rekening belanja',
            'pagu' => 'pagu',
            'jenis' => 'tahap anggaran',
            'nomor_dasar' => 'nomor dasar hukum',
        ];
    }

    public function messages(): array
    {
        return [
            'sub_activity_id.unique' => 'Rekening ini sudah punya baris anggaran pada sub kegiatan dan tahun tersebut. Buka baris itu lalu catat revisi baru.',
        ];
    }
}
