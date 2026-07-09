<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTravelSpjRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'personnels' => ['required', 'array', 'min:1'],
            'personnels.*.uang_harian' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_transport' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_taksi' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_penginapan' => ['required', 'numeric', 'min:0'],
            'personnels.*.biaya_representasi' => ['required', 'numeric', 'min:0'],
            'personnels.*.transport_riil' => ['nullable', 'boolean'],
            'personnels.*.taksi_riil' => ['nullable', 'boolean'],
            'personnels.*.penginapan_riil' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'personnels.*.uang_harian' => 'uang harian',
            'personnels.*.biaya_transport' => 'biaya transport',
            'personnels.*.biaya_taksi' => 'biaya taksi',
            'personnels.*.biaya_penginapan' => 'biaya penginapan',
            'personnels.*.biaya_representasi' => 'biaya representasi',
        ];
    }
}
