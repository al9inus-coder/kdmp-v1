<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountId = $this->route('account')?->id;

        return [
            'kode' => [
                'required',
                'string',
                Rule::unique('accounts', 'kode')->ignore($accountId),
            ],
            'nama' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
            // Skema pajak bawaan untuk rekening ini. Kosong berarti ikut
            // skema standar; diisi 'restoran' pada rekening makanan dan minuman.
            'skema_pajak' => ['nullable', 'string', Rule::in(array_keys(\App\Services\Pajak\SkemaPajak::pilihan()))],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode' => 'kode rekening belanja',
            'nama' => 'nama rekening belanja',
            'is_active' => 'status aktif',
            'skema_pajak' => 'skema pajak',
        ];
    }
}
