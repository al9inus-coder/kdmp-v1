<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $programId = $this->route('program')?->id;

        return [
            'kode' => [
                'required',
                'string',
                Rule::unique('programs', 'kode')->ignore($programId),
            ],
            'nama' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode' => 'kode program',
            'nama' => 'nama program',
            'is_active' => 'status aktif',
        ];
    }
}
