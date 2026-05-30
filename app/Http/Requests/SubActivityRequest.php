<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subActivityId = $this->route('sub_activity')?->id;

        return [
            'activity_id' => ['required', 'exists:activities,id'],
            'kode' => [
                'required',
                'string',
                Rule::unique('sub_activities', 'kode')->ignore($subActivityId),
            ],
            'nama' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'activity_id' => 'kegiatan',
            'kode' => 'kode sub kegiatan',
            'nama' => 'nama sub kegiatan',
            'is_active' => 'status aktif',
        ];
    }
}
