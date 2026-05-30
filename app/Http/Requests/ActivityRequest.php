<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $activityId = $this->route('activity')?->id;

        return [
            'program_id' => ['required', 'exists:programs,id'],
            'kode' => [
                'required',
                'string',
                Rule::unique('activities', 'kode')->ignore($activityId),
            ],
            'nama' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'program_id' => 'program',
            'kode' => 'kode kegiatan',
            'nama' => 'nama kegiatan',
            'is_active' => 'status aktif',
        ];
    }
}
