<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'settings.required' => 'Pengaturan wajib diisi.',
            'settings.array' => 'Pengaturan harus berupa array.',
        ];
    }
}
